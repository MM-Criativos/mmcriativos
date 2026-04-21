import "dotenv/config";

import { randomUUID } from "node:crypto";
import { createMcpExpressApp } from "@modelcontextprotocol/sdk/server/express.js";
import { McpServer } from "@modelcontextprotocol/sdk/server/mcp.js";
import { StreamableHTTPServerTransport } from "@modelcontextprotocol/sdk/server/streamableHttp.js";
import type { NextFunction, Request, Response } from "express";
import { z } from "zod";
import { DatabaseReadOnlyAdapter } from "./adapters/databaseReadOnlyAdapter.js";
import { DatabaseWriteAdapter } from "./adapters/databaseWriteAdapter.js";
import { N8nRestAdapter } from "./adapters/n8nRestAdapter.js";
import { ObsidianAdapter } from "./adapters/obsidianAdapter.js";
import { ServerSshAdapter } from "./adapters/serverSshAdapter.js";
import { VeeControlAdapter } from "./adapters/veeControlAdapter.js";
import { resolveTablePolicy } from "./adapters/queryAllowlist.js";
import type { WhereCondition } from "./adapters/structuredQueryBuilder.js";
import {
  applyWorkflowOperations,
  parseWorkflowOperations,
  summarizeWorkflowDiff,
  type WorkflowDiffSummary,
  type WorkflowRecord
} from "./workflowTools.js";

type CapabilityDescriptor = {
  name: string;
  phase: "v0.1" | "v0.2" | "v0.3" | "future";
  permission: "read_only" | "safe_execute" | "approval_required" | "restricted";
  status: "enabled" | "planned";
};

type ToolResult = {
  content: Array<{ type: "text"; text: string }>;
  structuredContent?: Record<string, unknown>;
  isError?: boolean;
};

type WorkflowApprovalPayload = {
  workflow_id: string;
  workflow: Record<string, unknown>;
  previous_workflow?: Record<string, unknown>;
  change_summary?: string;
  diff_summary?: WorkflowDiffSummary;
  [key: string]: unknown;
};

const APP_NAME = "vee-mcp-server";
const APP_VERSION = process.env.VEE_MCP_VERSION ?? "0.1.0";
const PORT = Number.parseInt(process.env.PORT ?? "3333", 10);
const HOST = process.env.HOST ?? "127.0.0.1";
const AUTH_TOKEN = (process.env.MCP_AUTH_TOKEN ?? "").trim();
const N8N_BASE_URL = (process.env.N8N_BASE_URL ?? "https://n8n.mmcriativos.cloud/api/v1").trim();
const N8N_API_KEY = (process.env.N8N_API_KEY ?? "").trim();
const N8N_TIMEOUT_MS = Number.parseInt(process.env.N8N_TIMEOUT_MS ?? "20000", 10);
const N8N_WRITE_ENABLED = (process.env.N8N_WRITE_ENABLED ?? "false").trim().toLowerCase() === "true";
const VEE_AUDIT_URL = (process.env.VEE_AUDIT_URL ?? "").trim();
const VEE_AUDIT_TOKEN = (process.env.VEE_AUDIT_TOKEN ?? "").trim();
const VEE_CONTROL_BASE_URL = (process.env.VEE_CONTROL_BASE_URL ?? "").trim();
const VEE_CONTROL_TOKEN = (process.env.VEE_CONTROL_TOKEN ?? VEE_AUDIT_TOKEN).trim();
const VEE_CONTROL_TIMEOUT_MS = Number.parseInt(process.env.VEE_CONTROL_TIMEOUT_MS ?? "20000", 10);
const OBSIDIAN_ROOT_PATH = (process.env.OBSIDIAN_ROOT_PATH ?? "").trim();
const OBSIDIAN_MAX_FILE_BYTES = Number.parseInt(process.env.OBSIDIAN_MAX_FILE_BYTES ?? "524288", 10);
const OBSIDIAN_WRITE_ENABLED = (process.env.OBSIDIAN_WRITE_ENABLED ?? "false").trim().toLowerCase() === "true";
const OBSIDIAN_WRITE_ALLOWED_FOLDERS = (process.env.OBSIDIAN_WRITE_ALLOWED_FOLDERS ?? "")
  .split(",")
  .map(s => s.trim())
  .filter(Boolean);
const SERVER_SSH_HOST = (process.env.SERVER_SSH_HOST ?? "").trim();
const SERVER_SSH_PORT = Number.parseInt(process.env.SERVER_SSH_PORT ?? "22", 10);
const SERVER_SSH_USERNAME = (process.env.SERVER_SSH_USERNAME ?? "").trim();
const SERVER_SSH_PASSWORD = process.env.SERVER_SSH_PASSWORD ?? "";
const SERVER_SSH_PRIVATE_KEY_PATH = (process.env.SERVER_SSH_PRIVATE_KEY_PATH ?? "").trim();
const SERVER_SSH_READY_TIMEOUT_MS = Number.parseInt(
  process.env.SERVER_SSH_READY_TIMEOUT_MS ?? "15000",
  10
);
const SERVER_SSH_COMMAND_TIMEOUT_MS = Number.parseInt(
  process.env.SERVER_SSH_COMMAND_TIMEOUT_MS ?? "20000",
  10
);
const SERVER_SSH_RESTART_ENABLED =
  (process.env.SERVER_SSH_RESTART_ENABLED ?? "false").trim().toLowerCase() === "true";
const SERVER_SSH_ALLOWED_CONTAINERS = (process.env.SERVER_SSH_ALLOWED_CONTAINERS ?? "")
  .split(",")
  .map(s => s.trim())
  .filter(Boolean);
const SERVER_SSH_ALLOWED_SERVICES = (process.env.SERVER_SSH_ALLOWED_SERVICES ?? "")
  .split(",")
  .map(s => s.trim())
  .filter(Boolean);
const SERVER_SSH_ALLOWED_LOG_PATHS = (process.env.SERVER_SSH_ALLOWED_LOG_PATHS ?? "")
  .split(",")
  .map(s => s.trim())
  .filter(Boolean);
const CLAUDE_MCP_PUBLIC_URL = (process.env.CLAUDE_MCP_PUBLIC_URL ?? "").trim();
const FS_ALLOWED_ROOTS = (process.env.FS_ALLOWED_ROOTS ?? "")
  .split(",")
  .map(s => s.trim())
  .filter(Boolean);
const FS_MAX_FILE_BYTES = Number.parseInt(process.env.FS_MAX_FILE_BYTES ?? "262144", 10);
const FS_WRITE_ENABLED = (process.env.FS_WRITE_ENABLED ?? "false").trim().toLowerCase() === "true";
const FS_WRITE_ALLOWED_PATHS = (process.env.FS_WRITE_ALLOWED_PATHS ?? "")
  .split(",")
  .map(s => s.trim())
  .filter(Boolean);
const DB_READONLY_HOST = (process.env.DB_READONLY_HOST ?? "").trim();
const DB_READONLY_PORT = Number.parseInt(process.env.DB_READONLY_PORT ?? "3306", 10);
const DB_READONLY_USER = (process.env.DB_READONLY_USER ?? "").trim();
const DB_READONLY_PASSWORD = process.env.DB_READONLY_PASSWORD ?? "";
const DB_READONLY_DATABASE = (process.env.DB_READONLY_DATABASE ?? "").trim();
const DB_READONLY_MAX_ROWS = Number.parseInt(process.env.DB_READONLY_MAX_ROWS ?? "100", 10);
const DB_READONLY_RATE_LIMIT = Number.parseInt(process.env.DB_READONLY_RATE_LIMIT ?? "10", 10);
const DB_WRITE_HOST = (process.env.DB_WRITE_HOST ?? "").trim();
const DB_WRITE_PORT = Number.parseInt(process.env.DB_WRITE_PORT ?? "3306", 10);
const DB_WRITE_USER = (process.env.DB_WRITE_USER ?? "").trim();
const DB_WRITE_PASSWORD = process.env.DB_WRITE_PASSWORD ?? "";
const DB_WRITE_DATABASE = (process.env.DB_WRITE_DATABASE ?? "").trim();
const DB_WRITE_ENABLED = (process.env.DB_WRITE_ENABLED ?? "false").trim().toLowerCase() === "true";

const n8nAdapter = new N8nRestAdapter({
  baseUrl: N8N_BASE_URL,
  apiKey: N8N_API_KEY,
  timeoutMs: Number.isNaN(N8N_TIMEOUT_MS) ? 20000 : N8N_TIMEOUT_MS
});

const veeControlAdapter = new VeeControlAdapter({
  baseUrl: VEE_CONTROL_BASE_URL,
  token: VEE_CONTROL_TOKEN,
  timeoutMs: Number.isNaN(VEE_CONTROL_TIMEOUT_MS) ? 20000 : VEE_CONTROL_TIMEOUT_MS
});

const obsidianAdapter = new ObsidianAdapter({
  rootPath: OBSIDIAN_ROOT_PATH,
  maxFileBytes: Number.isNaN(OBSIDIAN_MAX_FILE_BYTES) ? 524288 : OBSIDIAN_MAX_FILE_BYTES,
  writeEnabled: OBSIDIAN_WRITE_ENABLED,
  allowedWriteFolders: OBSIDIAN_WRITE_ALLOWED_FOLDERS
});

const serverSshAdapter = new ServerSshAdapter({
  host: SERVER_SSH_HOST,
  port: Number.isNaN(SERVER_SSH_PORT) ? 22 : SERVER_SSH_PORT,
  username: SERVER_SSH_USERNAME,
  password: SERVER_SSH_PASSWORD,
  privateKeyPath: SERVER_SSH_PRIVATE_KEY_PATH,
  readyTimeoutMs: Number.isNaN(SERVER_SSH_READY_TIMEOUT_MS) ? 15000 : SERVER_SSH_READY_TIMEOUT_MS,
  commandTimeoutMs: Number.isNaN(SERVER_SSH_COMMAND_TIMEOUT_MS) ? 20000 : SERVER_SSH_COMMAND_TIMEOUT_MS,
  restartEnabled: SERVER_SSH_RESTART_ENABLED,
  allowedContainers: SERVER_SSH_ALLOWED_CONTAINERS,
  allowedServices: SERVER_SSH_ALLOWED_SERVICES,
  allowedLogPaths: SERVER_SSH_ALLOWED_LOG_PATHS,
  fsAllowedRoots: FS_ALLOWED_ROOTS,
  fsMaxFileBytes: Number.isNaN(FS_MAX_FILE_BYTES) ? 262144 : FS_MAX_FILE_BYTES,
  fsWriteEnabled: FS_WRITE_ENABLED,
  fsWriteAllowedPaths: FS_WRITE_ALLOWED_PATHS
});

const dbReadOnlyAdapter = new DatabaseReadOnlyAdapter({
  host: DB_READONLY_HOST,
  port: Number.isNaN(DB_READONLY_PORT) ? 3306 : DB_READONLY_PORT,
  user: DB_READONLY_USER,
  password: DB_READONLY_PASSWORD,
  database: DB_READONLY_DATABASE,
  maxRowsPerQuery: Number.isNaN(DB_READONLY_MAX_ROWS) ? 100 : Math.max(1, Math.min(DB_READONLY_MAX_ROWS, 100)),
  rateLimitPerMinute: Number.isNaN(DB_READONLY_RATE_LIMIT) ? 10 : Math.max(1, DB_READONLY_RATE_LIMIT)
});

const dbWriteAdapter = new DatabaseWriteAdapter({
  host: DB_WRITE_HOST,
  port: Number.isNaN(DB_WRITE_PORT) ? 3306 : DB_WRITE_PORT,
  user: DB_WRITE_USER,
  password: DB_WRITE_PASSWORD,
  database: DB_WRITE_DATABASE,
  enabled: DB_WRITE_ENABLED
});

