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
  MODEL_EXECUTE,
  SYSTEM_PROMPT_CHAT,
  SYSTEM_PROMPT_COWORK
} from "../config.js";

// ─── Types ────────────────────────────────────────────────────────────────────

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

// Inline types for Responses API (avoids SDK import-path fragility)
type ResponsesMessageItem = {
  role: "user" | "assistant" | "system";
  content: string;
};
type ResponsesFunctionCallItem = {
  type: "function_call";
  id?: string;
  call_id: string;
  name: string;
  arguments: string;
};
type ResponsesFunctionCallOutputItem = {
  type: "function_call_output";
  call_id: string;
  output: string;
};
type ResponsesInputItem =
  | ResponsesMessageItem
  | ResponsesFunctionCallItem
  | ResponsesFunctionCallOutputItem;

type ResponsesTool = {
  type: "function";
  name: string;
  description?: string;
  parameters?: Record<string, unknown>;
};

// ─── Helpers ─────────────────────────────────────────────────────────────────

function sseWrite(res: Response, data: Record<string, unknown>): void {
  res.write(`data: ${JSON.stringify(data)}\n\n`);
}

/**
 * Returns true for models that require the Responses API.
 * GPT-5 family (gpt-5.*) and reasoning models (o1, o3, o4) use the Responses API.
 * GPT-4 family uses the classic Chat Completions API.
 */
function isResponsesModel(model: string): boolean {
  return (
    model.startsWith("gpt-5") ||
    model.startsWith("o1") ||
    model.startsWith("o3") ||
    model.startsWith("o4")
  );
}

/**
 * Converts a stored Chat Completions history into Responses API input items.
 * System messages are handled separately via the `instructions` param, so
 * this function skips them (pass them as the first item if needed).
 */
function chatHistoryToResponsesInput(
  messages: ChatCompletionMessageParam[]
): ResponsesInputItem[] {
  const items: ResponsesInputItem[] = [];

  for (const msg of messages) {
    if (msg.role === "system") {
      items.push({ role: "system", content: typeof msg.content === "string" ? msg.content : "" });
    } else if (msg.role === "user") {
      items.push({ role: "user", content: typeof msg.content === "string" ? msg.content : "" });
    } else if (msg.role === "assistant") {
      const a = msg as ChatCompletionAssistantMessageParam;
      if (a.tool_calls?.length) {
        if (a.content) {
          items.push({
            role: "assistant",
            content: typeof a.content === "string" ? a.content : ""
          });
        }
        for (const tc of a.tool_calls) {
          items.push({
            type: "function_call",
            call_id: tc.id,
            name: tc.function.name,
            arguments: tc.function.arguments
          });
        }
      } else {
        items.push({
          role: "assistant",
          content: typeof a.content === "string" ? (a.content ?? "") : ""
        });
      }
    } else if (msg.role === "tool") {
      const t = msg as { role: "tool"; tool_call_id: string; content: string };
      items.push({
        type: "function_call_output",
        call_id: t.tool_call_id,
        output: typeof t.content === "string" ? t.content : JSON.stringify(t.content)
      });
    }
  }

  return items;
}

/** Convert Chat Completions tool format → Responses API tool format */
function chatToolsToResponsesTools(tools: ChatCompletionTool[]): ResponsesTool[] {
  return tools.map((t) => ({
    type: "function" as const,
    name: t.function.name,
    description: t.function.description,
    parameters: t.function.parameters as Record<string, unknown>
  }));
}

// ─── Context summarization ────────────────────────────────────────────────────

