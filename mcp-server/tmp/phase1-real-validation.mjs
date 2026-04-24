import { writeFile } from "node:fs/promises";
import { Client } from "@modelcontextprotocol/sdk/client/index.js";
import { StreamableHTTPClientTransport } from "@modelcontextprotocol/sdk/client/streamableHttp.js";

const MCP_URL = process.env.REAL_MCP_URL ?? "https://mcp.mmcriativos.cloud/mcp";
const MCP_TOKEN = (process.env.REAL_MCP_TOKEN ?? "").trim();
const TRACE_ID =
  process.env.REAL_TRACE_ID ?? `phase1-real-${Date.now()}-${Math.random().toString(36).slice(2, 8)}`;
const SESSION_ID = process.env.REAL_SESSION_ID ?? `phase1-real-session-${Date.now()}`;
const ENTITY_ID = Number(process.env.REAL_ENTITY_ID ?? 1800000001);
const ACTOR = "Phase1RealValidator";

if (!MCP_TOKEN) {
  throw new Error("REAL_MCP_TOKEN is required.");
}

const report = {
  generated_at: new Date().toISOString(),
  mcp_url: MCP_URL,
  trace_id: TRACE_ID,
  session_id: SESSION_ID,
  entity_id: ENTITY_ID,
  checks: [],
  summary: {},
  artifacts: {}
};