const capabilityCatalog: CapabilityDescriptor[] = [
  { name: "vee_health", phase: "v0.1", permission: "read_only", status: "enabled" },
  { name: "vee_list_capabilities", phase: "v0.1", permission: "read_only", status: "enabled" },
  { name: "vee_n8n_list_workflows", phase: "v0.1", permission: "read_only", status: "enabled" },
  { name: "vee_n8n_get_workflow", phase: "v0.1", permission: "read_only", status: "enabled" },
  { name: "vee_n8n_preview_workflow_diff", phase: "v0.2", permission: "read_only", status: "enabled" },
  { name: "vee_n8n_list_recent_executions", phase: "v0.1", permission: "read_only", status: "enabled" },
  { name: "vee_n8n_get_execution", phase: "v0.2", permission: "read_only", status: "enabled" },
  { name: "vee_n8n_retry_execution", phase: "v0.2", permission: "safe_execute", status: "enabled" },
  { name: "vee_n8n_stop_execution", phase: "v0.2", permission: "safe_execute", status: "enabled" },
  {
    name: "vee_n8n_update_workflow",
    phase: "v0.2",
    permission: "approval_required",
    status: "enabled"
  },
  {
    name: "vee_n8n_patch_workflow_nodes",
    phase: "v0.2",
    permission: "approval_required",
    status: "enabled"
  },
  {
    name: "vee_n8n_rollback_workflow",
    phase: "v0.2",
    permission: "approval_required",
    status: "enabled"
  },
  { name: "vee_obsidian_health", phase: "v0.2", permission: "read_only", status: "enabled" },
  { name: "vee_obsidian_search", phase: "v0.2", permission: "read_only", status: "enabled" },
  { name: "vee_obsidian_read_note", phase: "v0.2", permission: "read_only", status: "enabled" },
  { name: "vee_obsidian_create_note", phase: "v0.2", permission: "safe_execute", status: "enabled" },
  { name: "vee_obsidian_append_to_note", phase: "v0.2", permission: "safe_execute", status: "enabled" },
  { name: "vee_obsidian_update_note_section", phase: "v0.2", permission: "safe_execute", status: "enabled" },
  { name: "vee_obsidian_append_to_daily_log", phase: "v0.2", permission: "safe_execute", status: "enabled" },
  { name: "vee_obsidian_create_task_note", phase: "v0.2", permission: "safe_execute", status: "enabled" },
  { name: "vee_server_status", phase: "v0.2", permission: "read_only", status: "enabled" },
  { name: "vee_server_list_containers", phase: "v0.2", permission: "read_only", status: "enabled" },
  { name: "vee_server_get_container_logs", phase: "v0.2", permission: "safe_execute", status: "enabled" },
  { name: "vee_server_disk_usage", phase: "v0.2", permission: "read_only", status: "enabled" },
  { name: "vee_server_memory_usage", phase: "v0.2", permission: "read_only", status: "enabled" },
  { name: "vee_server_list_containers_detailed", phase: "v0.2", permission: "read_only", status: "enabled" },
  { name: "vee_server_inspect_container", phase: "v0.2", permission: "read_only", status: "enabled" },
  { name: "vee_server_restart_container", phase: "v0.2", permission: "approval_required", status: "enabled" },
  { name: "vee_server_tail_log", phase: "v0.2", permission: "read_only", status: "enabled" },
  { name: "vee_server_service_status", phase: "v0.2", permission: "read_only", status: "enabled" },
  { name: "vee_server_health_check", phase: "v0.2", permission: "read_only", status: "enabled" },
  { name: "vee_server_list_allowed_paths", phase: "v0.2", permission: "read_only", status: "enabled" },
  { name: "vee_claude_connection_info", phase: "v0.2", permission: "read_only", status: "enabled" },
  { name: "vee_db_get_tenant_by_slug", phase: "v0.2", permission: "read_only", status: "enabled" },
  { name: "vee_db_get_client_by_phone", phase: "v0.2", permission: "read_only", status: "enabled" },
  { name: "vee_db_get_recent_ai_messages", phase: "v0.2", permission: "read_only", status: "enabled" },
  { name: "vee_db_get_context_state", phase: "v0.2", permission: "read_only", status: "enabled" },
  { name: "vee_db_get_booking_session", phase: "v0.2", permission: "read_only", status: "enabled" },
  { name: "vee_db_get_recent_appointments", phase: "v0.2", permission: "read_only", status: "enabled" },
  { name: "vee_db_get_project_summary", phase: "v0.2", permission: "read_only", status: "enabled" },
  { name: "vee_db_get_pending_tasks", phase: "v0.2", permission: "read_only", status: "enabled" },
  { name: "vee_db_get_user_preferences", phase: "v0.2", permission: "read_only", status: "enabled" },
  { name: "vee_db_get_agent_execution_history", phase: "v0.2", permission: "read_only", status: "enabled" },
  { name: "vee_db_update_project_status", phase: "v0.2", permission: "safe_execute", status: "enabled" },
  { name: "vee_db_create_internal_task", phase: "v0.2", permission: "safe_execute", status: "enabled" },
  { name: "vee_db_save_execution_note", phase: "v0.2", permission: "safe_execute", status: "enabled" },
  { name: "vee_db_update_context_state", phase: "v0.2", permission: "safe_execute", status: "enabled" },
  { name: "vee_db_register_incident", phase: "v0.2", permission: "safe_execute", status: "enabled" },
  { name: "vee_db_attach_task_to_project", phase: "v0.2", permission: "safe_execute", status: "enabled" },
  { name: "vee_db_mark_task_as_blocked", phase: "v0.2", permission: "safe_execute", status: "enabled" },
  { name: "vee_db_mark_task_as_done", phase: "v0.2", permission: "safe_execute", status: "enabled" },
  { name: "vee_mmcc_list_tenants", phase: "v0.1", permission: "read_only", status: "planned" },
  { name: "vee_fs_list_directory", phase: "v0.2", permission: "read_only", status: "enabled" },
  { name: "vee_fs_read_file", phase: "v0.2", permission: "read_only", status: "enabled" },
  { name: "vee_fs_search_text", phase: "v0.2", permission: "read_only", status: "enabled" },
  { name: "vee_fs_write_file", phase: "v0.2", permission: "safe_execute", status: "enabled" },
  { name: "vee_fs_list_allowed_paths", phase: "v0.2", permission: "read_only", status: "enabled" },
  { name: "vee_db_query", phase: "v0.3", permission: "read_only", status: "enabled" },
  { name: "vee_db_write", phase: "v0.3", permission: "safe_execute", status: "enabled" },
  { name: "vee_db_record_event", phase: "v0.3", permission: "safe_execute", status: "enabled" },
  { name: "vee_db_record_decision", phase: "v0.3", permission: "safe_execute", status: "enabled" },
  { name: "vee_db_get_timeline", phase: "v0.3", permission: "read_only", status: "enabled" }
];

function isRecord(value: unknown): value is Record<string, unknown> {
  return !!value && typeof value === "object" && !Array.isArray(value);
}

function toToolError(error: unknown, context: string): ToolResult {
  const message = error instanceof Error ? error.message : String(error);
  return {
    isError: true,
    content: [{ type: "text", text: `${context}: ${message}` }],
    structuredContent: {
      ok: false,
      context,
      error: message
    }
  };
}

function withAuth(req: Request, res: Response, next: NextFunction): void {
  if (!AUTH_TOKEN) {
    next();
    return;
  }

  const authorization = req.header("authorization") ?? "";
  if (authorization !== `Bearer ${AUTH_TOKEN}`) {
    res.status(401).json({
      error: "unauthorized",
      message: "Invalid or missing Bearer token."
    });
    return;
  }

  next();
}

function getHealthPayload() {
  return {
    name: APP_NAME,
    version: APP_VERSION,
    status: "ok",
    now: new Date().toISOString(),
    mode: AUTH_TOKEN ? "protected" : "open"
  };
}

function getCapabilitiesPayload() {
  return {
    server: {
      name: APP_NAME,
      version: APP_VERSION
    },
    tools: capabilityCatalog
  };
}

function getClaudeConnectionPayload() {
  const endpoint =
    CLAUDE_MCP_PUBLIC_URL ||
    (HOST === "0.0.0.0" || HOST === "127.0.0.1"
      ? `http://localhost:${PORT}/mcp`
      : `http://${HOST}:${PORT}/mcp`);

  return {
    endpoint,
    requires_bearer_auth: AUTH_TOKEN.length > 0,
    required_header: AUTH_TOKEN.length > 0 ? "Authorization: Bearer <MCP_AUTH_TOKEN>" : null,
    transport: "streamable-http",
    note: "Use this endpoint in Claude MCP connector settings."
  };
}

function compactWorkflowPayload(workflow: Record<string, unknown>): Record<string, unknown> {
  return {
    id: workflow.id ?? null,
    name: workflow.name ?? null,
    active: workflow.active ?? null,
    nodes_count: Array.isArray(workflow.nodes) ? workflow.nodes.length : null,
    connections_count:
      workflow.connections && typeof workflow.connections === "object"
        ? Object.keys(workflow.connections).length
        : null
  };
}

function compactAuditPayload(payload: unknown): unknown {
  if (payload === null || payload === undefined) {
    return payload;
  }

  if (typeof payload === "string") {
    return payload.length > 2000 ? `${payload.slice(0, 2000)}...` : payload;
  }

  if (Array.isArray(payload)) {
    const limited = payload.slice(0, 50);
    return limited.map((item) => compactAuditPayload(item));
  }

  if (typeof payload === "object") {
    const source = payload as Record<string, unknown>;
    const compacted: Record<string, unknown> = {};

    for (const [key, value] of Object.entries(source)) {
      if (
        (key === "workflow" || key === "previous_workflow") &&
        value &&
        typeof value === "object" &&
        !Array.isArray(value)
      ) {
        compacted[key] = compactWorkflowPayload(value as Record<string, unknown>);
        continue;
      }

      compacted[key] = compactAuditPayload(value);
    }

    return compacted;
  }

  return payload;
}

function getToolErrorMessage(result: ToolResult): string | null {
  if (!result.isError) {
    return null;
  }

  if (result.structuredContent && typeof result.structuredContent.error === "string") {
    return result.structuredContent.error;
  }

  const firstContent = result.content[0];
  return firstContent?.text ?? "Unknown tool error";
}

async function sendAuditEvent(payload: Record<string, unknown>): Promise<void> {
  if (!VEE_AUDIT_URL) {
    return;
  }

  const headers: Record<string, string> = {
    "Content-Type": "application/json",
    Accept: "application/json"
  };

  if (VEE_AUDIT_TOKEN) {
    headers["X-VEE-Internal-Token"] = VEE_AUDIT_TOKEN;
  }

  try {
    const response = await fetch(VEE_AUDIT_URL, {
      method: "POST",
      headers,
      body: JSON.stringify(payload)
    });

    if (!response.ok) {
      const body = await response.text();
      console.error(`[Vee MCP][audit] HTTP ${response.status}: ${body.slice(0, 300)}`);
    }
  } catch (error) {
    const message = error instanceof Error ? error.message : String(error);
    console.error(`[Vee MCP][audit] request failed: ${message}`);
  }
}

async function runAuditedTool(options: {
  toolName: string;
  permission: CapabilityDescriptor["permission"];
  isWrite: boolean;
  args: unknown;
  errorContext: string;
  handler: () => Promise<ToolResult>;
}): Promise<ToolResult> {
  const callId = randomUUID();
  const startedAt = Date.now();

  try {
    const result = await options.handler();
    const durationMs = Date.now() - startedAt;
    const status = result.isError ? "error" : "success";

    await sendAuditEvent({
      call_id: callId,
      tool_name: options.toolName,
      status,
      permission_level: options.permission,
      is_write: options.isWrite,
      duration_ms: durationMs,
      request_payload: compactAuditPayload(options.args),
      response_payload: compactAuditPayload(result.structuredContent ?? null),
      error_message: getToolErrorMessage(result),
      meta: {
        app_name: APP_NAME,
        app_version: APP_VERSION
      }
    });

    return result;
  } catch (error) {
    const result = toToolError(error, options.errorContext);
    const durationMs = Date.now() - startedAt;

    await sendAuditEvent({
      call_id: callId,
      tool_name: options.toolName,
      status: "error",
      permission_level: options.permission,
      is_write: options.isWrite,
      duration_ms: durationMs,
      request_payload: compactAuditPayload(options.args),
      response_payload: compactAuditPayload(result.structuredContent ?? null),
      error_message: getToolErrorMessage(result),
      meta: {
        app_name: APP_NAME,
        app_version: APP_VERSION
      }
    });

    return result;
  }
}

function assertN8nWriteAllowed(): void {
  if (!N8N_WRITE_ENABLED) {
    throw new Error("N8N writes are disabled. Set N8N_WRITE_ENABLED=true to allow write operations.");
  }
}

function ensureWorkflowRecord(value: unknown, fieldName: string): WorkflowRecord {
  if (!isRecord(value)) {
    throw new Error(`${fieldName} must be an object.`);
  }

  return value;
}

function parseWorkflowApprovalPayload(approvalId: string, payload: unknown): WorkflowApprovalPayload {
  if (!isRecord(payload)) {
    throw new Error(`Approval ${approvalId} has no valid request_payload.`);
  }

  const workflowId = typeof payload.workflow_id === "string" ? payload.workflow_id.trim() : "";
  if (!workflowId) {
    throw new Error(`Approval ${approvalId} payload is missing workflow_id.`);
  }

  const workflow = ensureWorkflowRecord(payload.workflow, `Approval ${approvalId}.workflow`);
  const previousWorkflow = isRecord(payload.previous_workflow) ? payload.previous_workflow : undefined;
  const changeSummary = typeof payload.change_summary === "string" ? payload.change_summary : undefined;
  const diffSummary = isRecord(payload.diff_summary)
    ? (payload.diff_summary as WorkflowDiffSummary)
    : undefined;

  return {
    ...payload,
    workflow_id: workflowId,
    workflow,
    previous_workflow: previousWorkflow,
    change_summary: changeSummary,
    diff_summary: diffSummary
  };
}

function buildApprovalPendingResult(approvalId: string): ToolResult {
  return {
    isError: true,
    content: [{ type: "text", text: `Approval ${approvalId} is still pending.` }],
    structuredContent: {
      ok: false,
      approval_required: true,
      approval_id: approvalId,
      status: "pending"
    }
  };
}

function buildApprovalRejectedResult(approvalId: string): ToolResult {
  return {
    isError: true,
    content: [{ type: "text", text: `Approval ${approvalId} was rejected.` }],
    structuredContent: {
      ok: false,
      approval_id: approvalId,
      status: "rejected"
    }
  };
}

function buildApprovalExecutedResult(approvalId: string): ToolResult {
  return {
    content: [
      {
        type: "text",
        text: JSON.stringify(
          {
            ok: true,
            approval_id: approvalId,
            status: "executed",
            message: "Approval already executed."
          },
          null,
          2
        )
      }
    ],
    structuredContent: {
      ok: true,
      approval_id: approvalId,
      status: "executed"
    }
  };
}

function buildApprovalCreationResult(options: {
  approvalId: string;
  status: string;
  message: string;
  diffSummary?: WorkflowDiffSummary;
  extra?: Record<string, unknown>;
}): ToolResult {
  const payload: Record<string, unknown> = {
    ok: false,
    approval_required: true,
    approval_id: options.approvalId,
    status: options.status,
    message: options.message
  };

  if (options.diffSummary) {
    payload.diff_summary = options.diffSummary;
  }
  if (options.extra) {
    Object.assign(payload, options.extra);
  }

  return {
    content: [{ type: "text", text: JSON.stringify(payload, null, 2) }],
    structuredContent: payload
  };
}

