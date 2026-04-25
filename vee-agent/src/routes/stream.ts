import type { Request, Response } from "express";
import { v4 as uuidv4 } from "uuid";
import type {
  ChatCompletionMessageParam,
  ChatCompletionTool,
  ChatCompletionAssistantMessageParam,
  ChatCompletionMessageToolCall
} from "openai/resources/chat/completions.js";
import { openai } from "../openaiClient.js";
import { getOpenAITools, callMcpTool, formatToolResult } from "../mcpClient.js";
import {
  createSession,
  getSession,
  loadHistory,
  loadStoredMessages,
  extractOverflowTurns,
  getContextSummary,
  saveContextSummary,
  saveMessage,
  updateSessionTitle,
  editUserMessage,
  MAX_CONTEXT_TOKENS,
  estimateTokens
} from "../sessions.js";
import { generateSummary } from "../summarizer.js";
import { emitLog } from "../eventBus.js";
import {
  MODEL_CHAT,
  MODEL_COWORK,
  SYSTEM_PROMPT_CHAT,
  SYSTEM_PROMPT_COWORK
} from "../config.js";

type StreamRequestBody = {
  message: string;
  session_id?: string;
  mode?: "chat" | "cowork";
  title?: string;
  edit_message_id?: number;
};

type AccumulatedToolCall = {
  index: number;
  id: string;
  name: string;
  arguments: string;
};

function sseWrite(res: Response, data: Record<string, unknown>): void {
  res.write(`data: ${JSON.stringify(data)}\n\n`);
}

/**
 * Resolves the context summary for a session.
 *
 * Two compaction triggers:
 *  1. Turn-window overflow — turns older than MAX_HISTORY_TURNS are summarized
 *     incrementally (existing behaviour).
 *  2. Token threshold — when the estimated tokens of the current history
 *     exceeds MAX_CONTEXT_TOKENS (default 120k), ALL stored messages are
 *     summarized from scratch and `forceFullCompaction` is returned as true.
 *     The caller must then skip the history entirely and rely solely on the
 *     summary block.
 */
async function resolveContextSummary(
  sessionId: string,
  historyMessages: ChatCompletionMessageParam[]
): Promise<{ summary: string; forceFullCompaction: boolean } | null> {
  const allRows = await loadStoredMessages(sessionId);
  const overflowTurns = extractOverflowTurns(allRows);

  const estimatedTokens = estimateTokens(historyMessages);
  const forceCompaction = MAX_CONTEXT_TOKENS > 0 && estimatedTokens > MAX_CONTEXT_TOKENS;

  if (overflowTurns.length === 0 && !forceCompaction) return null;

  const turnsToSummarize = forceCompaction ? allRows : overflowTurns;
  const overflowUserTurns = forceCompaction
    ? allRows.filter((r) => r.role === "user").length
    : overflowTurns.filter((r) => r.role === "user").length;

  const cached = await getContextSummary(sessionId);

  // Skip regeneration if the cached summary already covers all overflow turns
  // (only applies to the non-forced path).
  if (!forceCompaction && cached && cached.turns >= overflowUserTurns) {
    return { summary: cached.summary, forceFullCompaction: false };
  }

  // For a forced full compaction, always regenerate from scratch (pass null so
  // the summarizer doesn't try to layer on top of a stale summary).
  const summary = await generateSummary(
    turnsToSummarize,
    forceCompaction ? null : (cached?.summary ?? null)
  );
  await saveContextSummary(sessionId, summary, overflowUserTurns);
  return { summary, forceFullCompaction: forceCompaction };
}

