import { Client } from "@modelcontextprotocol/sdk/client/index.js";
import { StreamableHTTPClientTransport } from "@modelcontextprotocol/sdk/client/streamableHttp.js";

const MCP_URL = process.env.REAL_MCP_URL ?? "https://mcp.mmcriativos.cloud/mcp";
const MCP_TOKEN = (process.env.REAL_MCP_TOKEN ?? "").trim();

if (!MCP_TOKEN) {
  throw new Error("REAL_MCP_TOKEN is required.");
}

function parseStructured(result) {
  if (result?.structuredContent && typeof result.structuredContent === "object") {
    return result.structuredContent;
  }
  const text = Array.isArray(result?.content)
    ? result.content.find((item) => item?.type === "text" && typeof item?.text === "string")?.text
    : null;
  if (!text) return null;
  try {
    return JSON.parse(text);
  } catch {
    return { raw_text: text };
  }
}

const transport = new StreamableHTTPClientTransport(new URL(MCP_URL), {
  requestInit: { headers: { Authorization: `Bearer ${MCP_TOKEN}` } }
});
const client = new Client({ name: "fs-search-debug", version: "1.0.0" }, { capabilities: {} });

try {
  await client.connect(transport);
  const result = await client.callTool({
    name: "vee_fs_search_text",
    arguments: {
      path: "/obsidian",
      pattern: "2026",
      case_insensitive: true,
      max_matches: 20
    }
  });
  console.log(JSON.stringify({ isError: result.isError, structured: parseStructured(result), raw: result }, null, 2));
} finally {
  await client.close();
  await transport.close();
}