async function createWorkflowApprovalRequest(options: {
  actionName: string;
  toolName: string;
  workflowId: string;
  workflow: WorkflowRecord;
  previousWorkflow: WorkflowRecord;
  changeSummary: string;
  diffSummary?: WorkflowDiffSummary;
  extraPayload?: Record<string, unknown>;
  extraMeta?: Record<string, unknown>;
}): Promise<{ approval_id: string; status: string }> {
  const requestPayload: WorkflowApprovalPayload = {
    workflow_id: options.workflowId,
    workflow: options.workflow,
    previous_workflow: options.previousWorkflow,
    change_summary: options.changeSummary
  };

  if (options.diffSummary) {
    requestPayload.diff_summary = options.diffSummary;
  }
  if (options.extraPayload) {
    Object.assign(requestPayload, options.extraPayload);
  }

  const meta = {
    source: "vee-mcp-server",
    requested_at: new Date().toISOString(),
    ...(options.extraMeta ?? {})
  };

  const approval = await veeControlAdapter.createApproval({
    action_name: options.actionName,
    tool_name: options.toolName,
    summary: options.changeSummary,
    request_payload: requestPayload,
    meta
  });

  return {
    approval_id: approval.approval_id,
    status: approval.status
  };
}

async function executeApprovedWorkflowChange(options: {
  approvalId: string;
  expectedActionName: string;
  fallbackChangeSummary: string;
}): Promise<ToolResult> {
  const approval = await veeControlAdapter.getApproval(options.approvalId);

  if (approval.status === "pending") {
    return buildApprovalPendingResult(options.approvalId);
  }
  if (approval.status === "rejected") {
    return buildApprovalRejectedResult(options.approvalId);
  }
  if (approval.status === "executed") {
    return buildApprovalExecutedResult(options.approvalId);
  }
  if (approval.status !== "approved") {
    throw new Error(`Approval ${options.approvalId} has unsupported status '${approval.status}'.`);
  }

  const approvalRecord = approval as unknown as Record<string, unknown>;
  const actionName =
    typeof approvalRecord.action_name === "string" ? approvalRecord.action_name : undefined;

  if (actionName && actionName !== options.expectedActionName) {
    throw new Error(
      `Approval ${options.approvalId} action '${actionName}' does not match '${options.expectedActionName}'.`
    );
  }

  const requestPayload = parseWorkflowApprovalPayload(options.approvalId, approval.request_payload);

  assertN8nWriteAllowed();
  const updated = await n8nAdapter.updateWorkflow(requestPayload.workflow_id, requestPayload.workflow);

  await veeControlAdapter.markApprovalExecuted(options.approvalId, {
    n8n_updated_at: String(updated.updatedAt ?? ""),
    n8n_name: String(updated.name ?? ""),
    diff_summary: requestPayload.diff_summary ?? null
  });

  const payload: Record<string, unknown> = {
    ok: true,
    approval_id: options.approvalId,
    status: "executed",
    workflowId: requestPayload.workflow_id,
    updatedAt: String(updated.updatedAt ?? ""),
    name: String(updated.name ?? ""),
    changeSummary: requestPayload.change_summary ?? options.fallbackChangeSummary
  };

  if (requestPayload.diff_summary) {
    payload.diff_summary = requestPayload.diff_summary;
  }

  return {
    content: [{ type: "text", text: JSON.stringify(payload, null, 2) }],
    structuredContent: payload
  };
}

// ─── Reusable Zod schemas for structured DB tools (v0.3) ─────────────────────

const whereConditionSchema = z.object({
  column: z.string().min(1).describe("Column name (bare or table-qualified like projects.id)"),
  operator: z
    .enum(["=", "!=", ">", ">=", "<", "<=", "LIKE", "NOT LIKE", "IN", "NOT IN", "IS NULL", "IS NOT NULL"])
    .describe("Comparison operator"),
  value: z
    .union([z.string(), z.number(), z.boolean(), z.null(), z.array(z.union([z.string(), z.number()]))])
    .optional()
    .describe("Bound value (omit for IS NULL / IS NOT NULL; array for IN / NOT IN)")
});

const joinClauseSchema = z.object({
  type: z.enum(["INNER", "LEFT", "RIGHT"]).optional().describe("Join type (default INNER)"),
  table: z.string().min(1).describe("Table name to join (must be in the allowed table list)"),
  on: z
    .string()
    .min(5)
    .describe("ON clause — only format tableA.colA = tableB.colB accepted")
});

const orderBySchema = z.object({
  column: z.string().min(1).describe("Column to order by"),
  direction: z.enum(["ASC", "DESC"]).optional().describe("Sort direction (default ASC)")
});