function assert(condition, message) {
  if (!condition) {
    throw new Error(message);
  }
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

async function sleep(ms) {
  await new Promise((resolve) => setTimeout(resolve, ms));
}

async function main() {
  const transport = new StreamableHTTPClientTransport(new URL(MCP_URL), {
    requestInit: { headers: { Authorization: `Bearer ${MCP_TOKEN}` } }
  });
  const client = new Client({ name: "phase1-real-validation", version: "1.0.0" }, { capabilities: {} });

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
      return details;
    } catch (error) {
      report.checks.push({
        id,
        title,
        status: "fail",
        error: error instanceof Error ? error.message : String(error)
      });
      return null;
    }
  }

  try {
    await client.connect(transport);

    const fsState = await check("1", "Filesystem roots e leitura/escrita reais", async () => {
      const allowed = await call("vee_fs_list_allowed_paths", {});
      assert(!allowed.isError, "vee_fs_list_allowed_paths failed.");
      assert(Array.isArray(allowed.structured?.roots), "roots missing in vee_fs_list_allowed_paths.");
      assert(
        allowed.structured.roots.length === 1 && allowed.structured.roots[0] === "/obsidian",
        `Expected roots ['/obsidian'], received ${JSON.stringify(allowed.structured.roots)}`
      );

      const listed = await call("vee_fs_list_directory", { path: "/obsidian", max_entries: 60 });
      assert(!listed.isError, "vee_fs_list_directory failed for /obsidian.");
      assert((listed.structured?.total ?? 0) > 0, "No entries returned for /obsidian.");

      const read = await call("vee_fs_read_file", { path: "/obsidian/MMCloud/agentes.md", max_bytes: 8000 });
      assert(!read.isError, "vee_fs_read_file failed for /obsidian/MMCloud/agentes.md.");
      assert(typeof read.structured?.content === "string", "Read file content missing.");

      const search = await call("vee_fs_search_text", {
        path: "/obsidian/MMCloud",
        pattern: "Diretrizes",
        max_matches: 20,
        case_insensitive: true
      });
      assert(!search.isError, "vee_fs_search_text failed for /obsidian/MMCloud.");

      const writePath = `/obsidian/Sessoes/phase1-real-validation-${Date.now()}.md`;
      const write = await call("vee_fs_write_file", {
        path: writePath,
        content: `# Phase 1 Real Validation\n\ntrace_id: ${TRACE_ID}\nsession_id: ${SESSION_ID}\n`
      });
      assert(!write.isError, `vee_fs_write_file failed for ${writePath}.`);

      report.artifacts.fs_written_file = writePath;
      return {
        roots: allowed.structured.roots,
        write_paths: allowed.structured.write_paths ?? [],
        listed_total: listed.structured.total ?? 0,
        read_bytes: read.structured.total_bytes ?? null,
        search_matches: search.structured.total_matches ?? search.structured.total ?? 0
      };
    });

    let projectId = null;
    const dbRead = await check("2", "DB query em 3 tabelas allowlisted", async () => {
      const qProjects = await call("vee_db_query", {
        table: "projects",
        columns: ["id", "name", "slug", "status", "updated_at"],
        limit: 3,
        offset: 0
      });
      assert(!qProjects.isError, "vee_db_query failed for projects.");

      const qTasks = await call("vee_db_query", {
        table: "project_tasks",
        columns: ["id", "project_id", "title", "status", "updated_at"],
        limit: 3,
        offset: 0
      });
      assert(!qTasks.isError, "vee_db_query failed for project_tasks.");

      const qEvents = await call("vee_db_query", {
        table: "vee_project_events",
        columns: ["id", "event_id", "entity_type", "entity_id", "actor", "action", "created_at"],
        limit: 3,
        offset: 0
      });
      assert(!qEvents.isError, "vee_db_query failed for vee_project_events.");

      projectId = qProjects.structured?.rows?.[0]?.id ?? null;

      return {
        projects_rows: qProjects.structured?.total ?? 0,
        project_tasks_rows: qTasks.structured?.total ?? 0,
        vee_project_events_rows: qEvents.structured?.total ?? 0,
        sampled_project_id: projectId
      };
    });

    await check("3", "Padrao obrigatorio de reason (context: justification)", async () => {
      const invalid = await call("vee_db_write", {
        operation: "INSERT",
        table: "vee_execution_notes",
        data: {
          note_type: "summary",
          title: "Phase1 Invalid Reason",
          content: "This write should fail due to reason format."
        },
        actor: ACTOR,
        trace_id: TRACE_ID,
        reason: "invalid reason"
      });
      assert(invalid.isError, "Invalid reason unexpectedly passed.");

      const invalidError = String(invalid.structured?.error ?? "");
      const invalidRawText = String(invalid.structured?.raw_text ?? "");
      const mergedInvalidMessage = `${invalidError}\n${invalidRawText}`.trim();
      const hasExpectedGuidance =
        mergedInvalidMessage.includes("context: justification") ||
        mergedInvalidMessage.includes("context: detailed justification") ||
        mergedInvalidMessage.includes("Too small");
      assert(
        hasExpectedGuidance,
        `Invalid reason message does not mention expected format. Error: ${mergedInvalidMessage}`
      );

      const valid = await call("vee_db_write", {
        operation: "INSERT",
        table: "vee_execution_notes",
        data: {
          note_type: "summary",
          title: `Phase1 Valid Reason ${TRACE_ID}`,
          content: "Valid reason format verification."
        },
        actor: ACTOR,
        trace_id: TRACE_ID,
        reason: "phase1_validation: validating reason format acceptance for operational write"
      });
      assert(!valid.isError, "Valid reason write failed.");

      report.artifacts.reason_validation_note_id = valid.structured?.after?.id ?? null;
      return {
        invalid_reason_error: mergedInvalidMessage.slice(0, 220),
        valid_reason_accepted: true
      };
    });

    let updateWhereId = null;
    let upsertNoteId = `phase1-upsert-${Date.now()}`;
    await check("4", "vee_db_write com INSERT/UPDATE/UPSERT e payload data", async () => {
      const insert = await call("vee_db_write", {
        operation: "INSERT",
        table: "vee_execution_notes",
        data: {
          note_type: "analysis",
          title: `Phase1 Real Insert ${TRACE_ID}`,
          content: "Insert validation in real environment.",
          tool_name: "phase1-real-validation",
          session_id: SESSION_ID
        },
        actor: ACTOR,
        trace_id: TRACE_ID,
        reason: "phase1_validation: validating INSERT with payload data in vee_execution_notes"
      });
      assert(!insert.isError, "INSERT failed on vee_execution_notes.");
      updateWhereId = insert.structured?.after?.id ?? null;
      assert(updateWhereId !== null, "INSERT did not return row id for UPDATE test.");

      const update = await call("vee_db_write", {
        operation: "UPDATE",
        table: "vee_execution_notes",
        data: {
          title: `Phase1 Real Updated ${TRACE_ID}`,
          content: "Updated content for UPDATE validation."
        },
        where: [{ column: "id", operator: "=", value: updateWhereId }],
        actor: ACTOR,
        trace_id: TRACE_ID,
        reason: "phase1_validation: validating UPDATE with payload data and safe where clause"
      });
      assert(!update.isError, "UPDATE failed on vee_execution_notes.");

      const upsert = await call("vee_db_write", {
        operation: "UPSERT",
        table: "vee_execution_notes",
        data: {
          note_id: upsertNoteId,
          note_type: "summary",
          title: `Phase1 Real Upsert ${TRACE_ID}`,
          content: "Upsert validation in real environment.",
          tool_name: "phase1-real-validation",
          session_id: SESSION_ID
        },
        upsert_key: ["note_id"],
        actor: ACTOR,
        trace_id: TRACE_ID,
        reason: "phase1_validation: validating UPSERT with payload data and explicit upsert key"
      });
      assert(!upsert.isError, "UPSERT failed on vee_execution_notes.");

      report.artifacts.insert_note_pk = updateWhereId;
      report.artifacts.upsert_note_id = upsertNoteId;
      report.artifacts.upsert_table = upsert.structured?.table ?? "vee_execution_notes";

      return {
        insert_ok: true,
        update_ok: true,
        upsert_ok: true
      };
    });

    await check("5", "Erro de schema/campo obrigatorio", async () => {
      const missingField = await call("vee_db_write", {
        operation: "INSERT",
        table: "vee_execution_notes",
        data: {
          note_type: "summary",
          title: "Missing content field"
        },
        actor: ACTOR,
        trace_id: TRACE_ID,
        reason: "phase1_validation: forcing required field check for schema validation"
      });
      assert(missingField.isError, "Expected missing required field error.");
      const msg = String(missingField.structured?.error ?? "");
      assert(msg.includes("Missing required fields"), `Unexpected required field error: ${msg}`);
      return { required_field_error: msg.slice(0, 220) };
    });

    let recordedProjectId = projectId;
    await check("6", "Timeline: record_event + leitura por entidade/projeto/sessao", async () => {
      if (!recordedProjectId) {
        recordedProjectId = 1;
      }

      const recordEvent = await call("vee_db_record_event", {
        entity_type: "phase1_real_validation",
        entity_id: ENTITY_ID,
        actor: ACTOR,
        action: "phase1_real_event_recorded",
        payload: {
          trace_id: TRACE_ID,
          stage: "phase1",
          marker: "timeline_validation"
        },
        context: {
          source: "phase1-real-validation",
          source_tool: "vee_db_record_event"
        },
        trace_id: TRACE_ID,
        project_id: recordedProjectId,
        session_id: SESSION_ID
      });
      assert(!recordEvent.isError, "vee_db_record_event failed.");

      const timelineByEntity = await call("vee_db_get_timeline", {
        entity_type: "phase1_real_validation",
        entity_id: ENTITY_ID,
        trace_id: TRACE_ID,
        limit: 20
      });
      assert(!timelineByEntity.isError, "vee_db_get_timeline failed for entity filter.");
      assert((timelineByEntity.structured?.returned_rows ?? timelineByEntity.structured?.total ?? 0) > 0, "Entity timeline returned no rows.");

      const timelineByProjectSession = await call("vee_db_get_timeline", {
        project_id: recordedProjectId,
        session_id: SESSION_ID,
        trace_id: TRACE_ID,
        limit: 20
      });
      assert(!timelineByProjectSession.isError, "vee_db_get_timeline failed for project/session filter.");
      assert(
        (timelineByProjectSession.structured?.returned_rows ?? timelineByProjectSession.structured?.total ?? 0) > 0,
        "Project/session timeline returned no rows."
      );

      const operational = await call("vee_db_get_operational_timeline", {
        trace_id: TRACE_ID,
        project_id: recordedProjectId,
        session_id: SESSION_ID,
        limit: 40
      });
      assert(!operational.isError, "vee_db_get_operational_timeline failed.");

      report.artifacts.timeline_event_id = recordEvent.structured?.after?.event_id ?? null;

      return {
        timeline_entity_rows: timelineByEntity.structured?.returned_rows ?? timelineByEntity.structured?.total ?? 0,
        timeline_project_session_rows:
          timelineByProjectSession.structured?.returned_rows ?? timelineByProjectSession.structured?.total ?? 0,
        operational_rows: operational.structured?.total ?? 0
      };
    });

    await check("7", "Logs e rastreabilidade de acoes", async () => {
      let history = null;
      for (let i = 0; i < 4; i += 1) {
        const attempt = await call("vee_db_get_agent_execution_history", {
          tool_name: "vee_db_write",
          limit: 20
        });
        if (!attempt.isError) {
          history = attempt;
          break;
        }
        await sleep(1200);
      }

      assert(history && !history.isError, "vee_db_get_agent_execution_history failed.");
      const entries = Array.isArray(history.structured?.entries) ? history.structured.entries : [];
      return {
        agent_history_entries: entries.length,
        note: entries.length === 0
          ? "history endpoint respondeu com sucesso, mas a tabela de auditoria está vazia neste ambiente"
          : "history endpoint retornou registros"
      };
    });

    await check("8", "Allowlist final documentada no runtime", async () => {
      const allowlist = await call("vee_db_list_allowlist", {});
      assert(!allowlist.isError, "vee_db_list_allowlist failed.");
      assert((allowlist.structured?.totals?.tables ?? 0) >= 1, "Allowlist table count invalid.");

      report.summary = {
        allowlist_tables: allowlist.structured?.totals?.tables ?? 0,
        allowlist_named_views: allowlist.structured?.totals?.named_views ?? 0,
        safe_execute: allowlist.structured?.policy_groups?.safe_execute ?? [],
        approval_required: allowlist.structured?.policy_groups?.approval_required ?? [],
        read_only: allowlist.structured?.policy_groups?.read_only ?? []
      };

      return {
        tables: report.summary.allowlist_tables,
        named_views: report.summary.allowlist_named_views
      };
    });
  } finally {
    await transport.close().catch(() => {});
    await client.close().catch(() => {});
  }

  report.finished_at = new Date().toISOString();
  report.passed = report.checks.filter((check) => check.status === "pass").length;
  report.failed = report.checks.filter((check) => check.status === "fail").length;

  const outputPath = "tmp/phase1-real-validation-results.json";
  await writeFile(outputPath, `${JSON.stringify(report, null, 2)}\n`, "utf8");
  console.log(JSON.stringify({ ok: report.failed === 0, output: outputPath, passed: report.passed, failed: report.failed }, null, 2));
}

main().catch(async (error) => {
  const failure = {
    generated_at: new Date().toISOString(),
    fatal_error: error instanceof Error ? error.message : String(error)
  };
  await writeFile("tmp/phase1-real-validation-results.json", `${JSON.stringify(failure, null, 2)}\n`, "utf8");
  console.error(error);
  process.exitCode = 1;
});
