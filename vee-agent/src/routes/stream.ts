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
  editUserMessage
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

async function resolveContextSummary(sessionId: string): Promise<string | null> {
  const allRows = await loadStoredMessages(sessionId);
  const overflowTurns = extractOverflowTurns(allRows);

  if (overflowTurns.length === 0) return null;

  const overflowUserTurns = overflowTurns.filter((r) => r.role === "user").length;

  const cached = await getContextSummary(sessionId);
  if (cached && cached.turns >= overflowUserTurns) {
    return cached.summary;
  }

  const summary = await generateSummary(overflowTurns, cached?.summary ?? null);
  await saveContextSummary(sessionId, summary, overflowUserTurns);
  return summary;
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

  // In chat mode: start with the planning model (5.4-mini); switches to the
  // execution model (5.3-codex) inside runAgentLoop whenever tools are called.
  // In cowork mode: always uses MODEL_COWORK directly.
  const planningModel = mode === "cowork" ? MODEL_COWORK : MODEL_CHAT;
  const systemPrompt = mode === "cowork" ? SYSTEM_PROMPT_COWORK : SYSTEM_PROMPT_CHAT;

  let summaryBlock: ChatCompletionMessageParam[] = [];
  try {
    const contextSummary = await resolveContextSummary(sessionId);
    if (contextSummary) {
      summaryBlock = [
        {
          role: "user",
          content: `📋 Contexto das mensagens anteriores desta sessão:\n\n${contextSummary}`
        },
        {
          role: "assistant",
          content: "Contexto anterior recebido. Continuando a partir daqui."
        }
      ];
    }
  } catch (err) {
    const msg = err instanceof Error ? err.message : String(err);
    sseWrite(res, { type: "warning", message: `Context summarization failed: ${msg}` });
  }

  const messages: ChatCompletionMessageParam[] = [
    { role: "system", content: systemPrompt },
    ...summaryBlock,
    ...history,
    ...(!isEditFlow ? [{ role: "user" as const, content: userMessage }] : [])
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

  // Dynamic model: starts as the planning model (e.g. gpt-5.4-mini in chat mode).
  // Switches to MODEL_COWORK (gpt-5.3-codex) when tool calls are detected, then
  // returns to the planning model once tool execution is complete.
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

    // Emit model info so the UI can show which model is active
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
      // No tool calls — back to planning model for next turn
      currentModel = planningModel;
      break;
    }

    // Tool calls detected — switch to execution model for the next iteration
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