function createServer(): McpServer {
  const server = new McpServer(
    { name: APP_NAME, version: APP_VERSION },
    { capabilities: { logging: {} } }
  );

  server.registerTool(
    "vee_health",
    {
      title: "Vee Health",
      description: "Returns Vee MCP server health information."
    },
    async () =>
      runAuditedTool({
        toolName: "vee_health",
        permission: "read_only",
        isWrite: false,
        args: {},
        errorContext: "Failed to read Vee health",
        handler: async () => {
          const payload = getHealthPayload();
          return {
            content: [{ type: "text", text: JSON.stringify(payload, null, 2) }],
            structuredContent: payload
          };
        }
      })
  );

  server.registerTool(
    "vee_list_capabilities",
    {
      title: "Vee Capabilities",
      description: "Lists enabled and planned Vee MCP tools."
    },
    async () =>
      runAuditedTool({
        toolName: "vee_list_capabilities",
        permission: "read_only",
        isWrite: false,
        args: {},
        errorContext: "Failed to list Vee capabilities",
        handler: async () => {
          const payload = getCapabilitiesPayload();
          return {
            content: [{ type: "text", text: JSON.stringify(payload, null, 2) }],
            structuredContent: payload
          };
        }
      })
  );

  server.registerTool(
    "vee_n8n_list_workflows",
    {
      title: "Vee N8N List Workflows",
      description: "Lists all n8n workflows via REST API (includes sub-workflows)."
    },
    async () =>
      runAuditedTool({
        toolName: "vee_n8n_list_workflows",
        permission: "read_only",
        isWrite: false,
        args: {},
        errorContext: "Failed to list n8n workflows",
        handler: async () => {
          const workflows = await n8nAdapter.listWorkflows();
          const payload = {
            total: workflows.length,
            workflows
          };

          return {
            content: [{ type: "text", text: JSON.stringify(payload, null, 2) }],
            structuredContent: payload
          };
        }
      })
  );

  server.registerTool(
    "vee_n8n_get_workflow",
    {
      title: "Vee N8N Get Workflow",
      description: "Gets one n8n workflow by ID via REST API.",
      inputSchema: {
        workflowId: z.string().min(1).describe("n8n workflow ID")
      }
    },
    async ({ workflowId }) =>
      runAuditedTool({
        toolName: "vee_n8n_get_workflow",
        permission: "read_only",
        isWrite: false,
        args: { workflowId },
        errorContext: `Failed to get n8n workflow ${workflowId}`,
        handler: async () => {
          const workflow = await n8nAdapter.getWorkflow(workflowId);
          const payload = { workflow };

          return {
            content: [{ type: "text", text: JSON.stringify(payload, null, 2) }],
            structuredContent: payload
          };
        }
      })
  );

  server.registerTool(
    "vee_n8n_preview_workflow_diff",
    {
      title: "Vee N8N Preview Workflow Diff",
      description:
        "Previews workflow changes before approval, using either a full workflow payload or patch operations.",
      inputSchema: {
        workflowId: z.string().min(1).describe("n8n workflow ID"),
        workflow: z
          .record(z.string(), z.unknown())
          .optional()
          .describe("Candidate full workflow JSON"),
        operations: z
          .array(z.record(z.string(), z.unknown()))
          .min(1)
          .optional()
          .describe("Patch operations over current workflow")
      }
    },
    async ({ workflowId, workflow, operations }) =>
      runAuditedTool({
        toolName: "vee_n8n_preview_workflow_diff",
        permission: "read_only",
        isWrite: false,
        args: { workflowId, workflow, operations },
        errorContext: "Failed to preview workflow diff",
        handler: async () => {
          if (!workflow && !operations) {
            throw new Error("Provide either workflow or operations.");
          }
          if (workflow && operations) {
            throw new Error("Provide only one of workflow or operations.");
          }

          const currentWorkflow = ensureWorkflowRecord(await n8nAdapter.getWorkflow(workflowId), "workflow");

          let targetWorkflow: WorkflowRecord;
          let appliedOperations: unknown[] | undefined;

          if (workflow) {
            targetWorkflow = ensureWorkflowRecord(workflow, "workflow");
          } else {
            const parsedOperations = parseWorkflowOperations(operations);
            const patchResult = applyWorkflowOperations(currentWorkflow, parsedOperations);
            targetWorkflow = patchResult.workflow;
            appliedOperations = patchResult.applied;
          }

          const diffSummary = summarizeWorkflowDiff(currentWorkflow, targetWorkflow);

          const payload: Record<string, unknown> = {
            ok: true,
            workflowId,
            diff_summary: diffSummary
          };
          if (appliedOperations) {
            payload.applied_operations = appliedOperations;
          }

          return {
            content: [{ type: "text", text: JSON.stringify(payload, null, 2) }],
            structuredContent: payload
          };
        }
      })
  );

  server.registerTool(
    "vee_n8n_list_recent_executions",
    {
      title: "Vee N8N List Recent Executions",
      description: "Lists recent n8n executions, optionally filtered by workflow and status.",
      inputSchema: {
        workflowId: z.string().optional().describe("Optional n8n workflow ID"),
        status: z.string().optional().describe("Optional status filter (e.g. error, success)"),
        limit: z.number().int().min(1).max(100).optional().describe("Max executions to return")
      }
    },
    async ({ workflowId, status, limit }) =>
      runAuditedTool({
        toolName: "vee_n8n_list_recent_executions",
        permission: "read_only",
        isWrite: false,
        args: { workflowId, status, limit },
        errorContext: "Failed to list recent n8n executions",
        handler: async () => {
          const executions = await n8nAdapter.listExecutions({
            workflowId,
            status,
            limit
          });
          const payload = {
            total: executions.length,
            executions
          };

          return {
            content: [{ type: "text", text: JSON.stringify(payload, null, 2) }],
            structuredContent: payload
          };
        }
      })
  );

  server.registerTool(
    "vee_n8n_get_execution",
    {
      title: "Vee N8N Get Execution",
      description: "Gets execution details by ID.",
      inputSchema: {
        executionId: z.string().min(1).describe("n8n execution ID"),
        includeData: z
          .boolean()
          .optional()
          .describe("Include full execution data (can be large)")
      }
    },
    async ({ executionId, includeData }) =>
      runAuditedTool({
        toolName: "vee_n8n_get_execution",
        permission: "read_only",
        isWrite: false,
        args: { executionId, includeData },
        errorContext: `Failed to get execution ${executionId}`,
        handler: async () => {
          const execution = await n8nAdapter.getExecution(executionId, includeData ?? false);
          const payload = { execution };

          return {
            content: [{ type: "text", text: JSON.stringify(payload, null, 2) }],
            structuredContent: payload
          };
        }
      })
  );

  server.registerTool(
    "vee_n8n_retry_execution",
    {
      title: "Vee N8N Retry Execution",
      description: "Retries a failed/canceled execution.",
      inputSchema: {
        executionId: z.string().min(1).describe("n8n execution ID"),
        loadWorkflow: z
          .boolean()
          .optional()
          .describe("True to retry with latest workflow version")
      }
    },
    async ({ executionId, loadWorkflow }) =>
      runAuditedTool({
        toolName: "vee_n8n_retry_execution",
        permission: "safe_execute",
        isWrite: true,
        args: { executionId, loadWorkflow },
        errorContext: `Failed to retry execution ${executionId}`,
        handler: async () => {
          assertN8nWriteAllowed();
          const execution = await n8nAdapter.retryExecution(executionId, loadWorkflow ?? false);
          const payload = {
            ok: true,
            execution
          };

          return {
            content: [{ type: "text", text: JSON.stringify(payload, null, 2) }],
            structuredContent: payload
          };
        }
      })
  );

  server.registerTool(
    "vee_n8n_stop_execution",
    {
      title: "Vee N8N Stop Execution",
      description:
        "Stops a running execution. Optionally allows delete fallback for n8n versions without stop endpoint.",
      inputSchema: {
        executionId: z.string().min(1).describe("n8n execution ID"),
        allowDeleteFallback: z
          .boolean()
          .optional()
          .describe("If true, tries DELETE /executions/{id} when stop endpoint is unavailable")
      }
    },
    async ({ executionId, allowDeleteFallback }) =>
      runAuditedTool({
        toolName: "vee_n8n_stop_execution",
        permission: "safe_execute",
        isWrite: true,
        args: { executionId, allowDeleteFallback },
        errorContext: `Failed to stop execution ${executionId}`,
        handler: async () => {
          assertN8nWriteAllowed();
          const execution = await n8nAdapter.stopExecution(executionId, {
            allowDeleteFallback: allowDeleteFallback ?? false
          });
          const payload = {
            ok: true,
            execution
          };

          return {
            content: [{ type: "text", text: JSON.stringify(payload, null, 2) }],
            structuredContent: payload
          };
        }
      })
  );

  server.registerTool(
    "vee_n8n_update_workflow",
    {
      title: "Vee N8N Update Workflow",
      description: "Workflow update with persistent approval flow (request -> approve -> execute).",
      inputSchema: {
        workflowId: z.string().min(1).optional().describe("n8n workflow ID for approval request"),
        workflow: z
          .record(z.string(), z.unknown())
          .optional()
          .describe("Full n8n workflow JSON payload for approval request"),
        changeSummary: z.string().min(5).describe("Short summary of the change"),
        approvalId: z.string().optional().describe("Existing approval ID to execute"),
        autoRequestApproval: z
          .boolean()
          .optional()
          .describe("When true (default), creates pending approval if approvalId is not provided")
      }
    },
    async ({ workflowId, workflow, changeSummary, approvalId, autoRequestApproval }) =>
      runAuditedTool({
        toolName: "vee_n8n_update_workflow",
        permission: "approval_required",
        isWrite: true,
        args: { workflowId, workflow, changeSummary, approvalId, autoRequestApproval },
        errorContext: "Failed to update n8n workflow",
        handler: async () => {
          if (!approvalId) {
            if (autoRequestApproval === false) {
              throw new Error("approvalId is required when autoRequestApproval=false.");
            }
            if (!workflowId || !workflow) {
              throw new Error("workflowId and workflow are required to create an approval request.");
            }

            const currentWorkflow = ensureWorkflowRecord(
              await n8nAdapter.getWorkflow(workflowId),
              "workflow"
            );
            const candidateWorkflow = ensureWorkflowRecord(workflow, "workflow");
            const diffSummary = summarizeWorkflowDiff(currentWorkflow, candidateWorkflow);

            if (!diffSummary.has_changes) {
              const payload = {
                ok: true,
                workflowId,
                message: "No workflow changes detected.",
                diff_summary: diffSummary
              };

              return {
                content: [{ type: "text", text: JSON.stringify(payload, null, 2) }],
                structuredContent: payload
              };
            }

            const approval = await createWorkflowApprovalRequest({
              actionName: "n8n.update_workflow",
              toolName: "vee_n8n_update_workflow",
              workflowId,
              workflow: candidateWorkflow,
              previousWorkflow: currentWorkflow,
              changeSummary,
              diffSummary
            });

            return buildApprovalCreationResult({
              approvalId: approval.approval_id,
              status: approval.status,
              message:
                "Approval created. Approve it in dashboard/API, then call this tool again with approvalId.",
              diffSummary
            });
          }

          return executeApprovedWorkflowChange({
            approvalId,
            expectedActionName: "n8n.update_workflow",
            fallbackChangeSummary: changeSummary
          });
        }
      })
  );

  server.registerTool(
    "vee_n8n_patch_workflow_nodes",
    {
      title: "Vee N8N Patch Workflow Nodes",
      description:
        "Applies node/connection operations over the current workflow, then runs approval flow before writing.",
      inputSchema: {
        workflowId: z.string().min(1).optional().describe("n8n workflow ID for approval request"),
        operations: z
          .array(z.record(z.string(), z.unknown()))
          .min(1)
          .optional()
          .describe("Node/connection patch operations"),
        changeSummary: z.string().min(5).describe("Short summary of the change"),
        approvalId: z.string().optional().describe("Existing approval ID to execute"),
        autoRequestApproval: z
          .boolean()
          .optional()
          .describe("When true (default), creates pending approval if approvalId is not provided")
      }
    },
    async ({ workflowId, operations, changeSummary, approvalId, autoRequestApproval }) =>
      runAuditedTool({
        toolName: "vee_n8n_patch_workflow_nodes",
        permission: "approval_required",
        isWrite: true,
        args: { workflowId, operations, changeSummary, approvalId, autoRequestApproval },
        errorContext: "Failed to patch n8n workflow nodes",
        handler: async () => {
          if (!approvalId) {
            if (autoRequestApproval === false) {
              throw new Error("approvalId is required when autoRequestApproval=false.");
            }
            if (!workflowId || !operations) {
              throw new Error("workflowId and operations are required to create an approval request.");
            }

            const currentWorkflow = ensureWorkflowRecord(
              await n8nAdapter.getWorkflow(workflowId),
              "workflow"
            );

            const parsedOperations = parseWorkflowOperations(operations);
            const patchResult = applyWorkflowOperations(currentWorkflow, parsedOperations);
            const diffSummary = summarizeWorkflowDiff(currentWorkflow, patchResult.workflow);

            if (!diffSummary.has_changes) {
              const payload = {
                ok: true,
                workflowId,
                message: "No workflow changes detected after operations.",
                diff_summary: diffSummary,
                applied_operations: patchResult.applied
              };

              return {
                content: [{ type: "text", text: JSON.stringify(payload, null, 2) }],
                structuredContent: payload
              };
            }

            const approval = await createWorkflowApprovalRequest({
              actionName: "n8n.patch_workflow_nodes",
              toolName: "vee_n8n_patch_workflow_nodes",
              workflowId,
              workflow: patchResult.workflow,
              previousWorkflow: currentWorkflow,
              changeSummary,
              diffSummary,
              extraPayload: {
                operations: parsedOperations,
                applied_operations: patchResult.applied
              }
            });

            return buildApprovalCreationResult({
              approvalId: approval.approval_id,
              status: approval.status,
              message:
                "Approval created. Approve it in dashboard/API, then call this tool again with approvalId.",
              diffSummary,
              extra: {
                applied_operations: patchResult.applied
              }
            });
          }

          return executeApprovedWorkflowChange({
            approvalId,
            expectedActionName: "n8n.patch_workflow_nodes",
            fallbackChangeSummary: changeSummary
          });
        }
      })
  );

  server.registerTool(
    "vee_n8n_rollback_workflow",
    {
      title: "Vee N8N Rollback Workflow",
      description:
        "Creates and executes rollback approval using stored previous workflow snapshot from a prior approval.",
      inputSchema: {
        sourceApprovalId: z
          .string()
          .optional()
          .describe("Approval ID that contains previous_workflow snapshot"),
        changeSummary: z.string().min(5).optional().describe("Optional rollback summary"),
        approvalId: z.string().optional().describe("Existing rollback approval ID to execute"),
        autoRequestApproval: z
          .boolean()
          .optional()
          .describe("When true (default), creates pending rollback approval if approvalId is not provided")
      }
    },
    async ({ sourceApprovalId, changeSummary, approvalId, autoRequestApproval }) =>
      runAuditedTool({
        toolName: "vee_n8n_rollback_workflow",
        permission: "approval_required",
        isWrite: true,
        args: { sourceApprovalId, changeSummary, approvalId, autoRequestApproval },
        errorContext: "Failed to rollback n8n workflow",
        handler: async () => {
          if (!approvalId) {
            if (autoRequestApproval === false) {
              throw new Error("approvalId is required when autoRequestApproval=false.");
            }
            if (!sourceApprovalId) {
              throw new Error("sourceApprovalId is required to create rollback approval.");
            }

            const sourceApproval = await veeControlAdapter.getApproval(sourceApprovalId);
            const sourcePayload = parseWorkflowApprovalPayload(
              sourceApprovalId,
              sourceApproval.request_payload
            );
            if (!sourcePayload.previous_workflow) {
              throw new Error(
                `Approval ${sourceApprovalId} has no previous_workflow snapshot for rollback.`
              );
            }

            const workflowId = sourcePayload.workflow_id;
            const rollbackWorkflow = sourcePayload.previous_workflow;
            const currentWorkflow = ensureWorkflowRecord(
              await n8nAdapter.getWorkflow(workflowId),
              "workflow"
            );
            const rollbackDiff = summarizeWorkflowDiff(currentWorkflow, rollbackWorkflow);

            if (!rollbackDiff.has_changes) {
              const payload = {
                ok: true,
                workflowId,
                message: "Rollback target is identical to current workflow.",
                diff_summary: rollbackDiff
              };

              return {
                content: [{ type: "text", text: JSON.stringify(payload, null, 2) }],
                structuredContent: payload
              };
            }

            const summary =
              changeSummary ??
              `Rollback workflow ${workflowId} using snapshot from approval ${sourceApprovalId}`;

            const sourceApprovalRecord = sourceApproval as unknown as Record<string, unknown>;
            const sourceActionName =
              typeof sourceApprovalRecord.action_name === "string"
                ? sourceApprovalRecord.action_name
                : null;

            const rollbackApproval = await createWorkflowApprovalRequest({
              actionName: "n8n.rollback_workflow",
              toolName: "vee_n8n_rollback_workflow",
              workflowId,
              workflow: rollbackWorkflow,
              previousWorkflow: currentWorkflow,
              changeSummary: summary,
              diffSummary: rollbackDiff,
              extraPayload: {
                rollback_source_approval_id: sourceApprovalId,
                rollback_source_action: sourceActionName
              }
            });

            return buildApprovalCreationResult({
              approvalId: rollbackApproval.approval_id,
              status: rollbackApproval.status,
              message:
                "Rollback approval created. Approve it in dashboard/API, then call this tool again with approvalId.",
              diffSummary: rollbackDiff,
              extra: {
                rollback_source_approval_id: sourceApprovalId
              }
            });
          }

          return executeApprovedWorkflowChange({
            approvalId,
            expectedActionName: "n8n.rollback_workflow",
            fallbackChangeSummary: changeSummary ?? "Rollback workflow change"
          });
        }
      })
  );

  server.registerTool(
    "vee_obsidian_health",
    {
      title: "Vee Obsidian Health",
      description: "Returns Obsidian adapter configuration and root accessibility."
    },
    async () =>
      runAuditedTool({
        toolName: "vee_obsidian_health",
        permission: "read_only",
        isWrite: false,
        args: {},
        errorContext: "Failed to check Obsidian health",
        handler: async () => {
          const payload = await obsidianAdapter.getHealth();
          return {
            content: [{ type: "text", text: JSON.stringify(payload, null, 2) }],
            structuredContent: payload
          };
        }
      })
  );

  server.registerTool(
    "vee_obsidian_search",
    {
      title: "Vee Obsidian Search",
      description: "Searches markdown notes in the configured Obsidian vault.",
      inputSchema: {
        query: z.string().min(1).describe("Search term"),
        limit: z.number().int().min(1).max(100).optional().describe("Max results"),
        caseSensitive: z.boolean().optional().describe("Case-sensitive search")
      }
    },
    async ({ query, limit, caseSensitive }) =>
      runAuditedTool({
        toolName: "vee_obsidian_search",
        permission: "read_only",
        isWrite: false,
        args: { query, limit, caseSensitive },
        errorContext: "Failed to search Obsidian vault",
        handler: async () => {
          const matches = await obsidianAdapter.searchNotes({
            query,
            limit,
            caseSensitive
          });
          const payload = {
            total: matches.length,
            matches
          };

          return {
            content: [{ type: "text", text: JSON.stringify(payload, null, 2) }],
            structuredContent: payload
          };
        }
      })
  );

  server.registerTool(
    "vee_obsidian_read_note",
    {
      title: "Vee Obsidian Read Note",
      description: "Reads one markdown note from the Obsidian vault.",
      inputSchema: {
        path: z.string().min(1).describe("Relative path inside the vault, ex: Vee/tarefas.md"),
        maxChars: z.number().int().min(500).max(200000).optional().describe("Max chars returned")
      }
    },
    async ({ path, maxChars }) =>
      runAuditedTool({
        toolName: "vee_obsidian_read_note",
        permission: "read_only",
        isWrite: false,
        args: { path, maxChars },
        errorContext: "Failed to read Obsidian note",
        handler: async () => {
          const note = await obsidianAdapter.readNote(path, maxChars);
          const payload = {
            note
          };

          return {
            content: [{ type: "text", text: JSON.stringify(payload, null, 2) }],
            structuredContent: payload
          };
        }
      })
  );

  server.registerTool(
    "vee_obsidian_create_note",
    {
      title: "Vee Obsidian Create Note",
      description:
        "Creates a new markdown note in the Obsidian vault. Fails if the note already exists. Requires OBSIDIAN_WRITE_ENABLED=true.",
      inputSchema: {
        path: z
          .string()
          .min(1)
          .describe("Relative path inside vault, e.g. operacional/minha-nota.md"),
        title: z.string().min(1).describe("Note title"),
        template: z
          .enum(["project", "task", "incident", "decision", "analysis", "generic"])
          .optional()
          .describe("Template type. Default: generic"),
        frontmatter: z
          .record(z.string(), z.unknown())
          .optional()
          .describe("Extra frontmatter fields to merge"),
        content: z.string().optional().describe("Initial body content")
      }
    },
    async ({ path, title, template, frontmatter, content }) =>
      runAuditedTool({
        toolName: "vee_obsidian_create_note",
        permission: "safe_execute",
        isWrite: true,
        args: { path, title, template, frontmatter },
        errorContext: "Failed to create Obsidian note",
        handler: async () => {
          const payload = await obsidianAdapter.createNote({
            path,
            title,
            template,
            frontmatter: frontmatter as Record<string, unknown> | undefined,
            content
          });
          return {
            content: [{ type: "text", text: JSON.stringify(payload, null, 2) }],
            structuredContent: payload
          };
        }
      })
  );

  server.registerTool(
    "vee_obsidian_append_to_note",
    {
      title: "Vee Obsidian Append to Note",
      description:
        "Appends content to an existing markdown note. Optionally targets a specific section heading. Requires OBSIDIAN_WRITE_ENABLED=true.",
      inputSchema: {
        path: z.string().min(1).describe("Relative path inside vault"),
        content: z.string().min(1).describe("Content to append"),
        section: z
          .string()
          .optional()
          .describe("Section heading (without ##) to append after. Omit to append at end of file"),
        prepend: z.boolean().optional().describe("If true, prepend instead of append (ignored when section is set)")
      }
    },
    async ({ path, content, section, prepend }) =>
      runAuditedTool({
        toolName: "vee_obsidian_append_to_note",
        permission: "safe_execute",
        isWrite: true,
        args: { path, section, prepend, content_length: content.length },
        errorContext: "Failed to append to Obsidian note",
        handler: async () => {
          const payload = await obsidianAdapter.appendToNote({ path, content, section, prepend });
          return {
            content: [{ type: "text", text: JSON.stringify(payload, null, 2) }],
            structuredContent: payload
          };
        }
      })
  );

  server.registerTool(
    "vee_obsidian_update_note_section",
    {
      title: "Vee Obsidian Update Note Section",
      description:
        "Replaces the body of a specific ## section in a note. Can create the section if missing. Requires OBSIDIAN_WRITE_ENABLED=true.",
      inputSchema: {
        path: z.string().min(1).describe("Relative path inside vault"),
        section_name: z.string().min(1).describe("Section heading name (without ##)"),
        content: z.string().describe("New content for the section body"),
        create_if_missing: z
          .boolean()
          .optional()
          .describe("If true, creates the section at end of file when not found. Default: false")
      }
    },
    async ({ path, section_name, content, create_if_missing }) =>
      runAuditedTool({
        toolName: "vee_obsidian_update_note_section",
        permission: "safe_execute",
        isWrite: true,
        args: { path, section_name, create_if_missing },
        errorContext: "Failed to update Obsidian note section",
        handler: async () => {
          const payload = await obsidianAdapter.updateNoteSection({
            path,
            section_name,
            content,
            create_if_missing
          });
          return {
            content: [{ type: "text", text: JSON.stringify(payload, null, 2) }],
            structuredContent: payload
          };
        }
      })
  );

  server.registerTool(
    "vee_obsidian_append_to_daily_log",
    {
      title: "Vee Obsidian Append to Daily Log",
      description:
        "Appends a timestamped entry to the daily operational log (Sessoes/YYYY-MM-DD.md). Creates the file if it does not exist. Requires OBSIDIAN_WRITE_ENABLED=true.",
      inputSchema: {
        date: z
          .string()
          .regex(/^\d{4}-\d{2}-\d{2}$/)
          .optional()
          .describe("Target date (YYYY-MM-DD). Defaults to today"),
        category: z
          .string()
          .min(1)
          .describe("Log category, e.g. workflows, bugs, executions, deployments, alerts"),
        content: z.string().min(1).describe("Entry content in markdown")
      }
    },
    async ({ date, category, content }) =>
      runAuditedTool({
        toolName: "vee_obsidian_append_to_daily_log",
        permission: "safe_execute",
        isWrite: true,
        args: { date, category },
        errorContext: "Failed to append to daily log",
        handler: async () => {
          const payload = await obsidianAdapter.appendToDailyLog({ date, category, content });
          return {
            content: [{ type: "text", text: JSON.stringify(payload, null, 2) }],
            structuredContent: payload
          };
        }
      })
  );

  server.registerTool(
    "vee_obsidian_create_task_note",
    {
      title: "Vee Obsidian Create Task Note",
      description:
        "Creates a structured task note in Vee/tarefas/ with standard frontmatter and sections. Requires OBSIDIAN_WRITE_ENABLED=true.",
      inputSchema: {
        project: z
          .string()
          .min(1)
          .describe("Project context, e.g. mmcloud, monicai, mmcriativos, infrastructure"),
        title: z.string().min(1).describe("Task title"),
        status: z
          .enum(["open", "in-progress", "blocked", "done"])
          .optional()
          .describe("Initial status. Default: open"),
        priority: z
          .enum(["low", "medium", "high", "critical"])
          .optional()
          .describe("Priority level. Default: medium"),
        description: z.string().optional().describe("Task description"),
        links: z.array(z.string()).optional().describe("Related notes or links, e.g. [[BUG-001]]"),
        assignee: z.string().optional().describe("Assigned agent or person. Default: team")
      }
    },
    async ({ project, title, status, priority, description, links, assignee }) =>
      runAuditedTool({
        toolName: "vee_obsidian_create_task_note",
        permission: "safe_execute",
        isWrite: true,
        args: { project, title, status, priority, assignee },
        errorContext: "Failed to create task note",
        handler: async () => {
          const payload = await obsidianAdapter.createTaskNote({
            project,
            title,
            status,
            priority,
            description,
            links,
            assignee
          });
          return {
            content: [{ type: "text", text: JSON.stringify(payload, null, 2) }],
            structuredContent: payload
          };
        }
      })
  );

  server.registerTool(
    "vee_server_status",
    {
      title: "Vee Server Status",
      description: "Checks server SSH connectivity and basic host metadata."
    },
    async () =>
      runAuditedTool({
        toolName: "vee_server_status",
        permission: "read_only",
        isWrite: false,
        args: {},
        errorContext: "Failed to get server status",
        handler: async () => {
          const payload = await serverSshAdapter.getStatus();
          return {
            content: [{ type: "text", text: JSON.stringify(payload, null, 2) }],
            structuredContent: payload
          };
        }
      })
  );

  server.registerTool(
    "vee_server_list_containers",
    {
      title: "Vee Server List Containers",
      description: "Lists Docker containers from remote server via SSH.",
      inputSchema: {
        limit: z.number().int().min(1).max(1000).optional().describe("Max containers")
      }
    },
    async ({ limit }) =>
      runAuditedTool({
        toolName: "vee_server_list_containers",
        permission: "read_only",
        isWrite: false,
        args: { limit },
        errorContext: "Failed to list server containers",
        handler: async () => {
          const containers = await serverSshAdapter.listContainers(limit);
          const payload = {
            total: containers.length,
            containers
          };

          return {
            content: [{ type: "text", text: JSON.stringify(payload, null, 2) }],
            structuredContent: payload
          };
        }
      })
  );

  server.registerTool(
    "vee_server_get_container_logs",
    {
      title: "Vee Server Get Container Logs",
      description: "Gets container logs tail via SSH (read-only operation).",
      inputSchema: {
        containerName: z.string().min(1).describe("Docker container name"),
        tailLines: z.number().int().min(1).max(1000).optional().describe("Number of tail lines")
      }
    },
    async ({ containerName, tailLines }) =>
      runAuditedTool({
        toolName: "vee_server_get_container_logs",
        permission: "safe_execute",
        isWrite: false,
        args: { containerName, tailLines },
        errorContext: `Failed to get logs for container ${containerName}`,
        handler: async () => {
          const logs = await serverSshAdapter.getContainerLogs(containerName, tailLines ?? 100);
          const payload = {
            container: containerName,
            logs
          };

          return {
            content: [{ type: "text", text: JSON.stringify(payload, null, 2) }],
            structuredContent: payload
          };
        }
      })
  );

  server.registerTool(
    "vee_server_disk_usage",
    {
      title: "Vee Server Disk Usage",
      description: "Returns disk usage (df -h) from the remote server via SSH."
    },
    async () =>
      runAuditedTool({
        toolName: "vee_server_disk_usage",
        permission: "read_only",
        isWrite: false,
        args: {},
        errorContext: "Failed to get disk usage",
        handler: async () => {
          const entries = await serverSshAdapter.getDiskUsage();
          const payload = { total: entries.length, entries };
          return {
            content: [{ type: "text", text: JSON.stringify(payload, null, 2) }],
            structuredContent: payload
          };
        }
      })
  );

  server.registerTool(
    "vee_server_memory_usage",
    {
      title: "Vee Server Memory Usage",
      description: "Returns RAM usage (free -m) from the remote server via SSH."
    },
    async () =>
      runAuditedTool({
        toolName: "vee_server_memory_usage",
        permission: "read_only",
        isWrite: false,
        args: {},
        errorContext: "Failed to get memory usage",
        handler: async () => {
          const memory = await serverSshAdapter.getMemoryUsage();
          return {
            content: [{ type: "text", text: JSON.stringify(memory, null, 2) }],
            structuredContent: memory
          };
        }
      })
  );

  server.registerTool(
    "vee_server_list_containers_detailed",
    {
      title: "Vee Server List Containers Detailed",
      description: "Lists Docker containers with name, status, image, ports, and short ID via SSH.",
      inputSchema: {
        limit: z.number().int().min(1).max(1000).optional().describe("Max containers")
      }
    },
    async ({ limit }) =>
      runAuditedTool({
        toolName: "vee_server_list_containers_detailed",
        permission: "read_only",
        isWrite: false,
        args: { limit },
        errorContext: "Failed to list containers (detailed)",
        handler: async () => {
          const containers = await serverSshAdapter.listContainersDetailed(limit);
          const payload = { total: containers.length, containers };
          return {
            content: [{ type: "text", text: JSON.stringify(payload, null, 2) }],
            structuredContent: payload
          };
        }
      })
  );

  server.registerTool(
    "vee_server_inspect_container",
    {
      title: "Vee Server Inspect Container",
      description: "Returns configuration and state of a Docker container via docker inspect.",
      inputSchema: {
        containerName: z.string().min(1).describe("Docker container name")
      }
    },
    async ({ containerName }) =>
      runAuditedTool({
        toolName: "vee_server_inspect_container",
        permission: "read_only",
        isWrite: false,
        args: { containerName },
        errorContext: `Failed to inspect container ${containerName}`,
        handler: async () => {
          const payload = await serverSshAdapter.inspectContainer(containerName);
          return {
            content: [{ type: "text", text: JSON.stringify(payload, null, 2) }],
            structuredContent: payload
          };
        }
      })
  );

  server.registerTool(
    "vee_server_restart_container",
    {
      title: "Vee Server Restart Container",
      description:
        "Restarts a Docker container on the remote server. Requires SERVER_SSH_RESTART_ENABLED=true and the container must be in the allowlist.",
      inputSchema: {
        containerName: z.string().min(1).describe("Docker container name to restart")
      }
    },
    async ({ containerName }) =>
      runAuditedTool({
        toolName: "vee_server_restart_container",
        permission: "approval_required",
        isWrite: true,
        args: { containerName },
        errorContext: `Failed to restart container ${containerName}`,
        handler: async () => {
          const payload = await serverSshAdapter.restartContainer(containerName);
          return {
            content: [{ type: "text", text: JSON.stringify(payload, null, 2) }],
            structuredContent: payload
          };
        }
      })
  );

  server.registerTool(
    "vee_server_tail_log",
    {
      title: "Vee Server Tail Log",
      description:
        "Returns the last N lines of a log file on the remote server. Path must be in the allowed log paths list.",
      inputSchema: {
        path: z.string().min(1).describe("Absolute path to the log file on the server"),
        lines: z.number().int().min(1).max(1000).optional().describe("Number of lines to tail. Default: 100")
      }
    },
    async ({ path, lines }) =>
      runAuditedTool({
        toolName: "vee_server_tail_log",
        permission: "read_only",
        isWrite: false,
        args: { path, lines },
        errorContext: `Failed to tail log ${path}`,
        handler: async () => {
          const payload = await serverSshAdapter.tailLog(path, lines);
          return {
            content: [{ type: "text", text: JSON.stringify(payload, null, 2) }],
            structuredContent: payload
          };
        }
      })
  );

  server.registerTool(
    "vee_server_service_status",
    {
      title: "Vee Server Service Status",
      description:
        "Returns systemctl status output for an allowed service on the remote server.",
      inputSchema: {
        serviceName: z.string().min(1).describe("Systemd service name, e.g. nginx, docker, mysql")
      }
    },
    async ({ serviceName }) =>
      runAuditedTool({
        toolName: "vee_server_service_status",
        permission: "read_only",
        isWrite: false,
        args: { serviceName },
        errorContext: `Failed to get service status for ${serviceName}`,
        handler: async () => {
          const payload = await serverSshAdapter.getServiceStatus(serviceName);
          return {
            content: [{ type: "text", text: JSON.stringify(payload, null, 2) }],
            structuredContent: payload
          };
        }
      })
  );

  server.registerTool(
    "vee_server_health_check",
    {
      title: "Vee Server Health Check",
      description:
        "Runs a full server health diagnostic: SSH connectivity, hostname, uptime, disk usage, memory, and container count."
    },
    async () =>
      runAuditedTool({
        toolName: "vee_server_health_check",
        permission: "read_only",
        isWrite: false,
        args: {},
        errorContext: "Failed to run server health check",
        handler: async () => {
          const payload = await serverSshAdapter.getHealthCheck();
          return {
            content: [{ type: "text", text: JSON.stringify(payload, null, 2) }],
            structuredContent: payload
          };
        }
      })
  );

  server.registerTool(
    "vee_server_list_allowed_paths",
    {
      title: "Vee Server List Allowed Paths",
      description: "Returns the configured allowlists for containers, services, and log paths."
    },
    async () =>
      runAuditedTool({
        toolName: "vee_server_list_allowed_paths",
        permission: "read_only",
        isWrite: false,
        args: {},
        errorContext: "Failed to list allowed paths",
        handler: async () => {
          const payload = serverSshAdapter.listAllowedPaths();
          return {
            content: [{ type: "text", text: JSON.stringify(payload, null, 2) }],
            structuredContent: payload
          };
        }
      })
  );

  server.registerTool(
    "vee_claude_connection_info",
    {
      title: "Vee Claude Connection Info",
      description: "Returns MCP endpoint and auth requirements for Claude connector setup."
    },
    async () =>
      runAuditedTool({
        toolName: "vee_claude_connection_info",
        permission: "read_only",
        isWrite: false,
        args: {},
        errorContext: "Failed to get Claude connection info",
        handler: async () => {
          const payload = getClaudeConnectionPayload();
          return {
            content: [{ type: "text", text: JSON.stringify(payload, null, 2) }],
            structuredContent: payload
          };
        }
      })
  );

  server.registerTool(
    "vee_db_get_tenant_by_slug",
    {
      title: "Vee DB Get Tenant by Slug",
      description:
        "Returns a tenant profile from the database by slug. Requires DB_READONLY_* credentials.",
      inputSchema: {
        slug: z.string().min(1).describe("Tenant slug identifier")
      }
    },
    async ({ slug }) =>
      runAuditedTool({
        toolName: "vee_db_get_tenant_by_slug",
        permission: "read_only",
        isWrite: false,
        args: { slug },
        errorContext: `Failed to get tenant by slug: ${slug}`,
        handler: async () => {
          const tenant = await dbReadOnlyAdapter.getTenantBySlug(slug, "vee");
          const payload = { found: tenant !== null, tenant };
          return {
            content: [{ type: "text", text: JSON.stringify(payload, null, 2) }],
            structuredContent: payload
          };
        }
      })
  );

  server.registerTool(
    "vee_db_get_client_by_phone",
    {
      title: "Vee DB Get Client by Phone",
      description:
        "Returns a client/contact profile by phone number. Phone is masked in the response.",
      inputSchema: {
        phone: z.string().min(6).describe("Phone number (digits, spaces, and +/- allowed)")
      }
    },
    async ({ phone }) =>
      runAuditedTool({
        toolName: "vee_db_get_client_by_phone",
        permission: "read_only",
        isWrite: false,
        args: { phone_digits: phone.replace(/\D/g, "").length },
        errorContext: "Failed to get client by phone",
        handler: async () => {
          const client = await dbReadOnlyAdapter.getClientByPhone(phone, "vee");
          const payload = { found: client !== null, client };
          return {
            content: [{ type: "text", text: JSON.stringify(payload, null, 2) }],
            structuredContent: payload
          };
        }
      })
  );

  server.registerTool(
    "vee_db_get_recent_ai_messages",
    {
      title: "Vee DB Get Recent AI Messages",
      description:
        "Returns the most recent AI conversation messages for a session, ordered newest first.",
      inputSchema: {
        session_id: z.string().min(1).describe("Conversation session ID"),
        limit: z.number().int().min(1).max(100).optional().describe("Max messages to return. Default: 20")
      }
    },
    async ({ session_id, limit }) =>
      runAuditedTool({
        toolName: "vee_db_get_recent_ai_messages",
        permission: "read_only",
        isWrite: false,
        args: { session_id, limit },
        errorContext: `Failed to get AI messages for session ${session_id}`,
        handler: async () => {
          const messages = await dbReadOnlyAdapter.getRecentAiMessages(session_id, limit ?? 20, "vee");
          const payload = { total: messages.length, session_id, messages };
          return {
            content: [{ type: "text", text: JSON.stringify(payload, null, 2) }],
            structuredContent: payload
          };
        }
      })
  );

  server.registerTool(
    "vee_db_get_context_state",
    {
      title: "Vee DB Get Context State",
      description:
        "Returns saved agent context state for a session. Optionally filter by state key.",
      inputSchema: {
        session_id: z.string().min(1).describe("Session ID"),
        key: z.string().optional().describe("Optional state key filter")
      }
    },
    async ({ session_id, key }) =>
      runAuditedTool({
        toolName: "vee_db_get_context_state",
        permission: "read_only",
        isWrite: false,
        args: { session_id, key },
        errorContext: `Failed to get context state for session ${session_id}`,
        handler: async () => {
          const states = await dbReadOnlyAdapter.getContextState(session_id, key ?? null, "vee");
          const payload = { total: states.length, session_id, states };
          return {
            content: [{ type: "text", text: JSON.stringify(payload, null, 2) }],
            structuredContent: payload
          };
        }
      })
  );

  server.registerTool(
    "vee_db_get_booking_session",
    {
      title: "Vee DB Get Booking Session",
      description: "Returns details of a booking session by its ID.",
      inputSchema: {
        session_id: z.union([z.string(), z.number()]).describe("Booking session ID (number or string)")
      }
    },
    async ({ session_id }) =>
      runAuditedTool({
        toolName: "vee_db_get_booking_session",
        permission: "read_only",
        isWrite: false,
        args: { session_id },
        errorContext: `Failed to get booking session ${String(session_id)}`,
        handler: async () => {
          const session = await dbReadOnlyAdapter.getBookingSession(session_id, "vee");
          const payload = { found: session !== null, session };
          return {
            content: [{ type: "text", text: JSON.stringify(payload, null, 2) }],
            structuredContent: payload
          };
        }
      })
  );

  server.registerTool(
    "vee_db_get_recent_appointments",
    {
      title: "Vee DB Get Recent Appointments",
      description: "Returns recent or upcoming appointments, optionally filtered by tenant.",
      inputSchema: {
        tenant_id: z.number().int().optional().describe("Optional tenant ID filter"),
        limit: z.number().int().min(1).max(100).optional().describe("Max appointments. Default: 20")
      }
    },
    async ({ tenant_id, limit }) =>
      runAuditedTool({
        toolName: "vee_db_get_recent_appointments",
        permission: "read_only",
        isWrite: false,
        args: { tenant_id, limit },
        errorContext: "Failed to get recent appointments",
        handler: async () => {
          const appointments = await dbReadOnlyAdapter.getRecentAppointments(
            tenant_id ?? null,
            limit ?? 20,
            "vee"
          );
          const payload = { total: appointments.length, appointments };
          return {
            content: [{ type: "text", text: JSON.stringify(payload, null, 2) }],
            structuredContent: payload
          };
        }
      })
  );

  server.registerTool(
    "vee_db_get_project_summary",
    {
      title: "Vee DB Get Project Summary",
      description:
        "Returns a project summary including client, service, and task stats (pending/in_progress/done). Uses mmcriativos projects table.",
      inputSchema: {
        slug_or_id: z
          .string()
          .min(1)
          .describe("Project slug (e.g. loja-urbanfit) or numeric ID")
      }
    },
    async ({ slug_or_id }) =>
      runAuditedTool({
        toolName: "vee_db_get_project_summary",
        permission: "read_only",
        isWrite: false,
        args: { slug_or_id },
        errorContext: `Failed to get project summary for ${slug_or_id}`,
        handler: async () => {
          const project = await dbReadOnlyAdapter.getProjectSummary(slug_or_id, "vee");
          const payload = { found: project !== null, project };
          return {
            content: [{ type: "text", text: JSON.stringify(payload, null, 2) }],
            structuredContent: payload
          };
        }
      })
  );

  server.registerTool(
    "vee_db_get_pending_tasks",
    {
      title: "Vee DB Get Pending Tasks",
      description:
        "Returns pending or in-progress project tasks with project and client context. Uses mmcriativos project_tasks table.",
      inputSchema: {
        project_slug: z.string().optional().describe("Optional project slug filter"),
        status: z
          .enum(["pending", "in_progress", "all"])
          .optional()
          .describe("Task status filter. Default: pending"),
        limit: z.number().int().min(1).max(100).optional().describe("Max tasks. Default: 20")
      }
    },
    async ({ project_slug, status, limit }) =>
      runAuditedTool({
        toolName: "vee_db_get_pending_tasks",
        permission: "read_only",
        isWrite: false,
        args: { project_slug, status, limit },
        errorContext: "Failed to get pending tasks",
        handler: async () => {
          const tasks = await dbReadOnlyAdapter.getPendingTasks(
            { projectSlug: project_slug, status, limit },
            "vee"
          );
          const payload = { total: tasks.length, tasks };
          return {
            content: [{ type: "text", text: JSON.stringify(payload, null, 2) }],
            structuredContent: payload
          };
        }
      })
  );

  server.registerTool(
    "vee_db_get_user_preferences",
    {
      title: "Vee DB Get User Preferences",
      description: "Returns saved preferences for a tenant by tenant ID, optionally filtered by key.",
      inputSchema: {
        tenant_id: z.number().int().min(1).describe("Tenant ID"),
        key_filter: z.string().optional().describe("Optional key filter (partial match)")
      }
    },
    async ({ tenant_id, key_filter }) =>
      runAuditedTool({
        toolName: "vee_db_get_user_preferences",
        permission: "read_only",
        isWrite: false,
        args: { tenant_id, key_filter },
        errorContext: `Failed to get preferences for tenant ${tenant_id}`,
        handler: async () => {
          const preferences = await dbReadOnlyAdapter.getUserPreferences(
            tenant_id,
            key_filter ?? null,
            "vee"
          );
          const payload = { total: preferences.length, tenant_id, preferences };
          return {
            content: [{ type: "text", text: JSON.stringify(payload, null, 2) }],
            structuredContent: payload
          };
        }
      })
  );

  server.registerTool(
    "vee_db_get_agent_execution_history",
    {
      title: "Vee DB Get Agent Execution History",
      description:
        "Returns Vee MCP call execution history from vee_mcp_calls table. Filterable by tool name, status, or write operations.",
      inputSchema: {
        tool_name: z.string().optional().describe("Optional tool name filter (partial match)"),
        status: z
          .enum(["success", "error"])
          .optional()
          .describe("Optional status filter"),
        only_writes: z.boolean().optional().describe("If true, return only write operations"),
        limit: z.number().int().min(1).max(100).optional().describe("Max entries. Default: 20")
      }
    },
    async ({ tool_name, status, only_writes, limit }) =>
      runAuditedTool({
        toolName: "vee_db_get_agent_execution_history",
        permission: "read_only",
        isWrite: false,
        args: { tool_name, status, only_writes, limit },
        errorContext: "Failed to get agent execution history",
        handler: async () => {
          const entries = await dbReadOnlyAdapter.getAgentExecutionHistory(
            { toolName: tool_name, status, onlyWrites: only_writes, limit },
            "vee"
          );
          const payload = { total: entries.length, entries };
          return {
            content: [{ type: "text", text: JSON.stringify(payload, null, 2) }],
            structuredContent: payload
          };
        }
      })
  );

  server.registerTool(
    "vee_db_update_project_status",
    {
      title: "Vee DB Update Project Status",
      description:
        "Updates the status of a project (active | paused | done | cancelled). Setting done also sets finished_at. Requires DB_WRITE_ENABLED=true.",
      inputSchema: {
        slug_or_id: z.string().min(1).describe("Project slug or numeric ID"),
        status: z
          .enum(["active", "paused", "done", "cancelled"])
          .describe("New project status"),
        notes: z.string().optional().describe("Optional notes about the status change")
      }
    },
    async ({ slug_or_id, status, notes }) =>
      runAuditedTool({
        toolName: "vee_db_update_project_status",
        permission: "safe_execute",
        isWrite: true,
        args: { slug_or_id, status },
        errorContext: `Failed to update project status for ${slug_or_id}`,
        handler: async () => {
          const result = await dbWriteAdapter.updateProjectStatus(slug_or_id, status, notes ?? null);
          return {
            content: [{ type: "text", text: JSON.stringify(result, null, 2) }],
            structuredContent: result
          };
        }
      })
  );

  server.registerTool(
    "vee_db_create_internal_task",
    {
      title: "Vee DB Create Internal Task",
      description:
        "Creates a new pending task linked to a project. Requires DB_WRITE_ENABLED=true.",
      inputSchema: {
        project_slug_or_id: z.string().min(1).describe("Project slug or numeric ID"),
        title: z.string().min(1).describe("Task title"),
        description: z.string().optional().describe("Task description"),
        priority: z
          .enum(["low", "medium", "high", "critical"])
          .optional()
          .describe("Task priority (stored in progress_notes)"),
        assigned_to: z.number().int().optional().describe("Optional user ID to assign")
      }
    },
    async ({ project_slug_or_id, title, description, priority, assigned_to }) =>
      runAuditedTool({
        toolName: "vee_db_create_internal_task",
        permission: "safe_execute",
        isWrite: true,
        args: { project_slug_or_id, title, priority },
        errorContext: "Failed to create internal task",
        handler: async () => {
          const result = await dbWriteAdapter.createInternalTask({
            projectSlugOrId: project_slug_or_id,
            title,
            description,
            priority,
            assignedTo: assigned_to ?? null
          });
          return {
            content: [{ type: "text", text: JSON.stringify(result, null, 2) }],
            structuredContent: result
          };
        }
      })
  );

  server.registerTool(
    "vee_db_save_execution_note",
    {
      title: "Vee DB Save Execution Note",
      description:
        "Saves a Vee execution note (diagnosis, fix attempt, summary, or analysis) to vee_execution_notes. Requires DB_WRITE_ENABLED=true.",
      inputSchema: {
        note_type: z
          .enum(["diagnosis", "fix_attempt", "summary", "analysis"])
          .describe("Type of execution note"),
        title: z.string().min(1).describe("Short note title"),
        content: z.string().min(1).describe("Full note content in markdown"),
        tool_name: z.string().optional().describe("Related MCP tool name"),
        session_id: z.string().optional().describe("Related session ID"),
        project_slug_or_id: z.string().optional().describe("Optional project to link")
      }
    },
    async ({ note_type, title, content, tool_name, session_id, project_slug_or_id }) =>
      runAuditedTool({
        toolName: "vee_db_save_execution_note",
        permission: "safe_execute",
        isWrite: true,
        args: { note_type, title, tool_name, session_id },
        errorContext: "Failed to save execution note",
        handler: async () => {
          const result = await dbWriteAdapter.saveExecutionNote({
            noteType: note_type,
            title,
            content,
            toolName: tool_name,
            sessionId: session_id,
            projectSlugOrId: project_slug_or_id
          });
          return {
            content: [{ type: "text", text: JSON.stringify(result, null, 2) }],
            structuredContent: result
          };
        }
      })
  );

  server.registerTool(
    "vee_db_update_context_state",
    {
      title: "Vee DB Update Context State",
      description:
        "Upserts a key/value context state entry for an agent session. Creates the row if missing, updates if exists. Requires DB_WRITE_ENABLED=true.",
      inputSchema: {
        session_id: z.string().min(1).describe("Session ID"),
        key: z.string().min(1).describe("State key"),
        state: z.unknown().describe("State value (any JSON-serializable value)"),
        tenant_id: z.number().int().optional().describe("Optional tenant ID")
      }
    },
    async ({ session_id, key, state, tenant_id }) =>
      runAuditedTool({
        toolName: "vee_db_update_context_state",
        permission: "safe_execute",
        isWrite: true,
        args: { session_id, key },
        errorContext: `Failed to update context state for session ${session_id}`,
        handler: async () => {
          const result = await dbWriteAdapter.updateContextState({
            sessionId: session_id,
            key,
            state,
            tenantId: tenant_id ?? null
          });
          return {
            content: [{ type: "text", text: JSON.stringify(result, null, 2) }],
            structuredContent: result
          };
        }
      })
  );

  server.registerTool(
    "vee_db_register_incident",
    {
      title: "Vee DB Register Incident",
      description:
        "Registers a new operational incident in vee_incidents with severity and affected service. Requires DB_WRITE_ENABLED=true.",
      inputSchema: {
        title: z.string().min(1).describe("Short incident title"),
        severity: z.enum(["low", "medium", "high", "critical"]).describe("Incident severity"),
        description: z.string().min(1).describe("Detailed incident description"),
        affected_service: z
          .string()
          .optional()
          .describe("Affected service name, e.g. n8n, mmcc, evolution-api")
      }
    },
    async ({ title, severity, description, affected_service }) =>
      runAuditedTool({
        toolName: "vee_db_register_incident",
        permission: "safe_execute",
        isWrite: true,
        args: { title, severity, affected_service },
        errorContext: "Failed to register incident",
        handler: async () => {
          const result = await dbWriteAdapter.registerIncident({
            title,
            severity,
            description,
            affectedService: affected_service
          });
          return {
            content: [{ type: "text", text: JSON.stringify(result, null, 2) }],
            structuredContent: result
          };
        }
      })
  );

  server.registerTool(
    "vee_db_attach_task_to_project",
    {
      title: "Vee DB Attach Task to Project",
      description:
        "Moves a project task to a different project. Requires DB_WRITE_ENABLED=true.",
      inputSchema: {
        task_id: z.number().int().min(1).describe("Task ID"),
        project_slug_or_id: z.string().min(1).describe("Target project slug or numeric ID")
      }
    },
    async ({ task_id, project_slug_or_id }) =>
      runAuditedTool({
        toolName: "vee_db_attach_task_to_project",
        permission: "safe_execute",
        isWrite: true,
        args: { task_id, project_slug_or_id },
        errorContext: `Failed to attach task ${task_id} to project ${project_slug_or_id}`,
        handler: async () => {
          const result = await dbWriteAdapter.attachTaskToProject(task_id, project_slug_or_id);
          return {
            content: [{ type: "text", text: JSON.stringify(result, null, 2) }],
            structuredContent: result
          };
        }
      })
  );

  server.registerTool(
    "vee_db_mark_task_as_blocked",
    {
      title: "Vee DB Mark Task as Blocked",
      description:
        "Marks a task as blocked (status → in_progress) and appends a timestamped block reason to progress_notes. Requires DB_WRITE_ENABLED=true.",
      inputSchema: {
        task_id: z.number().int().min(1).describe("Task ID"),
        reason: z.string().min(1).describe("Why the task is blocked")
      }
    },
    async ({ task_id, reason }) =>
      runAuditedTool({
        toolName: "vee_db_mark_task_as_blocked",
        permission: "safe_execute",
        isWrite: true,
        args: { task_id },
        errorContext: `Failed to mark task ${task_id} as blocked`,
        handler: async () => {
          const result = await dbWriteAdapter.markTaskAsBlocked(task_id, reason);
          return {
            content: [{ type: "text", text: JSON.stringify(result, null, 2) }],
            structuredContent: result
          };
        }
      })
  );

  server.registerTool(
    "vee_db_mark_task_as_done",
    {
      title: "Vee DB Mark Task as Done",
      description:
        "Marks a task as done and sets completed_at. Optionally appends a completion note. Requires DB_WRITE_ENABLED=true.",
      inputSchema: {
        task_id: z.number().int().min(1).describe("Task ID"),
        notes: z.string().optional().describe("Optional completion notes")
      }
    },
    async ({ task_id, notes }) =>
      runAuditedTool({
        toolName: "vee_db_mark_task_as_done",
        permission: "safe_execute",
        isWrite: true,
        args: { task_id },
        errorContext: `Failed to mark task ${task_id} as done`,
        handler: async () => {
          const result = await dbWriteAdapter.markTaskAsDone(task_id, notes ?? null);
          return {
            content: [{ type: "text", text: JSON.stringify(result, null, 2) }],
            structuredContent: result
          };
        }
      })
  );

  // ─── Filesystem tools ─────────────────────────────────────────────────────

  server.registerTool(
    "vee_fs_list_allowed_paths",
    {
      title: "FS — List Allowed Paths",
      description:
        "Returns the configured filesystem allowed roots and write paths. Use this to know which directories are accessible before calling other vee_fs_* tools.",
      inputSchema: {}
    },
    async () =>
      runAuditedTool({
        toolName: "vee_fs_list_allowed_paths",
        permission: "read_only",
        isWrite: false,
        args: {},
        errorContext: "Failed to list FS allowed paths",
        handler: async () => {
          const result = serverSshAdapter.listAllowedFsPaths();
          return {
            content: [{ type: "text", text: JSON.stringify(result, null, 2) }],
            structuredContent: result
          };
        }
      })
  );

  server.registerTool(
    "vee_fs_list_directory",
    {
      title: "FS — List Directory",
      description:
        "Lists the contents of a directory on the remote server via SSH. Returns each entry's name, type (file/dir/link), size in bytes, and last modified time. Path must be absolute and under a configured FS_ALLOWED_ROOTS prefix.",
      inputSchema: {
        path: z
          .string()
          .min(1)
          .describe("Absolute path to the directory to list (e.g. /opt/mmcriativos)"),
        max_entries: z
          .number()
          .int()
          .min(1)
          .max(1000)
          .optional()
          .describe("Max number of entries to return (default 200, max 1000)")
      }
    },
    async ({ path, max_entries }) =>
      runAuditedTool({
        toolName: "vee_fs_list_directory",
        permission: "read_only",
        isWrite: false,
        args: { path, max_entries },
        errorContext: `Failed to list directory ${path}`,
        handler: async () => {
          const result = await serverSshAdapter.listDirectory(path, max_entries);
          return {
            content: [{ type: "text", text: JSON.stringify(result, null, 2) }],
            structuredContent: result
          };
        }
      })
  );

  server.registerTool(
    "vee_fs_read_file",
    {
      title: "FS — Read File",
      description:
        "Reads the contents of a file on the remote server via SSH. Truncates at FS_MAX_FILE_BYTES (default 256 KB). Returns content, total size, and whether it was truncated. Path must be absolute and under a configured FS_ALLOWED_ROOTS prefix.",
      inputSchema: {
        path: z
          .string()
          .min(1)
          .describe("Absolute path to the file to read (e.g. /opt/mmcriativos/.env)"),
        max_bytes: z
          .number()
          .int()
          .min(1)
          .optional()
          .describe("Max bytes to read (default and cap: FS_MAX_FILE_BYTES, usually 262144)")
      }
    },
    async ({ path, max_bytes }) =>
      runAuditedTool({
        toolName: "vee_fs_read_file",
        permission: "read_only",
        isWrite: false,
        args: { path, max_bytes },
        errorContext: `Failed to read file ${path}`,
        handler: async () => {
          const result = await serverSshAdapter.readFile(path, max_bytes);
          return {
            content: [{ type: "text", text: JSON.stringify(result, null, 2) }],
            structuredContent: result
          };
        }
      })
  );

  server.registerTool(
    "vee_fs_search_text",
    {
      title: "FS — Search Text in Files",
      description:
        "Searches for a text pattern in files on the remote server via SSH (grep -rn). Returns matching file paths, line numbers, and matched lines. Path must be absolute and under a configured FS_ALLOWED_ROOTS prefix.",
      inputSchema: {
        pattern: z
          .string()
          .min(1)
          .max(200)
          .describe(
            "Search pattern (grep regex). Avoid shell metacharacters: ` $ ! ; | & < > \\"
          ),
        path: z
          .string()
          .min(1)
          .describe("Absolute path to search in — file or directory (e.g. /opt/mmcriativos)"),
        max_matches: z
          .number()
          .int()
          .min(1)
          .max(500)
          .optional()
          .describe("Max number of matching lines to return (default 100, max 500)"),
        case_insensitive: z
          .boolean()
          .optional()
          .describe("If true, search is case-insensitive (grep -i). Default false."),
        include: z
          .string()
          .optional()
          .describe(
            "Glob pattern to filter files (e.g. *.env, *.yml). Only alphanumeric, dots, asterisks, hyphens allowed."
          )
      }
    },
    async ({ pattern, path, max_matches, case_insensitive, include }) =>
      runAuditedTool({
        toolName: "vee_fs_search_text",
        permission: "read_only",
        isWrite: false,
        args: { pattern, path, max_matches, case_insensitive, include },
        errorContext: `Failed to search "${pattern}" in ${path}`,
        handler: async () => {
          const result = await serverSshAdapter.searchText(pattern, path, {
            maxMatches: max_matches,
            caseInsensitive: case_insensitive,
            include
          });
          return {
            content: [{ type: "text", text: JSON.stringify(result, null, 2) }],
            structuredContent: result
          };
        }
      })
  );

  server.registerTool(
    "vee_fs_write_file",
    {
      title: "FS — Write File",
      description:
        "Writes content to a file on the remote server via SSH. Requires FS_WRITE_ENABLED=true and path must match FS_WRITE_ALLOWED_PATHS (if configured). Content is transferred safely as base64. Creates or overwrites the file.",
      inputSchema: {
        path: z
          .string()
          .min(1)
          .describe("Absolute path to the file to write (e.g. /opt/mmcriativos/config.json)"),
        content: z.string().describe("Text content to write to the file")
      }
    },
    async ({ path, content }) =>
      runAuditedTool({
        toolName: "vee_fs_write_file",
        permission: "safe_execute",
        isWrite: true,
        args: { path, content_length: content.length },
        errorContext: `Failed to write file ${path}`,
        handler: async () => {
          const result = await serverSshAdapter.writeFile(path, content);
          return {
            content: [{ type: "text", text: JSON.stringify(result, null, 2) }],
            structuredContent: result
          };
        }
      })
  );

  // ─── DB generic tools (v0.3) ───────────────────────────────────────────────

  server.registerTool(
    "vee_db_query",
    {
      title: "DB — Structured Query",
      description:
        "Executes a structured SELECT against allowed tables or named views (project_timeline, active_projects, pending_work). " +
        "Supports WHERE filters, JOINs, ORDER BY, and pagination. No raw SQL — all inputs are validated. " +
        "Tables with blocked columns require an explicit 'columns' list. Max 100 rows.",
      inputSchema: {
        table: z
          .string()
          .min(1)
          .describe(
            "Table name (e.g. projects, project_tasks) or named view (project_timeline, active_projects, pending_work)"
          ),
        columns: z
          .array(z.string().min(1))
          .optional()
          .describe("Columns to return. Required for tables with blocked columns (contacts, users)."),
        where: z.array(whereConditionSchema).optional().describe("WHERE conditions (AND-joined)"),
        joins: z.array(joinClauseSchema).optional().describe("JOIN clauses"),
        order_by: z.array(orderBySchema).optional().describe("ORDER BY clauses"),
        limit: z.number().int().min(1).max(100).optional().describe("Max rows (default 20, max 100)"),
        offset: z.number().int().min(0).optional().describe("Row offset for pagination")
      }
    },
    async ({ table, columns, where, joins, order_by, limit, offset }) =>
      runAuditedTool({
        toolName: "vee_db_query",
        permission: "read_only",
        isWrite: false,
        args: { table, columns, where_count: where?.length ?? 0, joins_count: joins?.length ?? 0 },
        errorContext: `Failed to execute structured query on "${table}"`,
        handler: async () => {
          const result = await dbReadOnlyAdapter.executeStructuredQuery(
            {
              table,
              columns,
              where: where as Parameters<typeof dbReadOnlyAdapter.executeStructuredQuery>[0]["where"],
              joins: joins as Parameters<typeof dbReadOnlyAdapter.executeStructuredQuery>[0]["joins"],
              orderBy: order_by as Parameters<typeof dbReadOnlyAdapter.executeStructuredQuery>[0]["orderBy"],
              limit: limit ?? 20,
              offset
            },
            "vee"
          );
          return {
            content: [{ type: "text", text: JSON.stringify(result, null, 2) }],
            structuredContent: result
          };
        }
      })
  );

  server.registerTool(
    "vee_db_write",
    {
      title: "DB — Structured Write",
      description:
        "Executes a structured INSERT / UPDATE / UPSERT against allowlisted tables. " +
        "Safe tables (projects, project_tasks, vee_*, etc.) execute directly. " +
        "Protected tables (tenants, clients, contacts, users) require approval: call without approval_id to create one, then call again with the returned approval_id after it is approved. " +
        "UPDATE without WHERE is rejected. DELETE / DROP / TRUNCATE are never allowed.",
      inputSchema: {
        operation: z.enum(["INSERT", "UPDATE", "UPSERT"]).describe("Write operation type"),
        table: z.string().min(1).describe("Target table name (must be in allowed list)"),
        data: z.record(z.string(), z.unknown()).describe("Column-value pairs to write"),
        where: z
          .array(whereConditionSchema)
          .optional()
          .describe("WHERE conditions (required for UPDATE)"),
        reason: z
          .string()
          .min(10)
          .describe("Justification for this write (min 10 characters)"),
        upsert_key: z
          .array(z.string())
          .optional()
          .describe("Unique-key columns for UPSERT (excluded from ON DUPLICATE KEY UPDATE)"),
        approval_id: z
          .string()
          .optional()
          .describe(
            "For protected tables: provide the approval_id returned from the first call once it is approved"
          )
      }
    },
    async ({ operation, table, data, where, reason, upsert_key, approval_id }) =>
      runAuditedTool({
        toolName: "vee_db_write",
        permission: "safe_execute",
        isWrite: true,
        args: { operation, table, columns: Object.keys(data), reason: reason.slice(0, 120) },
        errorContext: `Failed to execute structured write on "${table}"`,
        handler: async () => {
          const policy = resolveTablePolicy(table);
          if (!policy) {
            throw new Error(
              `Table "${table}" is not in the allowed list. Use vee_db_query to see available tables.`
            );
          }
          if (policy.writeMode === "read_only") {
            throw new Error(`Table "${table}" is read-only and does not allow writes.`);
          }

          // ── Approval gate for protected tables ──────────────────────────
          if (policy.writeMode === "approval_required") {
            if (!approval_id) {
              // First call: create pending approval
              const approval = await veeControlAdapter.createApproval({
                action_name: `db_write:${operation.toLowerCase()}:${table}`,
                tool_name: "vee_db_write",
                summary: `${operation} on ${table}: ${reason}`,
                request_payload: { operation, table, data, where, reason, upsert_key },
                meta: { source: "vee-mcp-server", requested_at: new Date().toISOString() }
              });
              return buildApprovalCreationResult({
                approvalId: approval.approval_id,
                status: approval.status,
                message: `Write to protected table "${table}" requires approval. Approval created — share the approval_id with the approver.`,
                extra: { table, operation, reason }
              });
            }

            // Second call: verify and execute
            const approval = await veeControlAdapter.getApproval(approval_id);
            if (approval.status === "pending") return buildApprovalPendingResult(approval_id);
            if (approval.status === "rejected") return buildApprovalRejectedResult(approval_id);
            if (approval.status === "executed") return buildApprovalExecutedResult(approval_id);
            if (approval.status !== "approved") {
              throw new Error(
                `Approval ${approval_id} has unexpected status "${approval.status}".`
              );
            }

            // Anti-replay: use only the data from the approved payload
            const saved = isRecord(approval.request_payload) ? approval.request_payload : null;
            if (
              !saved ||
              saved["table"] !== table ||
              saved["operation"] !== operation
            ) {
              throw new Error(
                `Approval ${approval_id} does not match current request (table/operation mismatch).`
              );
            }

            const writeResult = await dbWriteAdapter.executeStructuredWrite({
              operation: saved["operation"] as "INSERT" | "UPDATE" | "UPSERT",
              table: saved["table"] as string,
              data: saved["data"] as Record<string, unknown>,
              where: saved["where"] as WhereCondition[] | undefined,
              reason: saved["reason"] as string,
              upsertKey: saved["upsert_key"] as string[] | undefined
            });

            await veeControlAdapter.markApprovalExecuted(approval_id, {
              table,
              operation,
              affected_rows: writeResult.affected_rows
            });

            const payload = { approval_id, ...writeResult, ok: true };
            return {
              content: [{ type: "text", text: JSON.stringify(payload, null, 2) }],
              structuredContent: payload
            };
          }

          // ── safe_execute: run directly ────────────────────────────────────
          const writeResult = await dbWriteAdapter.executeStructuredWrite({
            operation,
            table,
            data,
            where: where as WhereCondition[] | undefined,
            reason,
            upsertKey: upsert_key
          });

          return {
            content: [{ type: "text", text: JSON.stringify(writeResult, null, 2) }],
            structuredContent: writeResult
          };
        }
      })
  );

  server.registerTool(
    "vee_db_record_event",
    {
      title: "DB — Record Project Event",
      description:
        "Appends an event to the vee_project_events log. Use to record any meaningful state change, " +
        "action, or observation about a project, task, appointment, or other entity. " +
        "Forms the core of the traceability timeline.",
      inputSchema: {
        entity_type: z
          .string()
          .min(1)
          .describe("Entity type: project, task, appointment, session, incident, etc."),
        entity_id: z
          .number()
          .int()
          .min(1)
          .describe("ID of the entity"),
        actor: z
          .string()
          .min(1)
          .describe("Who triggered this event (e.g. Vee, system, user name)"),
        action: z
          .string()
          .min(1)
          .describe("Event action name (e.g. status_changed, created, blocked, note_added)"),
        payload: z
          .record(z.string(), z.unknown())
          .optional()
          .describe("Event-specific data (e.g. { from: 'pending', to: 'done' })"),
        context: z
          .record(z.string(), z.unknown())
          .optional()
          .describe("Surrounding context (e.g. { session_id, tool_name })")
      }
    },
    async ({ entity_type, entity_id, actor, action, payload, context }) =>
      runAuditedTool({
        toolName: "vee_db_record_event",
        permission: "safe_execute",
        isWrite: true,
        args: { entity_type, entity_id, actor, action },
        errorContext: "Failed to record project event",
        handler: async () => {
          const result = await dbWriteAdapter.recordProjectEvent({
            entityType: entity_type,
            entityId: entity_id,
            actor,
            action,
            payload: payload ?? null,
            context: context ?? null
          });
          return {
            content: [{ type: "text", text: JSON.stringify(result, null, 2) }],
            structuredContent: result
          };
        }
      })
  );

  server.registerTool(
    "vee_db_record_decision",
    {
      title: "DB — Record Operational Decision",
      description:
        "Records a decision made during operations or analysis into vee_operational_decisions. " +
        "Use to document why something was done, what alternatives were considered, and what the outcome was. " +
        "Forms the institutional memory layer for future context.",
      inputSchema: {
        title: z.string().min(1).describe("Short title for the decision"),
        context: z
          .string()
          .min(1)
          .describe("Situation or problem that prompted this decision"),
        rationale: z
          .string()
          .min(1)
          .describe("Why this decision was made; alternatives considered"),
        outcome: z
          .string()
          .min(1)
          .describe("What was decided or will be done"),
        actor: z
          .string()
          .min(1)
          .describe("Who made this decision (e.g. Vee, user name)"),
        project_id: z
          .number()
          .int()
          .optional()
          .describe("Optional: associate with a project ID")
      }
    },
    async ({ title, context, rationale, outcome, actor, project_id }) =>
      runAuditedTool({
        toolName: "vee_db_record_decision",
        permission: "safe_execute",
        isWrite: true,
        args: { title, actor, project_id },
        errorContext: "Failed to record operational decision",
        handler: async () => {
          const result = await dbWriteAdapter.recordOperationalDecision({
            title,
            context,
            rationale,
            outcome,
            actor,
            projectId: project_id ?? null
          });
          return {
            content: [{ type: "text", text: JSON.stringify(result, null, 2) }],
            structuredContent: result
          };
        }
      })
  );

  server.registerTool(
    "vee_db_get_timeline",
    {
      title: "DB — Get Entity Timeline",
      description:
        "Returns the event timeline from vee_project_events for a given entity or project. " +
        "Shows who did what and when, ordered newest first. " +
        "Use for investigation, context recovery, or audit of any entity.",
      inputSchema: {
        entity_type: z
          .string()
          .optional()
          .describe("Filter by entity type (e.g. project, task, appointment)"),
        entity_id: z.number().int().optional().describe("Filter by entity ID"),
        actor: z.string().optional().describe("Filter by actor name"),
        action: z.string().optional().describe("Filter by action (exact match)"),
        limit: z
          .number()
          .int()
          .min(1)
          .max(100)
          .optional()
          .describe("Max events to return (default 50)")
      }
    },
    async ({ entity_type, entity_id, actor, action, limit }) =>
      runAuditedTool({
        toolName: "vee_db_get_timeline",
        permission: "read_only",
        isWrite: false,
        args: { entity_type, entity_id, actor, action, limit },
        errorContext: "Failed to get entity timeline",
        handler: async () => {
          const where: WhereCondition[] = [];
          if (entity_type) where.push({ column: "entity_type", operator: "=", value: entity_type });
          if (entity_id) where.push({ column: "entity_id", operator: "=", value: entity_id });
          if (actor) where.push({ column: "actor", operator: "=", value: actor });
          if (action) where.push({ column: "action", operator: "=", value: action });

          const result = await dbReadOnlyAdapter.executeStructuredQuery(
            {
              table: "vee_project_events",
              where,
              orderBy: [{ column: "created_at", direction: "DESC" }],
              limit: limit ?? 50
            },
            "vee"
          );
          return {
            content: [{ type: "text", text: JSON.stringify(result, null, 2) }],
            structuredContent: result
          };
        }
      })
  );

  return server;
}