/**
 * Resolves the context summary for a session.
 *
 * Two compaction triggers:
 *  1. Turn-window overflow — turns older than MAX_HISTORY_TURNS are summarized
 *     incrementally (existing behaviour).
 *  2. Token threshold — when the estimated tokens of the current history
 *     exceeds MAX_CONTEXT_TOKENS (default 120k), ALL stored messages are
 *     summarized from scratch and `forceFullCompaction` is returned as true.
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

  if (!forceCompaction && cached && cached.turns >= overflowUserTurns) {
    return { summary: cached.summary, forceFullCompaction: false };
  }

  const summary = await generateSummary(
    turnsToSummarize,
    forceCompaction ? null : (cached?.summary ?? null)
  );
  await saveContextSummary(sessionId, summary, overflowUserTurns);
  return { summary, forceFullCompaction: forceCompaction };
}

// ─── Main handler ─────────────────────────────────────────────────────────────

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
    // Route to Responses API if planning OR execution model is a GPT-5/reasoning model
    const useResponsesApi =
      isResponsesModel(planningModel) || isResponsesModel(MODEL_EXECUTE);

    const fullResponse = useResponsesApi
      ? await runAgentLoopResponses({ messages, tools, planningModel, sessionId, mode, res })
      : await runAgentLoopChat({ messages, tools, planningModel, sessionId, mode, res });

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

// ─── Agent Loop — Chat Completions (GPT-4 family) ────────────────────────────

async function runAgentLoopChat(options: {
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

    // Switch to execution model for tool-result processing iterations
    currentModel = MODEL_EXECUTE;

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

// ─── Agent Loop — Responses API (GPT-5 / reasoning models) ───────────────────

async function runAgentLoopResponses(options: {
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
  const responsesTools = chatToolsToResponsesTools(tools);

  // Build initial input from Chat Completions history
  let responsesInput: ResponsesInputItem[] = chatHistoryToResponsesInput(messages);

  for (let iteration = 0; iteration < 10; iteration++) {
    sseWrite(res, { type: "model_active", model: currentModel, iteration });

    // openai.responses.create is typed loosely here to avoid SDK import path fragility.
    // eslint-disable-next-line @typescript-eslint/no-explicit-any
    const responsesApi = (openai as any).responses;
    if (!responsesApi?.create) {
      throw new Error(
        "openai.responses.create is not available. Upgrade the openai package to >=4.77.0."
      );
    }

    const stream = await responsesApi.create({
      model: currentModel,
      input: responsesInput,
      ...(responsesTools.length > 0
        ? { tools: responsesTools, tool_choice: "auto" }
        : {}),
      stream: true
    });

    let iterationContent = "";
    const completedToolCalls: ResponsesFunctionCallItem[] = [];

    for await (const event of stream as AsyncIterable<Record<string, unknown>>) {
      const eventType = event["type"] as string | undefined;

      if (eventType === "response.output_text.delta") {
        const delta = (event["delta"] as string) ?? "";
        iterationContent += delta;
        fullAssistantContent += delta;
        sseWrite(res, { type: "text", content: delta });
      } else if (eventType === "response.output_item.done") {
        const item = event["item"] as Record<string, unknown> | undefined;
        if (item && item["type"] === "function_call") {
          completedToolCalls.push({
            type: "function_call",
            id: item["id"] as string | undefined,
            call_id: (item["call_id"] as string) ?? (item["id"] as string) ?? "",
            name: (item["name"] as string) ?? "",
            arguments: (item["arguments"] as string) ?? "{}"
          });
        }
      }
    }

    // Save the assistant message with tool_calls whenever tool calls are present,
    // even when there is no accompanying text content (iterationContent is empty).
    //
    // Without this, sessions resumed after a tool-only turn will have orphaned
    // `function_call_output` items in the Responses API input with no matching
    // `function_call` counterpart, causing OpenAI to return:
    //   400 "No tool call found for function call output with call_id …"
    if (completedToolCalls.length > 0) {
      await saveMessage({
        session_id: sessionId,
        role: "assistant",
        content: iterationContent || null,
        tool_calls: completedToolCalls.map((tc) => ({
          id: tc.call_id,
          type: "function" as const,
          function: { name: tc.name, arguments: tc.arguments }
        }))
      });
    }

    // No tool calls → done
    if (completedToolCalls.length === 0) break;

    // Switch to execution model for tool-result processing
    currentModel = MODEL_EXECUTE;

    // Append assistant function_call items to the input
    for (const tc of completedToolCalls) {
      responsesInput.push({
        type: "function_call",
        id: tc.id,
        call_id: tc.call_id,
        name: tc.name,
        arguments: tc.arguments
      });
    }

    // Execute each tool and append results
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

      // Save in Chat Completions format for history compatibility
      await saveMessage({
        session_id: sessionId,
        role: "tool",
        content: toolResultText,
        tool_call_id: tc.call_id,
        tool_name: tc.name
      });

      // Append function_call_output to Responses API input
      responsesInput.push({
        type: "function_call_output",
        call_id: tc.call_id,
        output: toolResultText
      });
    }
  }

  return fullAssistantContent;
}
