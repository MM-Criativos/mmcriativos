import { writeFile } from "node:fs/promises";
import { Client } from "@modelcontextprotocol/sdk/client/index.js";
import { StreamableHTTPClientTransport } from "@modelcontextprotocol/sdk/client/streamableHttp.js";

const MCP_URL = process.env.PHASE1_MCP_URL ?? "http://127.0.0.1:3341/mcp";
const MCP_TOKEN = (process.env.PHASE1_MCP_TOKEN ?? "").trim();
const WORKSPACE_ROOT = process.env.PHASE1_WORKSPACE_ROOT ?? "C:\\laragon\\www\\mmcriativos";
const ENTITY_ID = Number(process.env.PHASE1_ENTITY_ID ?? 1700000001);
const TRACE_ID =
  process.env.PHASE1_TRACE_ID ??
  `phase1-${Date.now()}-${Math.random().toString(36).slice(2, 8)}`;
const SESSION_ID =
  process.env.PHASE1_SESSION_ID ??
  `phase1-validation-${Date.now()}`;

const report = {
  generated_at: new Date().toISOString(),
  mcp_url: MCP_URL,
  trace_id: TRACE_ID,
  entity_id: ENTITY_ID,
  checklist: [],
  artifacts: {},
  allowlist: {},
  filesystem: {}
};

function normalizePathLike(value) {
  return String(value ?? "")
    .trim()
    .replace(/\\/g, "/")
    .replace(/\/+$/, "")
    .toLowerCase();
}

function assert(condition, message) {
  if (!condition) {
    throw new Error(message);
  }
}

function extractStructuredContent(result) {
  if (result?.structuredContent && typeof result.structuredContent === "object") {
    return result.structuredContent;
  }

  const textItem = Array.isArray(result?.content)
    ? result.content.find((item) => item?.type === "text" && typeof item?.text === "string")
    : null;
  if (!textItem?.text) {
    return null;
  }

  try {
    return JSON.parse(textItem.text);
  } catch {
    return null;
  }
}

async function sleep(ms) {
  await new Promise((resolve) => setTimeout(resolve, ms));
}