const app = createMcpExpressApp({ host: HOST });

app.get("/health", (_req, res) => {
  res.status(200).json(getHealthPayload());
});

app.post("/mcp", withAuth, async (req, res) => {
  const server = createServer();
  const transport = new StreamableHTTPServerTransport({
    sessionIdGenerator: undefined
  });

  try {
    await server.connect(transport);
    await transport.handleRequest(req, res, req.body);
  } catch (error) {
    if (!res.headersSent) {
      res.status(500).json({
        jsonrpc: "2.0",
        error: { code: -32603, message: "Internal server error" },
        id: null
      });
    }
  } finally {
    res.on("close", () => {
      void transport.close();
      void server.close();
    });
  }
});

app.get("/mcp", withAuth, (_req, res) => {
  res.status(405).json({
    jsonrpc: "2.0",
    error: { code: -32000, message: "Method not allowed." },
    id: null
  });
});

app.delete("/mcp", withAuth, (_req, res) => {
  res.status(405).json({
    jsonrpc: "2.0",
    error: { code: -32000, message: "Method not allowed." },
    id: null
  });
});

app.listen(PORT, HOST, () => {
  const authMode = AUTH_TOKEN ? "Bearer token required" : "No auth token configured";
  const n8nMode = N8N_API_KEY ? "N8N REST configured" : "N8N_API_KEY missing";
  const n8nWriteMode = N8N_WRITE_ENABLED ? "N8N writes enabled" : "N8N writes disabled";
  const auditMode = VEE_AUDIT_URL ? "audit enabled" : "audit disabled";
  const approvalMode =
    VEE_CONTROL_BASE_URL && VEE_CONTROL_TOKEN
      ? "approval control enabled"
      : "approval control disabled";
  const obsidianMode = obsidianAdapter.isConfigured() ? "obsidian enabled" : "obsidian disabled";
  const serverMode = serverSshAdapter.isConfigured() ? "server ssh enabled" : "server ssh disabled";
  const dbMode = dbReadOnlyAdapter.isConfigured() ? "db readonly enabled" : "db readonly disabled";
  const dbWriteMode = dbWriteAdapter.isConfigured() ? "db write enabled" : "db write disabled";
  const claudeMode = CLAUDE_MCP_PUBLIC_URL ? "claude endpoint configured" : "claude endpoint auto";
  const fsMode =
    FS_ALLOWED_ROOTS.length > 0
      ? `fs enabled (roots: ${FS_ALLOWED_ROOTS.join(", ")}${FS_WRITE_ENABLED ? ", writes ON" : ""})`
      : "fs disabled (FS_ALLOWED_ROOTS not set)";

  console.log(`[Vee MCP] listening on http://${HOST}:${PORT}`);
  console.log(`[Vee MCP] /mcp (${authMode})`);
  console.log(`[Vee MCP] n8n mode: ${n8nMode}`);
  console.log(`[Vee MCP] n8n write mode: ${n8nWriteMode}`);
  console.log(`[Vee MCP] ${auditMode}`);
  console.log(`[Vee MCP] ${approvalMode}`);
  console.log(`[Vee MCP] ${obsidianMode}`);
  console.log(`[Vee MCP] ${serverMode}`);
  console.log(`[Vee MCP] ${dbMode}`);
  console.log(`[Vee MCP] ${dbWriteMode}`);
  console.log(`[Vee MCP] ${claudeMode}`);
  console.log(`[Vee MCP] ${fsMode}`);
  console.log(
    "[Vee MCP] enabled tools: vee.health, vee.list_capabilities, vee.n8n.list_workflows, vee.n8n.get_workflow, vee.n8n.preview_workflow_diff, vee.n8n.list_recent_executions, vee.n8n.get_execution, vee.n8n.retry_execution, vee.n8n.stop_execution, vee.n8n.update_workflow, vee.n8n.patch_workflow_nodes, vee.n8n.rollback_workflow, vee.obsidian.health, vee.obsidian.search, vee.obsidian.read_note, vee.obsidian.create_note, vee.obsidian.append_to_note, vee.obsidian.update_note_section, vee.obsidian.append_to_daily_log, vee.obsidian.create_task_note, vee.server.status, vee.server.list_containers, vee.server.get_container_logs, vee.server.disk_usage, vee.server.memory_usage, vee.server.list_containers_detailed, vee.server.inspect_container, vee.server.restart_container, vee.server.tail_log, vee.server.service_status, vee.server.health_check, vee.server.list_allowed_paths, vee.claude.connection_info, vee.db.get_tenant_by_slug, vee.db.get_client_by_phone, vee.db.get_recent_ai_messages, vee.db.get_context_state, vee.db.get_booking_session, vee.db.get_recent_appointments, vee.db.get_project_summary, vee.db.get_pending_tasks, vee.db.get_user_preferences, vee.db.get_agent_execution_history, vee.db.update_project_status, vee.db.create_internal_task, vee.db.save_execution_note, vee.db.update_context_state, vee.db.register_incident, vee.db.attach_task_to_project, vee.db.mark_task_as_blocked, vee.db.mark_task_as_done, vee.fs.list_allowed_paths, vee.fs.list_directory, vee.fs.read_file, vee.fs.search_text, vee.fs.write_file, vee.db.query, vee.db.write, vee.db.record_event, vee.db.record_decision, vee.db.get_timeline"
  );
});
