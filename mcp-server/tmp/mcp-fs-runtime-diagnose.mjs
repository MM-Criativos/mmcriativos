import { Client } from "@modelcontextprotocol/sdk/client/index.js";
import { StreamableHTTPClientTransport } from "@modelcontextprotocol/sdk/client/streamableHttp.js";

const MCP_URL = process.env.REAL_MCP_URL ?? "https://mcp.mmcriativos.cloud/mcp";
const MCP_TOKEN = (process.env.REAL_MCP_TOKEN ?? "").trim();

if (!MCP_TOKEN) {
  throw new Error("REAL_MCP_TOKEN is required.");
}

function parseStructured(result) {
  if (result?.structuredContent && typeof result.structuredContent === "object") return result.structuredContent;
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

async function main() {
  const transport = new StreamableHTTPClientTransport(new URL(MCP_URL), {
    requestInit: { headers: { Authorization: `Bearer ${MCP_TOKEN}` } }
  });
  const client = new Client({ name: "mcp-fs-runtime-diagnose", version: "1.0.0" }, { capabilities: {} });

  async function call(name, args) {
    const result = await client.callTool({ name, arguments: args });
    return {
      tool: name,
      args,
      isError: Boolean(result?.isError),
      structured: parseStructured(result)
    };
  }

  try {
    await client.connect(transport);
    const outputs = [];
    outputs.push(await call("vee_fs_list_allowed_paths", {}));
    outputs.push(await call("vee_fs_list_directory", { path: "/obsidian", max_entries: 20 }));
    outputs.push(await call("vee_fs_list_directory", { path: "/opt/obsidian/mm-brain", max_entries: 20 }));
    outputs.push(await call("vee_fs_read_file", { path: "/obsidian/MMCloud/agentes.md", max_bytes: 4000 }));
    outputs.push(
      await call("vee_fs_read_file", { path: "/opt/obsidian/mm-brain/MMCloud/agentes.md", max_bytes: 4000 })
    );
    outputs.push(
      await call("vee_fs_search_text", {
        path: "/obsidian",
        pattern: "Diretrizes",
        case_insensitive: true,
        max_matches: 10
      })
    );
    outputs.push(
      await call("vee_fs_search_text", {
        path: "/opt/obsidian/mm-brain",
        pattern: "Diretrizes",
        case_insensitive: true,
        max_matches: 10
      })
    );
    console.log(JSON.stringify({ mcp_url: MCP_URL, at: new Date().toISOString(), outputs }, null, 2));
  } finally {
    await client.close();
    await transport.close();
  }
}

main().catch((error) => {
  console.error(error instanceof Error ? error.stack ?? error.message : String(error));
  process.exitCode = 1;
});
