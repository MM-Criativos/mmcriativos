import { Client } from "@modelcontextprotocol/sdk/client/index.js";
import { StreamableHTTPClientTransport } from "@modelcontextprotocol/sdk/client/streamableHttp.js";
import type { Tool } from "@modelcontextprotocol/sdk/types.js";
import type { ChatCompletionTool } from "openai/resources/chat/completions.js";
import { MCP_SERVER_URL, MCP_AUTH_TOKEN } from "./config.js";

type McpToolResult = {
  content: Array<{ type: string; text?: string }>;
  isError?: boolean;
};

function buildTransport(): StreamableHTTPClientTransport {
  const headers: Record<string, string> = {};
  if (MCP_AUTH_TOKEN) {
    headers["Authorization"] = `Bearer ${MCP_AUTH_TOKEN}`;
  }
  return new StreamableHTTPClientTransport(new URL(MCP_SERVER_URL), { requestInit: { headers } });
}

function withTimeout<T>(promise: Promise<T>, ms: number, label: string): Promise<T> {
  return Promise.race([
    promise,
    new Promise<never>((_, reject) =>
      setTimeout(() => reject(new Error(`MCP timeout: ${label} (${ms}ms)`)), ms)
    )
  ]);
}

async function withClient<T>(fn: (client: Client) => Promise<T>, timeoutMs = 10000): Promise<T> {
  const client = new Client({ name: "vee-agent", version: "1.0.0" });
  const transport = buildTransport();
  await withTimeout(client.connect(transport), timeoutMs, "connect");
  try {
    return await withTimeout(fn(client), timeoutMs, "operation");
  } finally {
    // close sem await para não bloquear — erros de close são ignorados
    client.close().catch(() => undefined);
  }
}

export async function listMcpTools(): Promise<Tool[]> {
  return withClient(async (client) => {
    const result = await client.listTools();
    return result.tools;
  });
}

export async function callMcpTool(
  name: string,
  args: Record<string, unknown>
): Promise<McpToolResult> {
  return withClient(async (client) => {
    const result = await client.callTool({ name, arguments: args });
    return result as McpToolResult;
  });
}

export function mcpToolsToOpenAI(tools: Tool[]): ChatCompletionTool[] {
  return tools.map((tool) => ({
    type: "function" as const,
    function: {
      name: tool.name,
      description: tool.description ?? "",
      parameters: (tool.inputSchema as Record<string, unknown>) ?? { type: "object", properties: {} }
    }
  }));
}

let cachedTools: ChatCompletionTool[] | null = null;
let cacheExpiry = 0;
const CACHE_TTL_MS = 5 * 60 * 1000; // 5 minutes

export async function getOpenAITools(): Promise<ChatCompletionTool[]> {
  if (cachedTools && Date.now() < cacheExpiry) {
    return cachedTools;
  }
  const tools = await listMcpTools();
  cachedTools = mcpToolsToOpenAI(tools);
  cacheExpiry = Date.now() + CACHE_TTL_MS;
  return cachedTools;
}

export function formatToolResult(result: McpToolResult): string {
  const textContent = result.content
    .filter((c) => c.type === "text" && c.text)
    .map((c) => c.text ?? "")
    .join("\n");
  return textContent || JSON.stringify(result.content);
}