export async function handleStream(req: Request, res: Response): Promise<void> {
  const body = req.body as StreamRequestBody;
  const userMessage = (body.message ?? "").trim();
  const mode = body.mode === "cowork" ? "cowork" : "chat";

  if (!userMessage) {
    res.status(400).json({ error: "message is required" });
    return;
  }

  res.setHeader("Content-Type", "text/event-stream");
  res.setHeader("Cache-Control", "no-cache");
  res.setHeader("Connection", "keep-alive");
  res.setHeader("X-Accel-Buffering", "no");
  res.flushHeaders();

  let sessionId = body.session_id ?? "";
  if (sessionId) {
    const existing = await getSession(sessionId);
    if (!existing) sessionId = "";
  }
  if (!sessionId) {
    const session = await createSession(mode, body.title);
    sessionId = session.id;
    sseWrite(res, { type: "session_created", session_id: sessionId, mode });
  }

  const editMessageId = Number(body.edit_message_id ?? 0);
  const isEditFlow = Number.isFinite(editMessageId) && editMessageId > 0;

  if (isEditFlow) {
    const editResult = await editUserMessage(sessionId, editMessageId, userMessage);
    if (!editResult.ok) {
      sseWrite(res, { type: "error", message: editResult.error });
      res.end();
      return;
    }
    sseWrite(res, {
      type: "message_edited",
      message_id: editMessageId,
      pruned_messages: editResult.pruned_messages
    });
  }

  const history = await loadHistory(sessionId);
  if (!isEditFlow) {
    await saveMessage({ session_id: sessionId, role: "user", content: userMessage });
  }

  const planningModel = mode === "cowork" ? MODEL_COWORK : MODEL_CHAT;
  const systemPrompt = mode === "cowork" ? SYSTEM_PROMPT_COWORK : SYSTEM_PROMPT_CHAT;

  let summaryBlock: ChatCompletionMessageParam[] = [];
  let forceFullCompaction = false;
  try {
    const contextResult = await resolveContextSummary(sessionId, history);
    if (contextResult) {
      forceFullCompaction = contextResult.forceFullCompaction;
      summaryBlock = [
        {
          role: "user",
          content: `📋 Contexto das mensagens anteriores desta sessão:\n\n${contextResult.summary}`
        },
        {
          role: "assistant",
          content: "Contexto anterior recebido. Continuando a partir daqui."
        }
      ];
      if (forceFullCompaction) {
        sseWrite(res, {
          type: "context_compacted",
          reason: "token_threshold",
          estimated_tokens: estimateTokens(history)
        });
      }
    }
  } catch (err) {
    const msg = err instanceof Error ? err.message : String(err);
    sseWrite(res, { type: "warning", message: `Context summarization failed: ${msg}` });
  }

  // When a full token-based compaction fires, the summary already covers the
  // entire history — skip it to stay well under the context limit.
  // For edit flows under compaction, we re-add the user message so the
  // messages array always ends with a user turn.
  const needsUserMessage = !isEditFlow || forceFullCompaction;

  const messages: ChatCompletionMessageParam[] = [
    { role: "system", content: systemPrompt },
    ...summaryBlock,
    ...(forceFullCompaction ? [] : history),
    ...(needsUserMessage ? [{ role: "user" as const, content: userMessage }] : [])
  ];

  let tools: ChatCompletionTool[] = [];
  try {
    tools = await getOpenAITools();
  } catch {
    sseWrite(res, { type: "warning", message: "MCP tools unavailable, running without tools" });
  }

  emitLog({
    id: uuidv4(),
    timestamp: new Date().toISOString(),
    session_id: sessionId,
    mode,
    type: "message_start"
  });

  try {
    const fullResponse = await runAgentLoop({
      messages,
      tools,
      planningModel,
      sessionId,
      mode,
      res
    });

    await saveMessage({ session_id: sessionId, role: "assistant", content: fullResponse });

    if (history.length === 0) {
      const title = userMessage.slice(0, 60) + (userMessage.length > 60 ? "…" : "");
      await updateSessionTitle(sessionId, title);
      sseWrite(res, { type: "session_titled", session_id: sessionId, title });
    }

    emitLog({
      id: uuidv4(),
      timestamp: new Date().toISOString(),
      session_id: sessionId,
      mode,
      type: "message_done"
    });

    sseWrite(res, { type: "done", session_id: sessionId });
  } catch (error) {
    const message = error instanceof Error ? error.message : String(error);
    sseWrite(res, { type: "error", message });
  } finally {
    res.end();
  }
}

