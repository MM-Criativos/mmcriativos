import { writeFile } from "node:fs/promises";
import { Client } from "@modelcontextprotocol/sdk/client/index.js";
import { StreamableHTTPClientTransport } from "@modelcontextprotocol/sdk/client/streamableHttp.js";

const MCP_URL = process.env.REAL_MCP_URL ?? "https://mcp.mmcriativos.cloud/mcp";
const MCP_TOKEN = (process.env.REAL_MCP_TOKEN ?? "").trim();
const TRACE_ID = `fs-fix-${Date.now()}-${Math.random().toString(36).slice(2, 8)}`;
const FS_TEST_ROOT = process.env.FS_TEST_ROOT ?? "/obsidian";

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

function assert(condition, message) {
  if (!condition) throw new Error(message);
}

async function main() {
  const transport = new StreamableHTTPClientTransport(new URL(MCP_URL), {
    requestInit: { headers: { Authorization: `Bearer ${MCP_TOKEN}` } }
  });
  const client = new Client({ name: "filesystem-fix-validation", version: "1.0.0" }, { capabilities: {} });

  const report = {
    generated_at: new Date().toISOString(),
    mcp_url: MCP_URL,
    trace_id: TRACE_ID,
    checks: [],
    summary: { ok: false, passed: 0, failed: 0 },
    artifacts: {}
  };

  async function call(name, args = {}) {
    const result = await client.callTool({ name, arguments: args });
    const structured = parseStructured(result);
    const isError = Boolean(result?.isError) || Boolean(structured?.ok === false);
    return { result, structured, isError };
  }

  async function check(id, title, fn) {
    try {
      const details = await fn();
      report.checks.push({ id, title, status: "pass", details: details ?? {} });
    } catch (error) {
      report.checks.push({
        id,
        title,
        status: "fail",
        error: error instanceof Error ? error.message : String(error)
      });
    }
  }

  try {
    await client.connect(transport);

    await check("1", "Allowed paths com roots reais", async () => {
      const allowed = await call("vee_fs_list_allowed_paths");
      assert(!allowed.isError, "vee_fs_list_allowed_paths returned error.");
      const roots = Array.isArray(allowed.structured?.roots) ? allowed.structured.roots : [];
      assert(roots.length > 0, "No roots returned.");
      assert(roots.includes(FS_TEST_ROOT), `Expected ${FS_TEST_ROOT} in roots. Received: ${JSON.stringify(roots)}`);

      return {
        roots,
        write_paths: allowed.structured?.write_paths ?? []
      };
    });

    await check("2", "List directory real", async () => {
      const listed = await call("vee_fs_list_directory", { path: FS_TEST_ROOT, max_entries: 80 });
      assert(!listed.isError, "vee_fs_list_directory failed.");
      assert((listed.structured?.total ?? 0) > 0, `No directory entries found for ${FS_TEST_ROOT}.`);
      return { total: listed.structured?.total ?? 0 };
    });

    await check("3", "Read file real", async () => {
      const read = await call("vee_fs_read_file", {
        path: `${FS_TEST_ROOT}/MMCloud/agentes.md`,
        max_bytes: 12000
      });
      assert(!read.isError, "vee_fs_read_file failed.");
      assert(typeof read.structured?.content === "string", "No content returned from read_file.");
      return { total_bytes: read.structured?.total_bytes ?? null };
    });

    await check("4", "Search text real", async () => {
      const search = await call("vee_fs_search_text", {
        path: FS_TEST_ROOT,
        pattern: "2026",
        case_insensitive: true,
        max_matches: 20
      });
      assert(!search.isError, "vee_fs_search_text failed.");
      const totalMatches = Array.isArray(search.structured?.matches)
        ? search.structured.matches.length
        : search.structured?.total_matches ?? search.structured?.total ?? 0;
      assert(totalMatches > 0, "No matches found in vee_fs_search_text.");
      return {
        total_matches: totalMatches
      };
    });

    await check("5", "Write file em path permitido", async () => {
      const writePath = `${FS_TEST_ROOT}/Sessoes/fs-fix-validation-${Date.now()}.md`;
      const write = await call("vee_fs_write_file", {
        path: writePath,
        content: `# Filesystem Fix Validation\n\ntrace_id: ${TRACE_ID}\n`
      });
      assert(!write.isError, `vee_fs_write_file failed for ${writePath}.`);
      report.artifacts.written_file = writePath;
      return { path: writePath };
    });

    report.summary.passed = report.checks.filter((item) => item.status === "pass").length;
    report.summary.failed = report.checks.filter((item) => item.status === "fail").length;
    report.summary.ok = report.summary.failed === 0;
  } finally {
    await client.close();
    await transport.close();
  }

  const output = "mcp-server/tmp/filesystem-fix-validation-results.json";
  await writeFile(output, JSON.stringify(report, null, 2), "utf8");
  console.log(JSON.stringify(report, null, 2));
  console.log(`\nSaved report to ${output}`);
}

main().catch((error) => {
  console.error(error instanceof Error ? error.stack ?? error.message : String(error));
  process.exitCode = 1;
});