async function main() {
  const headers = MCP_TOKEN ? { Authorization: `Bearer ${MCP_TOKEN}` } : undefined;
  const transport = new StreamableHTTPClientTransport(new URL(MCP_URL), {
    requestInit: headers ? { headers } : undefined
  });
  const client = new Client(
    { name: "phase1-validation-client", version: "1.0.0" },
    { capabilities: {} }
  );

  async function callTool(name, args = {}) {
    const result = await client.callTool({ name, arguments: args });
    const structured = extractStructuredContent(result);
    const isError = Boolean(result?.isError) || Boolean(structured?.ok === false);
    return { result, structured, isError };
  }

  async function check(id, title, fn) {
    try {
      const details = await fn();
      report.checklist.push({
        id,
        title,
        status: "pass",
        details: details ?? {}
      });
    } catch (error) {
      report.checklist.push({
        id,
        title,
        status: "fail",
        error: error instanceof Error ? error.message : String(error)
      });
    }
  }

  try {
    await client.connect(transport);
    const toolsResult = await client.listTools();
    const availableToolNames = (toolsResult.tools ?? []).map((tool) => tool.name);

    assert(availableToolNames.includes("vee_fs_list_allowed_paths"), "Tool vee_fs_list_allowed_paths not found.");
    assert(availableToolNames.includes("vee_db_query"), "Tool vee_db_query not found.");
    assert(availableToolNames.includes("vee_db_write"), "Tool vee_db_write not found.");
    assert(availableToolNames.includes("vee_db_get_timeline"), "Tool vee_db_get_timeline not found.");
    assert(
      availableToolNames.includes("vee_db_get_operational_timeline"),
      "Tool vee_db_get_operational_timeline not found."
    );

    await check("1", "Testar o workspace local completo do projeto", async () => {
      const allowed = await callTool("vee_fs_list_allowed_paths", {});
      assert(!allowed.isError, "vee_fs_list_allowed_paths returned error.");
      const roots = Array.isArray(allowed.structured?.roots) ? allowed.structured.roots : [];
      const normalizedWorkspaceRoot = normalizePathLike(WORKSPACE_ROOT);
      assert(
        roots.some((root) => normalizePathLike(root) === normalizedWorkspaceRoot),
        `Workspace root ${WORKSPACE_ROOT} not present in allowed roots.`
      );

      const listed = await callTool("vee_fs_list_directory", {
        path: WORKSPACE_ROOT,
        max_entries: 100
      });
      assert(!listed.isError, "vee_fs_list_directory returned error.");

      const searched = await callTool("vee_fs_search_text", {
        path: WORKSPACE_ROOT,
        pattern: "APP_NAME",
        include: "*.env",
        case_insensitive: false,
        max_matches: 20
      });
      assert(!searched.isError, "vee_fs_search_text returned error.");

      report.filesystem = {
        allowlist_environment: allowed.structured?.allowlist_environment ?? null,
        roots,
        write_enabled: Boolean(allowed.structured?.write_enabled),
        write_paths: Array.isArray(allowed.structured?.write_paths) ? allowed.structured.write_paths : []
      };

      return {
        root_entries: listed.structured?.total ?? 0,
        search_matches: searched.structured?.total_matches ?? searched.structured?.total ?? 0
      };
    });

    await check("2", "Testar leitura de arquivos de configuração reais", async () => {
      const readConfig = await callTool("vee_fs_read_file", {
        path: `${WORKSPACE_ROOT}\\config\\database.php`,
        max_bytes: 4000
      });
      assert(!readConfig.isError, "Failed to read config/database.php.");
      assert(typeof readConfig.structured?.content === "string", "config/database.php content not returned.");

      const readDocsOps = await callTool("vee_fs_read_file", {
        path: `${WORKSPACE_ROOT}\\docs\\operations\\deployment.md`,
        max_bytes: 4000
      });
      assert(!readDocsOps.isError, "Failed to read docs/operations/deployment.md.");
      assert(typeof readDocsOps.structured?.content === "string", "deployment.md content not returned.");

      return {
        config_bytes: readConfig.structured?.total_bytes ?? null,
        docs_bytes: readDocsOps.structured?.total_bytes ?? null
      };
    });

    await check("3", "Testar escrita em path de operação/documentação real", async () => {
      const opPath = `${WORKSPACE_ROOT}\\storage\\app\\vee\\phase1-validation-${Date.now()}.txt`;
      const docPath = `${WORKSPACE_ROOT}\\docs\\operations\\phase1-validation-smoke-${Date.now()}.md`;

      const writeOp = await callTool("vee_fs_write_file", {
        path: opPath,
        content: `phase1 validation trace_id=${TRACE_ID}\n`
      });
      assert(!writeOp.isError, "Failed to write operation file.");

      const writeDoc = await callTool("vee_fs_write_file", {
        path: docPath,
        content: `# Phase 1 Smoke\n\ntrace_id: ${TRACE_ID}\n`
      });
      assert(!writeDoc.isError, "Failed to write documentation file.");

      report.artifacts.operation_file = opPath;
      report.artifacts.documentation_file = docPath;

      return { operation_path: opPath, documentation_path: docPath };
    });

    await check("4", "Testar query em pelo menos 3 tabelas diferentes da allowlist", async () => {
      const q1 = await callTool("vee_db_query", {
        table: "projects",
        columns: ["id", "name", "slug", "status", "updated_at"],
        limit: 3,
        offset: 0
      });
      assert(!q1.isError, "Query failed for projects.");

      const q2 = await callTool("vee_db_query", {
        table: "project_tasks",
        columns: ["id", "project_id", "title", "status", "updated_at"],
        limit: 3,
        offset: 0
      });
      assert(!q2.isError, "Query failed for project_tasks.");

      const q3 = await callTool("vee_db_query", {
        table: "vee_project_events",
        columns: ["id", "event_id", "entity_type", "entity_id", "actor", "action", "created_at"],
        limit: 3,
        offset: 0
      });
      assert(!q3.isError, "Query failed for vee_project_events.");

      return {
        tables: [
          { table: "projects", rows: q1.structured?.total ?? 0 },
          { table: "project_tasks", rows: q2.structured?.total ?? 0 },
          { table: "vee_project_events", rows: q3.structured?.total ?? 0 }
        ]
      };
    });

    await check("5", "Testar vee_db_write em mais de uma tabela operacional", async () => {
      const write1 = await callTool("vee_db_write", {
        operation: "INSERT",
        table: "vee_execution_notes",
        data: {
          note_type: "summary",
          title: `Phase1 Validation ${TRACE_ID}`,
          content: "Smoke test write validation for phase 1.",
          tool_name: "phase1-validation",
          session_id: SESSION_ID
        },
        actor: "Phase1Validator",
        trace_id: TRACE_ID,
        reason: "phase1-validation: writing execution note for smoke validation"
      });
      assert(!write1.isError, "Write failed for vee_execution_notes.");

      const write2 = await callTool("vee_db_write", {
        operation: "INSERT",
        table: "vee_project_events",
        data: {
          entity_type: "phase1_validation",
          entity_id: ENTITY_ID,
          actor: "Phase1Validator",
          action: "phase1_smoke_write",
          payload: { trace_id: TRACE_ID, stage: "phase1" },
          context: { source: "phase1-validation-script", session_id: SESSION_ID }
        },
        actor: "Phase1Validator",
        trace_id: TRACE_ID,
        session_id: SESSION_ID,
        reason: "phase1-validation: writing project event for traceability verification"
      });
      assert(!write2.isError, "Write failed for vee_project_events.");

      report.artifacts.db_write_tables = ["vee_execution_notes", "vee_project_events"];
      report.artifacts.timeline_event_id_from_write = write2.structured?.timeline_event_id ?? null;

      return {
        write_1_table: write1.structured?.table ?? "vee_execution_notes",
        write_2_table: write2.structured?.table ?? "vee_project_events"
      };
    });

    await check("6", "Testar comportamento com erro de schema/campo obrigatório", async () => {
      const invalidWrite = await callTool("vee_db_write", {
        operation: "INSERT",
        table: "vee_execution_notes",
        data: {
          note_type: "summary",
          title: "Missing Content Field"
        },
        actor: "Phase1Validator",
        trace_id: TRACE_ID,
        reason: "phase1-validation: forcing required-field validation failure"
      });

      assert(invalidWrite.isError, "Expected write validation error, but operation succeeded.");

      const errorMessage =
        invalidWrite.structured?.error ??
        invalidWrite.result?.content?.find((item) => item.type === "text")?.text ??
        "unknown_error";

      return {
        expected_error_received: true,
        error_excerpt: String(errorMessage).slice(0, 220)
      };
    });

    await check("7", "Testar logs e rastreabilidade de ações executadas", async () => {
      const timeline = await callTool("vee_db_get_timeline", {
        entity_type: "phase1_validation",
        entity_id: ENTITY_ID,
        trace_id: TRACE_ID,
        limit: 20
      });
      assert(!timeline.isError, "vee_db_get_timeline failed.");

      const operationalTimeline = await callTool("vee_db_get_operational_timeline", {
        trace_id: TRACE_ID,
        limit: 50,
        include_agent_actions: true
      });
      assert(!operationalTimeline.isError, "vee_db_get_operational_timeline failed.");

      let history = null;
      for (let attempt = 0; attempt < 3; attempt += 1) {
        const historyResult = await callTool("vee_db_get_agent_execution_history", {
          tool_name: "vee_db_write",
          limit: 20
        });
        if (!historyResult.isError && (historyResult.structured?.entries?.length ?? 0) > 0) {
          history = historyResult;
          break;
        }
        await sleep(1000);
      }
      assert(history && !history.isError, "vee_db_get_agent_execution_history failed.");

      return {
        timeline_rows: timeline.structured?.returned_rows ?? timeline.structured?.total ?? 0,
        operational_timeline_rows: operationalTimeline.structured?.total ?? 0,
        agent_history_entries: history.structured?.entries?.length ?? 0
      };
    });

    await check("8", "Documentar a allowlist final e os roots autorizados", async () => {
      const allowlist = await callTool("vee_db_list_allowlist", {});
      assert(!allowlist.isError, "vee_db_list_allowlist failed.");

      const allowedPaths = await callTool("vee_fs_list_allowed_paths", {});
      assert(!allowedPaths.isError, "vee_fs_list_allowed_paths failed during documentation step.");

      report.allowlist = {
        table_count: allowlist.structured?.totals?.tables ?? 0,
        named_view_count: allowlist.structured?.totals?.named_views ?? 0,
        safe_execute: allowlist.structured?.policy_groups?.safe_execute ?? [],
        approval_required: allowlist.structured?.policy_groups?.approval_required ?? [],
        read_only: allowlist.structured?.policy_groups?.read_only ?? []
      };

      report.filesystem = {
        ...report.filesystem,
        allowlist_environment: allowedPaths.structured?.allowlist_environment ?? null,
        roots: allowedPaths.structured?.roots ?? [],
        write_enabled: Boolean(allowedPaths.structured?.write_enabled),
        write_paths: allowedPaths.structured?.write_paths ?? []
      };

      return {
        documented_table_count: report.allowlist.table_count,
        documented_named_view_count: report.allowlist.named_view_count,
        documented_root_count: Array.isArray(report.filesystem.roots)
          ? report.filesystem.roots.length
          : 0
      };
    });
  } finally {
    await transport.close().catch(() => {});
    await client.close().catch(() => {});
  }

  report.finished_at = new Date().toISOString();
  report.failed = report.checklist.filter((item) => item.status === "fail").length;
  report.passed = report.checklist.filter((item) => item.status === "pass").length;

  const outPath = "tmp/phase1-validation-results.json";
  await writeFile(outPath, `${JSON.stringify(report, null, 2)}\n`, "utf8");
  console.log(JSON.stringify({ ok: report.failed === 0, output: outPath, passed: report.passed, failed: report.failed }, null, 2));
}

main().catch(async (error) => {
  const failure = {
    generated_at: new Date().toISOString(),
    fatal_error: error instanceof Error ? error.message : String(error)
  };
  await writeFile("tmp/phase1-validation-results.json", `${JSON.stringify(failure, null, 2)}\n`, "utf8");
  console.error(error);
  process.exitCode = 1;
});