async function runAgentLoop(options: {
  messages: ChatCompletionMessageParam[];
  tools: ChatCompletionTool[];
  planningModel: string;
  sessionId: string;
  mode: "chat" | "cowork";
  res: Response;
}): Promise<string> {
  const { messages, tools, planningModel, sessionId, mode, res } = options;
  let fullAssistantContent = "";

  let currentModel = planningModel;

  for (let iteration = 0; iteration < 10; iteration++) {
    const streamParams: Parameters<typeof openai.chat.completions.create>[0] = {
      model: currentModel,
      messages,
      stream: true as const
    };

    if (tools.length > 0) {
      streamParams.tools = tools;
      streamParams.tool_choice = "auto";
    }

    sseWrite(res, { type: "model_active", model: currentModel, iteration });

    const stream = await openai.chat.completions.create({
      ...streamParams,
      stream: true
    });

    let iterationContent = "";
    const pendingToolCalls: AccumulatedToolCall[] = [];
    let finishReason: string | null = null;

    for await (const chunk of stream) {
      const choice = chunk.choices[0];
      if (!choice) continue;

      const delta = choice.delta;
      finishReason = choice.finish_reason ?? finishReason;

      if (delta.content) {
        iterationContent += delta.content;
        fullAssistantContent += delta.content;
        sseWrite(res, { type: "text", content: delta.content });
      }

      if (delta.tool_calls) {
        for (const tc of delta.tool_calls) {
          const idx = tc.index ?? 0;
          if (!pendingToolCalls[idx]) {
            pendingToolCalls[idx] = { index: idx, id: "", name: "", arguments: "" };
          }
          if (tc.id) pendingToolCalls[idx].id = tc.id;
          if (tc.function?.name) pendingToolCalls[idx].name = tc.function.name;
          if (tc.function?.arguments) pendingToolCalls[idx].arguments += tc.function.arguments;
        }
      }
    }

    const completedToolCalls = pendingToolCalls.filter(Boolean);

    const toolCallsForMessage: ChatCompletionMessageToolCall[] | undefined =
      completedToolCalls.length > 0
        ? completedToolCalls.map((tc) => ({
            id: tc.id,
            type: "function" as const,
            function: { name: tc.name, arguments: tc.arguments }
          }))
        : undefined;

    const assistantMessage: ChatCompletionAssistantMessageParam = {
      role: "assistant",
      content: iterationContent || null,
      ...(toolCallsForMessage ? { tool_calls: toolCallsForMessage } : {})
    };

    if (toolCallsForMessage) {
      await saveMessage({
        session_id: sessionId,
        role: "assistant",
        content: iterationContent || null,
        tool_calls: toolCallsForMessage
      });
    }

    messages.push(assistantMessage);

    if (finishReason === "stop" || completedToolCalls.length === 0) {
      currentModel = planningModel;
      break;
    }

    currentModel = MODEL_COWORK;

    for (const tc of completedToolCalls) {
      let parsedArgs: Record<string, unknown> = {};
      try {
        parsedArgs = JSON.parse(tc.arguments) as Record<string, unknown>;
      } catch {
        parsedArgs = {};
      }

      const callId = uuidv4();
      const startMs = Date.now();

      sseWrite(res, { type: "tool_start", call_id: callId, name: tc.name, args: parsedArgs });

      emitLog({
        id: callId,
        timestamp: new Date().toISOString(),
        session_id: sessionId,
        mode,
        type: "tool_start",
        tool_name: tc.name,
        args: parsedArgs
      });

      let toolResultText: string;
      let toolError: string | undefined;

      try {
        const result = await callMcpTool(tc.name, parsedArgs);
        toolResultText = formatToolResult(result);

        sseWrite(res, {
          type: "tool_result",
          call_id: callId,
          name: tc.name,
          result: toolResultText.slice(0, 500),
          duration_ms: Date.now() - startMs
        });

        emitLog({
          id: callId,
          timestamp: new Date().toISOString(),
          session_id: sessionId,
          mode,
          type: "tool_result",
          tool_name: tc.name,
          result: toolResultText.slice(0, 500),
          duration_ms: Date.now() - startMs
        });
      } catch (error) {
        toolError = error instanceof Error ? error.message : String(error);
        toolResultText = `Error: ${toolError}`;

        sseWrite(res, {
          type: "tool_error",
          call_id: callId,
          name: tc.name,
          error: toolError,
          duration_ms: Date.now() - startMs
        });

        emitLog({
          id: callId,
          timestamp: new Date().toISOString(),
          session_id: sessionId,
          mode,
          type: "tool_error",
          tool_name: tc.name,
          error: toolError,
          duration_ms: Date.now() - startMs
        });
      }

      await saveMessage({
        session_id: sessionId,
        role: "tool",
        content: toolResultText,
        tool_call_id: tc.id,
        tool_name: tc.name
      });

      messages.push({
        role: "tool",
        tool_call_id: tc.id,
        content: toolResultText
      });
    }
  }

  return fullAssistantContent;
}
