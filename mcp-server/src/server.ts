import "dotenv/config";

import { randomUUID } from "node:crypto";
import path from "node:path";
import { createMcpExpressApp } from "@modelcontextprotocol/sdk/server/express.js";
import { McpServer } from "@modelcontextprotocol/sdk/server/mcp.js";
import { StreamableHTTPServerTransport } from "@modelcontextprotocol/sdk/server/streamableHttp.js";
import type { NextFunction, Request, Response } from "express";
import { z } from "zod";
import { DatabaseReadOnlyAdapter } from "./adapters/databaseReadOnlyAdapter.js";
import { DatabaseWriteAdapter } from "./adapters/databaseWriteAdapter.js";
import { N8nRestAdapter } from "./adapters/n8nRestAdapter.js";
import { ObsidianAdapter } from "./adapters/obsidianAdapter.js";
import { FilesystemLocalAdapter } from "./adapters/filesystemLocalAdapter.js";
import { ServerSshAdapter } from "./adapters/serverSshAdapter.js";
import { VeeControlAdapter } from "./adapters/veeControlAdapter.js";
import {
  listQueryAllowlist,
  listWritePolicyGroups,
  resolveTablePolicy,
  resolveActualTableName
} from "./adapters/queryAllowlist.js";
import { buildWriteQuery, type WhereCondition } from "./adapters/structuredQueryBuilder.js";
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

const DB_TARGET_VALUES = ["mmcriativos", "mmcc"] as const;
type DbTarget = (typeof DB_TARGET_VALUES)[number];
const FS_ALLOWLIST_ENV_VALUES = ["production", "development", "documentation"] as const;
type FsAllowlistEnv = (typeof FS_ALLOWLIST_ENV_VALUES)[number];

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
const FS_ALLOWLIST_ENV = normalizeFsAllowlistEnv(process.env.FS_ALLOWLIST_ENV) ?? "development";
const FS_ALLOWED_ROOTS = buildFsAllowedRoots(FS_ALLOWLIST_ENV);
const FS_MAX_FILE_BYTES = Number.parseInt(process.env.FS_MAX_FILE_BYTES ?? "262144", 10);
const FS_WRITE_ENABLED = (process.env.FS_WRITE_ENABLED ?? "false").trim().toLowerCase() === "true";
const FS_WRITE_ALLOWED_PATHS = buildFsWriteAllowedPaths(FS_ALLOWLIST_ENV);
const DB_READONLY_HOST = (process.env.DB_READONLY_HOST ?? "").trim();
const DB_READONLY_PORT = Number.parseInt(process.env.DB_READONLY_PORT ?? "3306", 10);
const DB_READONLY_USER = (process.env.DB_READONLY_USER ?? "").trim();
const DB_READONLY_PASSWORD = process.env.DB_READONLY_PASSWORD ?? "";
const DB_READONLY_DATABASE = (process.env.DB_READONLY_DATABASE ?? "").trim();
const DB_READONLY_MAX_ROWS = Number.parseInt(process.env.DB_READONLY_MAX_ROWS ?? "100", 10);
const DB_READONLY_RATE_LIMIT = Number.parseInt(process.env.DB_READONLY_RATE_LIMIT ?? "10", 10);
const DB_READONLY_AUDIT_RATE_LIMIT = Number.parseInt(
  process.env.DB_READONLY_AUDIT_RATE_LIMIT ?? process.env.DB_COVERAGE_RATE_LIMIT ?? String(DB_READONLY_RATE_LIMIT),
  10
);
const DB_WRITE_HOST = (process.env.DB_WRITE_HOST ?? "").trim();
const DB_WRITE_PORT = Number.parseInt(process.env.DB_WRITE_PORT ?? "3306", 10);
const DB_WRITE_USER = (process.env.DB_WRITE_USER ?? "").trim();
const DB_WRITE_PASSWORD = process.env.DB_WRITE_PASSWORD ?? "";
const DB_WRITE_DATABASE = (process.env.DB_WRITE_DATABASE ?? "").trim();
const DB_WRITE_ENABLED = (process.env.DB_WRITE_ENABLED ?? "false").trim().toLowerCase() === "true";
const DB_DEFAULT_TARGET = normalizeDbTarget(process.env.DB_DEFAULT_TARGET) ?? "mmcriativos";
const DB_MMCC_READONLY_HOST = (process.env.DB_MMCC_READONLY_HOST ?? "").trim();
const DB_MMCC_READONLY_PORT = Number.parseInt(process.env.DB_MMCC_READONLY_PORT ?? "3306", 10);
const DB_MMCC_READONLY_USER = (process.env.DB_MMCC_READONLY_USER ?? "").trim();
const DB_MMCC_READONLY_PASSWORD = process.env.DB_MMCC_READONLY_PASSWORD ?? "";
const DB_MMCC_READONLY_DATABASE = (process.env.DB_MMCC_READONLY_DATABASE ?? "").trim();
const DB_MMCC_WRITE_HOST = (process.env.DB_MMCC_WRITE_HOST ?? "").trim();
const DB_MMCC_WRITE_PORT = Number.parseInt(process.env.DB_MMCC_WRITE_PORT ?? "3306", 10);
const DB_MMCC_WRITE_USER = (process.env.DB_MMCC_WRITE_USER ?? "").trim();
const DB_MMCC_WRITE_PASSWORD = process.env.DB_MMCC_WRITE_PASSWORD ?? "";
const DB_MMCC_WRITE_DATABASE = (process.env.DB_MMCC_WRITE_DATABASE ?? "").trim();
const DB_MMCC_WRITE_ENABLED =
  (process.env.DB_MMCC_WRITE_ENABLED ?? "false").trim().toLowerCase() === "true";

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

const filesystemLocalAdapter = new FilesystemLocalAdapter({
  allowedRoots: FS_ALLOWED_ROOTS,
  maxFileBytes: Number.isNaN(FS_MAX_FILE_BYTES) ? 262144 : FS_MAX_FILE_BYTES,
  writeEnabled: FS_WRITE_ENABLED,
  writeAllowedPaths: FS_WRITE_ALLOWED_PATHS,
  allowlistEnvironment: FS_ALLOWLIST_ENV
});

const dbReadOnlyAdapters: Record<DbTarget, DatabaseReadOnlyAdapter> = {
  mmcriativos: new DatabaseReadOnlyAdapter({
    host: DB_READONLY_HOST,
    port: Number.isNaN(DB_READONLY_PORT) ? 3306 : DB_READONLY_PORT,
    user: DB_READONLY_USER,
    password: DB_READONLY_PASSWORD,
    database: DB_READONLY_DATABASE,
    maxRowsPerQuery: Number.isNaN(DB_READONLY_MAX_ROWS)
      ? 100
      : Math.max(1, Math.min(DB_READONLY_MAX_ROWS, 100)),
    rateLimitPerMinute: Number.isNaN(DB_READONLY_RATE_LIMIT) ? 10 : Math.max(1, DB_READONLY_RATE_LIMIT),
    auditRateLimitPerMinute: Number.isNaN(DB_READONLY_AUDIT_RATE_LIMIT)
      ? (Number.isNaN(DB_READONLY_RATE_LIMIT) ? 10 : Math.max(1, DB_READONLY_RATE_LIMIT))
      : Math.max(1, DB_READONLY_AUDIT_RATE_LIMIT)
  }),
  mmcc: new DatabaseReadOnlyAdapter({
    host: DB_MMCC_READONLY_HOST,
    port: Number.isNaN(DB_MMCC_READONLY_PORT) ? 3306 : DB_MMCC_READONLY_PORT,
    user: DB_MMCC_READONLY_USER,
    password: DB_MMCC_READONLY_PASSWORD,
    database: DB_MMCC_READONLY_DATABASE,
    maxRowsPerQuery: Number.isNaN(DB_READONLY_MAX_ROWS)
      ? 100
      : Math.max(1, Math.min(DB_READONLY_MAX_ROWS, 100)),
    rateLimitPerMinute: Number.isNaN(DB_READONLY_RATE_LIMIT) ? 10 : Math.max(1, DB_READONLY_RATE_LIMIT),
    auditRateLimitPerMinute: Number.isNaN(DB_READONLY_AUDIT_RATE_LIMIT)
      ? (Number.isNaN(DB_READONLY_RATE_LIMIT) ? 10 : Math.max(1, DB_READONLY_RATE_LIMIT))
      : Math.max(1, DB_READONLY_AUDIT_RATE_LIMIT)
  })
};

const dbWriteAdapters: Record<DbTarget, DatabaseWriteAdapter> = {
  mmcriativos: new DatabaseWriteAdapter({
    host: DB_WRITE_HOST,
    port: Number.isNaN(DB_WRITE_PORT) ? 3306 : DB_WRITE_PORT,
    user: DB_WRITE_USER,
    password: DB_WRITE_PASSWORD,
    database: DB_WRITE_DATABASE,
    enabled: DB_WRITE_ENABLED
  }),
  mmcc: new DatabaseWriteAdapter({
    host: DB_MMCC_WRITE_HOST,
    port: Number.isNaN(DB_MMCC_WRITE_PORT) ? 3306 : DB_MMCC_WRITE_PORT,
    user: DB_MMCC_WRITE_USER,
    password: DB_MMCC_WRITE_PASSWORD,
    database: DB_MMCC_WRITE_DATABASE,
    enabled: DB_MMCC_WRITE_ENABLED
  })
};

// Backward-compatible defaults for legacy db tools without db_target.
const dbReadOnlyAdapter = dbReadOnlyAdapters[DB_DEFAULT_TARGET];
const dbWriteAdapter = dbWriteAdapters[DB_DEFAULT_TARGET];

const dbTargetSchema = z
  .enum(DB_TARGET_VALUES)
  .optional()
  .describe(`Database target (${DB_TARGET_VALUES.join(" | ")}). Default: ${DB_DEFAULT_TARGET}`);

function normalizeDbTarget(raw: unknown): DbTarget | undefined {
  if (typeof raw !== "string") {
    return undefined;
  }
  const value = raw.trim().toLowerCase();
  return (DB_TARGET_VALUES as readonly string[]).includes(value) ? (value as DbTarget) : undefined;
}

function normalizeFsAllowlistEnv(raw: unknown): FsAllowlistEnv | undefined {
  if (typeof raw !== "string") {
    return undefined;
  }
  const value = raw.trim().toLowerCase();
  return (FS_ALLOWLIST_ENV_VALUES as readonly string[]).includes(value) ? (value as FsAllowlistEnv) : undefined;
}

function parseCsvEnvVar(name: string): string[] {
  return (process.env[name] ?? "")
    .split(",")
    .map(s => s.trim())
    .filter(Boolean);
}

function dedupeStrings(values: string[]): string[] {
  const seen = new Set<string>();
  const unique: string[] = [];
  for (const value of values) {
    const key = value.toLowerCase();
    if (seen.has(key)) {
      continue;
    }
    seen.add(key);
    unique.push(value);
  }
  return unique;
}

const MMCRIATIVOS_SAFE_EXECUTE_BASE = [
  "projects",
  "project_tasks",
  "vee_project_events",
  "vee_status_history",
  "vee_blocks",
  "vee_operational_decisions",
  "vee_execution_notes"
];

const MMCRIATIVOS_APPROVAL_REQUIRED_BASE = [
  "clients",
  "contacts",
  "tenants",
  "users",
  "messages",
  "password_resets",
  "personal_access_tokens",
  "oauth_access_tokens",
  "oauth_refresh_tokens",
  "oauth_clients",
  "api_credentials",
  "integration_credentials",
  "integrations",
  "billing_subscriptions",
  "billing_invoices",
  "billing_transactions",
  "subscriptions",
  "invoices",
  "payment_methods"
];

const MMCC_SAFE_EXECUTE_BASE = [
  "ai_messages",
  "ai_context_states",
  "ai_memory_snapshots",
  "ai_usage_events",
  "agent_executions",
  "booking_sessions",
  "appointments",
  "appointment_notes",
  "appointment_confirmation_reminder_dispatches",
  "customer_appointment_reminder_dispatches",
  "professional_daily_schedule_dispatches"
];

const MMCC_APPROVAL_REQUIRED_BASE = [
  "tenants",
  "customers",
  "users"
];

function buildFsAllowedRoots(fsEnv: FsAllowlistEnv): string[] {
  const envSuffix = fsEnv.toUpperCase();
  return dedupeStrings(
    [
      ...parseCsvEnvVar("FS_ALLOWED_ROOTS"),
      ...parseCsvEnvVar("FS_ALLOWED_ROOTS_COMMON"),
      ...parseCsvEnvVar(`FS_ALLOWED_ROOTS_${envSuffix}`),
      ...parseCsvEnvVar("FS_WORKSPACE_ROOTS"),
      ...parseCsvEnvVar("FS_DEV_REPO_ROOTS"),
      ...parseCsvEnvVar("FS_CONFIG_READ_PATHS")
    ].map(v => v.trim())
  );
}

function buildFsWriteAllowedPaths(fsEnv: FsAllowlistEnv): string[] {
  const envSuffix = fsEnv.toUpperCase();
  return dedupeStrings(
    [
      ...parseCsvEnvVar("FS_WRITE_ALLOWED_PATHS"),
      ...parseCsvEnvVar("FS_WRITE_ALLOWED_PATHS_COMMON"),
      ...parseCsvEnvVar(`FS_WRITE_ALLOWED_PATHS_${envSuffix}`),
      ...parseCsvEnvVar("FS_OPERATION_WRITE_PATHS"),
      ...parseCsvEnvVar("FS_DOCS_WRITE_PATHS")
    ].map(v => v.trim())
  );
}

function getConfiguredReadTargets(): DbTarget[] {
  return (Object.entries(dbReadOnlyAdapters) as Array<[DbTarget, DatabaseReadOnlyAdapter]>)
    .filter(([, adapter]) => adapter.isConfigured())
    .map(([target]) => target);
}

function getConfiguredWriteTargets(): DbTarget[] {
  return (Object.entries(dbWriteAdapters) as Array<[DbTarget, DatabaseWriteAdapter]>)
    .filter(([, adapter]) => adapter.isConfigured())
    .map(([target]) => target);
}

function resolveReadOnlyAdapter(dbTarget?: DbTarget): { target: DbTarget; adapter: DatabaseReadOnlyAdapter } {
  const target = dbTarget ?? DB_DEFAULT_TARGET;
  const adapter = dbReadOnlyAdapters[target];
  if (!adapter || !adapter.isConfigured()) {
    const configured = getConfiguredReadTargets();
    throw new Error(
      configured.length > 0
        ? `DB target "${target}" is not configured for read. Configured read targets: ${configured.join(", ")}`
        : "No DB read target is configured. Set DB_READONLY_* (and optionally DB_MMCC_READONLY_*)."
    );
  }
  return { target, adapter };
}

function resolveWriteAdapter(dbTarget?: DbTarget): { target: DbTarget; adapter: DatabaseWriteAdapter } {
  const target = dbTarget ?? DB_DEFAULT_TARGET;
  const adapter = dbWriteAdapters[target];
  if (!adapter || !adapter.isConfigured()) {
    const configured = getConfiguredWriteTargets();
    throw new Error(
      configured.length > 0
        ? `DB target "${target}" is not configured for write. Configured write targets: ${configured.join(", ")}`
        : "No DB write target is configured. Set DB_WRITE_* and DB_WRITE_ENABLED=true (and optionally DB_MMCC_WRITE_*)."
    );
  }
  return { target, adapter };
}

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
  { name: "vee_db_list_allowlist", phase: "v0.3", permission: "read_only", status: "enabled" },
  { name: "vee_db_inspect_schema_object", phase: "v0.3", permission: "read_only", status: "enabled" },
  { name: "vee_db_list_table_indexes", phase: "v0.3", permission: "read_only", status: "enabled" },
  { name: "vee_db_list_table_relations", phase: "v0.3", permission: "read_only", status: "enabled" },
  { name: "vee_db_list_allowlist_schema", phase: "v0.3", permission: "read_only", status: "enabled" },
  { name: "vee_db_discover_relationships", phase: "v0.3", permission: "read_only", status: "enabled" },
  { name: "vee_db_test_join_assisted", phase: "v0.3", permission: "read_only", status: "enabled" },
  { name: "vee_db_validate_named_views", phase: "v0.3", permission: "read_only", status: "enabled" },
  { name: "vee_db_validate_table_behavior", phase: "v0.3", permission: "read_only", status: "enabled" },
  { name: "vee_db_schedule_coverage_scan", phase: "v0.3", permission: "read_only", status: "enabled" },
  { name: "vee_db_run_coverage_batch", phase: "v0.3", permission: "read_only", status: "enabled" },
  { name: "vee_db_get_coverage_status", phase: "v0.3", permission: "read_only", status: "enabled" },
  { name: "vee_db_generate_coverage_report", phase: "v0.3", permission: "read_only", status: "enabled" },
  { name: "vee_db_save_coverage_report_markdown", phase: "v0.3", permission: "safe_execute", status: "enabled" },
  { name: "vee_db_update_allowlist_inventory_doc", phase: "v0.3", permission: "safe_execute", status: "enabled" },
  { name: "vee_db_append_schema_observation", phase: "v0.3", permission: "safe_execute", status: "enabled" },
  { name: "vee_db_query", phase: "v0.3", permission: "read_only", status: "enabled" },
  { name: "vee_db_write", phase: "v0.3", permission: "safe_execute", status: "enabled" },
  { name: "vee_db_record_event", phase: "v0.3", permission: "safe_execute", status: "enabled" },
  { name: "vee_db_record_decision", phase: "v0.3", permission: "safe_execute", status: "enabled" },
  { name: "vee_db_record_block", phase: "v0.3", permission: "safe_execute", status: "enabled" },
  { name: "vee_db_unblock_entity", phase: "v0.3", permission: "safe_execute", status: "enabled" },
  { name: "vee_db_get_timeline", phase: "v0.3", permission: "read_only", status: "enabled" },
  { name: "vee_db_get_operational_timeline", phase: "v0.3", permission: "read_only", status: "enabled" }
];

function isRecord(value: unknown): value is Record<string, unknown> {
  return !!value && typeof value === "object" && !Array.isArray(value);
}

function normalizeWriteReason(raw: string): string {
  const normalized = raw.replace(/\s+/g, " ").trim();
  if (normalized.length < 15) {
    throw new Error(
      "Write reason is too short. Use at least 15 characters in the format \"context: detailed justification\"."
    );
  }
  if (!/^[a-zA-Z][a-zA-Z0-9_/-]{2,40}:\s.+/.test(normalized)) {
    throw new Error(
      "Write reason must follow \"context: justification\" format (example: \"ops_fix: normalize stale execution rows\")."
    );
  }
  return normalized;
}

function extractWriteDataPayload(input: {
  data?: Record<string, unknown>;
  values?: Record<string, unknown>;
  set?: Record<string, unknown>;
  payload?: Record<string, unknown>;
}): { data: Record<string, unknown>; source: string } {
  if (input.data && Object.keys(input.data).length > 0) {
    return { data: input.data, source: "data" };
  }
  if (input.values && Object.keys(input.values).length > 0) {
    return { data: input.values, source: "values" };
  }
  if (input.set && Object.keys(input.set).length > 0) {
    return { data: input.set, source: "set" };
  }

  const payload = input.payload;
  if (payload) {
    const payloadData = payload["data"];
    const payloadValues = payload["values"];
    const payloadSet = payload["set"];
    if (isRecord(payloadData) && Object.keys(payloadData).length > 0) {
      return { data: payloadData, source: "payload.data" };
    }
    if (isRecord(payloadValues) && Object.keys(payloadValues).length > 0) {
      return { data: payloadValues, source: "payload.values" };
    }
    if (isRecord(payloadSet) && Object.keys(payloadSet).length > 0) {
      return { data: payloadSet, source: "payload.set" };
    }
  }

  throw new Error(
    "Missing write payload. Provide one of: data, values, set, payload.data, payload.values, or payload.set."
  );
}

type OperationalTimelineItem = {
  kind: "event" | "decision" | "block" | "status_change" | "agent_action";
  timestamp: string;
  actor: string;
  action: string;
  entity_type: string;
  entity_id: number | null;
  project_id: number | null;
  trace_id: string | null;
  source_table: string;
  raw: Record<string, unknown>;
};

function normalizeActorName(raw: string | undefined): string {
  const normalized = (raw ?? "").trim();
  return normalized.length > 0 ? normalized : "Vee";
}

function normalizeTraceId(raw: string | undefined): string {
  const normalized = (raw ?? "").trim();
  if (!normalized) {
    return randomUUID();
  }
  if (!/^[a-zA-Z0-9._:-]{8,120}$/.test(normalized)) {
    throw new Error("trace_id format is invalid. Use 8-120 chars: letters, numbers, . _ : -");
  }
  return normalized;
}

function normalizeEntityType(raw: string): string {
  const normalized = raw.trim().toLowerCase();
  if (!/^[a-z_][a-z0-9_:-]{1,80}$/.test(normalized)) {
    throw new Error(`Invalid entity_type "${raw}". Use lowercase identifiers like project, task, session, incident.`);
  }
  return normalized;
}

function coercePositiveInt(value: unknown): number | null {
  const n = Number(value);
  return Number.isInteger(n) && n > 0 ? n : null;
}

function parseJsonLike(value: unknown): Record<string, unknown> | null {
  if (isRecord(value)) {
    return value;
  }
  if (typeof value !== "string") {
    return null;
  }
  const trimmed = value.trim();
  if (!trimmed || (trimmed[0] !== "{" && trimmed[0] !== "[")) {
    return null;
  }
  try {
    const parsed = JSON.parse(trimmed);
    return isRecord(parsed) ? parsed : null;
  } catch {
    return null;
  }
}

function firstDefinedString(...values: unknown[]): string | null {
  for (const value of values) {
    if (typeof value !== "string") {
      continue;
    }
    const normalized = value.trim();
    if (normalized.length > 0) {
      return normalized;
    }
  }
  return null;
}

function firstDefinedNumber(...values: unknown[]): number | null {
  for (const value of values) {
    const n = coercePositiveInt(value);
    if (n !== null) {
      return n;
    }
  }
  return null;
}

function resolveTimelineEntityId(params: {
  where?: WhereCondition[];
  data: Record<string, unknown>;
  before: Record<string, unknown> | null;
  after: Record<string, unknown> | null;
}): number | null {
  const fromWhere = (params.where ?? []).find(
    condition => condition.operator === "=" && typeof condition.column === "string" && condition.column.endsWith("id")
  );

  return firstDefinedNumber(
    params.after?.["id"],
    params.before?.["id"],
    params.data["id"],
    params.after?.["entity_id"],
    params.before?.["entity_id"],
    params.data["entity_id"],
    fromWhere?.value
  );
}

function resolveTimelineProjectId(params: {
  project_id?: number | null;
  data: Record<string, unknown>;
  before: Record<string, unknown> | null;
  after: Record<string, unknown> | null;
}): number | null {
  return firstDefinedNumber(
    params.project_id,
    params.after?.["project_id"],
    params.before?.["project_id"],
    params.data["project_id"]
  );
}

function inferEntityTypeFromTable(table: string): string {
  const normalized = table.trim().toLowerCase();
  if (normalized.endsWith("ies")) {
    return `${normalized.slice(0, -3)}y`;
  }
  if (normalized.endsWith("s")) {
    return normalized.slice(0, -1);
  }
  return normalized;
}

function summarizeTimelineViews(items: OperationalTimelineItem[]): {
  by_actor: Record<string, number>;
  by_entity: Record<string, number>;
  by_project: Record<string, number>;
  by_kind: Record<string, number>;
} {
  const byActor: Record<string, number> = {};
  const byEntity: Record<string, number> = {};
  const byProject: Record<string, number> = {};
  const byKind: Record<string, number> = {};

  for (const item of items) {
    byActor[item.actor] = (byActor[item.actor] ?? 0) + 1;
    const entityKey = `${item.entity_type}:${item.entity_id ?? "n/a"}`;
    byEntity[entityKey] = (byEntity[entityKey] ?? 0) + 1;
    if (item.project_id !== null) {
      const projectKey = String(item.project_id);
      byProject[projectKey] = (byProject[projectKey] ?? 0) + 1;
    }
    byKind[item.kind] = (byKind[item.kind] ?? 0) + 1;
  }

  return { by_actor: byActor, by_entity: byEntity, by_project: byProject, by_kind: byKind };
}

function normalizeTimelineTimestamp(raw: unknown): string | null {
  if (raw instanceof Date) {
    return raw.toISOString();
  }
  if (typeof raw !== "string") {
    return null;
  }
  const value = raw.trim();
  if (!value) {
    return null;
  }
  if (value.includes("T")) {
    return value;
  }
  if (/^\d{4}-\d{2}-\d{2}\s/.test(value)) {
    return value.replace(" ", "T");
  }
  return value;
}

function timestampToMs(value: string | null): number | null {
  if (!value) {
    return null;
  }
  const parsed = Date.parse(value);
  return Number.isNaN(parsed) ? null : parsed;
}

function extractCorrelationString(raw: Record<string, unknown>, key: string): string | null {
  const payload = parseJsonLike(raw["payload"]);
  const context = parseJsonLike(raw["context"]);
  const requestPayload = parseJsonLike(raw["request_payload"]);
  const responsePayload = parseJsonLike(raw["response_payload"]);
  const meta = parseJsonLike(raw["meta"]);

  return firstDefinedString(
    raw[key],
    payload?.[key],
    context?.[key],
    requestPayload?.[key],
    responsePayload?.[key],
    meta?.[key]
  );
}

function extractCorrelationNumber(raw: Record<string, unknown>, key: string): number | null {
  const payload = parseJsonLike(raw["payload"]);
  const context = parseJsonLike(raw["context"]);
  const requestPayload = parseJsonLike(raw["request_payload"]);
  const responsePayload = parseJsonLike(raw["response_payload"]);
  const meta = parseJsonLike(raw["meta"]);

  return firstDefinedNumber(
    raw[key],
    payload?.[key],
    context?.[key],
    requestPayload?.[key],
    responsePayload?.[key],
    meta?.[key]
  );
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

type AllowlistEntry = ReturnType<typeof listQueryAllowlist>[number];
type CoverageStatus = "ok" | "vazia" | "schema_divergente" | "bloqueada" | "erro";

type CoverageEntryResult = {
  logical_name: string;
  resolved_name: string;
  kind: AllowlistEntry["kind"];
  status: CoverageStatus;
  row_count: number | null;
  checked_at: string;
  schema_summary: {
    object_type: "table" | "view";
    columns_total: number;
    missing_allowlist_columns: string[];
  };
  validations: {
    read_probe: "pass" | "fail";
    blocked_column_guard: "pass" | "fail" | "skipped";
    query_controls: "pass" | "fail";
    write_policy: "pass" | "fail" | "skipped";
    error_messages: "pass" | "fail";
  };
  warnings: string[];
  error: string | null;
};

type CoverageJobState = {
  job_id: string;
  db_target: DbTarget;
  created_at: string;
  updated_at: string;
  status: "queued" | "running" | "paused" | "completed" | "erro";
  include_named_views: boolean;
  audit_mode: boolean;
  queue: AllowlistEntry[];
  cursor: number;
  results: Record<string, CoverageEntryResult>;
  last_error: string | null;
  pause_reason: string | null;
};

const coverageJobs = new Map<string, CoverageJobState>();
const COVERAGE_JOB_MAX_AGE_MS = 12 * 60 * 60 * 1000;

function normalizeAllowlistName(value: string): string {
  return value.trim().toLowerCase();
}

function cleanupCoverageJobs(): void {
  const now = Date.now();
  for (const [jobId, job] of coverageJobs.entries()) {
    const updatedAt = Date.parse(job.updated_at);
    if (!Number.isFinite(updatedAt)) {
      continue;
    }
    if (now - updatedAt > COVERAGE_JOB_MAX_AGE_MS) {
      coverageJobs.delete(jobId);
    }
  }
}

function getCoverageQueue(dbTarget: DbTarget, includeNamedViews: boolean, tableFilter?: string[]): AllowlistEntry[] {
  const entries = listQueryAllowlist(dbTarget).filter(entry => includeNamedViews || entry.kind === "table");
  if (!tableFilter || tableFilter.length === 0) {
    return entries;
  }
  const normalizedFilter = new Set(tableFilter.map(name => normalizeAllowlistName(name)));
  return entries.filter(
    entry =>
      normalizedFilter.has(normalizeAllowlistName(entry.logical_name)) ||
      normalizedFilter.has(normalizeAllowlistName(entry.resolved_name))
  );
}

function resolveAllowlistEntryOrThrow(name: string, dbTarget: DbTarget): AllowlistEntry {
  const normalized = normalizeAllowlistName(name);
  const entry = listQueryAllowlist(dbTarget).find(
    item =>
      normalizeAllowlistName(item.logical_name) === normalized ||
      normalizeAllowlistName(item.resolved_name) === normalized
  );
  if (!entry) {
    throw new Error(`Table/view "${name}" is not in the DB allowlist.`);
  }
  return entry;
}

function pickReadableColumns(entry: AllowlistEntry, availableColumns: string[]): string[] {
  const availableSet = new Set(availableColumns.map(column => column.toLowerCase()));
  const blockedSet = new Set(entry.blocked_columns.map(column => column.toLowerCase()));

  const sourceColumns =
    entry.allowed_columns === "*"
      ? availableColumns
      : entry.allowed_columns.filter(column => availableSet.has(column.toLowerCase()));

  const readable = sourceColumns.filter(column => !blockedSet.has(column.toLowerCase()));
  return [...new Set(readable.map(column => column.toLowerCase()))];
}

function pickProbeColumn(columns: string[]): string | null {
  if (columns.length === 0) {
    return null;
  }
  const nonId = columns.find(column => column !== "id");
  return nonId ?? columns[0] ?? null;
}

function pickDummyValue(columnType: string, columnName: string): string | number | boolean | null {
  const normalizedType = columnType.toLowerCase();
  const normalizedName = columnName.toLowerCase();
  if (
    normalizedType.includes("int") ||
    normalizedType.includes("decimal") ||
    normalizedType.includes("float") ||
    normalizedType.includes("double")
  ) {
    return 1;
  }
  if (normalizedType.includes("bool")) {
    return true;
  }
  if (normalizedType.includes("date") || normalizedType.includes("time")) {
    return "2026-01-01 00:00:00";
  }
  if (normalizedName.endsWith("_id") || normalizedName === "id") {
    return 1;
  }
  return "coverage_probe";
}

function classifyCoverageError(message: string): CoverageStatus {
  const normalized = message.toLowerCase();
  if (normalized.includes("blocked")) {
    return "bloqueada";
  }
  if (normalized.includes("not in the allowed") || normalized.includes("not found")) {
    return "schema_divergente";
  }
  return "erro";
}

function parseToolErrorMessage(error: unknown): string {
  return error instanceof Error ? error.message : String(error);
}

async function validateTableBehaviorInternal(params: {
  adapter: DatabaseReadOnlyAdapter;
  target: DbTarget;
  entry: AllowlistEntry;
  auditMode: boolean;
}): Promise<CoverageEntryResult> {
  const { adapter, target, entry, auditMode } = params;
  const checkedAt = new Date().toISOString();
  const inspection = await adapter.inspectSchemaObject(entry.resolved_name, `vee:${target}:schema:${entry.logical_name}`, {
    auditMode
  });

  if (!inspection.exists) {
    return {
      logical_name: entry.logical_name,
      resolved_name: entry.resolved_name,
      kind: entry.kind,
      status: "schema_divergente",
      row_count: null,
      checked_at: checkedAt,
      schema_summary: {
        object_type: "table",
        columns_total: 0,
        missing_allowlist_columns: []
      },
      validations: {
        read_probe: "fail",
        blocked_column_guard: "skipped",
        query_controls: "fail",
        write_policy: "fail",
        error_messages: "fail"
      },
      warnings: [],
      error: `Object "${entry.resolved_name}" does not exist in schema "${entry.resolved_name}".`
    };
  }

  const schemaColumns = inspection.columns.map(column => column.name.toLowerCase());
  const schemaColumnSet = new Set(schemaColumns);
  const missingAllowlistColumns =
    entry.allowed_columns === "*"
      ? []
      : entry.allowed_columns.filter(column => !schemaColumnSet.has(column.toLowerCase()));
  const readableColumns = pickReadableColumns(entry, schemaColumns);
  const probeColumn = pickProbeColumn(readableColumns);

  const warnings: string[] = [];
  let rowCount: number | null = null;
  let status: CoverageStatus = "ok";
  let errorMessage: string | null = null;
  let readProbe: "pass" | "fail" = "pass";
  let blockedGuard: "pass" | "fail" | "skipped" = "skipped";
  let queryControls: "pass" | "fail" = "pass";
  let writePolicy: "pass" | "fail" | "skipped" = "skipped";
  let errorMessages: "pass" | "fail" = "pass";

  if (missingAllowlistColumns.length > 0) {
    status = "schema_divergente";
    warnings.push(`Allowlist columns missing in schema: ${missingAllowlistColumns.join(", ")}`);
  }

  if (!probeColumn) {
    status = "bloqueada";
    readProbe = "fail";
    queryControls = "fail";
    warnings.push("No readable columns available after blocked-column filtering.");
  } else {
    try {
      const readResult = await adapter.executeStructuredQuery(
        {
          dbTarget: target,
          table: entry.logical_name,
          columns: readableColumns.slice(0, Math.min(readableColumns.length, 3)),
          limit: 1,
          offset: 0
        },
        `vee:${target}:coverage:read:${entry.logical_name}`,
        { auditMode }
      );
      rowCount = readResult.total;
      if (status === "ok" && readResult.total === 0) {
        status = "vazia";
      }
    } catch (error) {
      readProbe = "fail";
      const message = parseToolErrorMessage(error);
      status = classifyCoverageError(message);
      errorMessage = message;
    }

    if (readProbe === "pass") {
      try {
        await adapter.executeStructuredQuery(
          {
            dbTarget: target,
            table: entry.logical_name,
            columns: [probeColumn],
            where: [{ column: probeColumn, operator: "IS NOT NULL" }],
            orderBy: [{ column: probeColumn, direction: "ASC" }],
            limit: 2,
            offset: 0
          },
          `vee:${target}:coverage:controls:${entry.logical_name}`,
          { auditMode }
        );
      } catch (error) {
        queryControls = "fail";
        const message = parseToolErrorMessage(error);
        status = status === "ok" ? classifyCoverageError(message) : status;
        errorMessage = errorMessage ?? message;
      }
    }
  }

  if (entry.blocked_columns.length > 0) {
    const blockedColumn = entry.blocked_columns[0]!;
    try {
      await adapter.executeStructuredQuery(
        {
          dbTarget: target,
          table: entry.logical_name,
          columns: [blockedColumn],
          limit: 1
        },
        `vee:${target}:coverage:blocked:${entry.logical_name}`,
        { auditMode }
      );
      blockedGuard = "fail";
      warnings.push(`Blocked-column guard failed for "${blockedColumn}".`);
    } catch (error) {
      const message = parseToolErrorMessage(error).toLowerCase();
      blockedGuard = message.includes("blocked") ? "pass" : "fail";
    }
  }

  const policy = resolveTablePolicy(entry.logical_name, target);
  if (!policy) {
    writePolicy = "fail";
    warnings.push("Table policy not found while validating write permissions.");
  } else {
    const writeCandidate = probeColumn;
    if (!writeCandidate) {
      writePolicy = "skipped";
    } else {
      const sampleColumn = inspection.columns.find(column => column.name.toLowerCase() === writeCandidate);
      const sampleValue = pickDummyValue(sampleColumn?.column_type ?? sampleColumn?.data_type ?? "varchar", writeCandidate);
      const hasIdColumn = schemaColumnSet.has("id");
      const upsertKey = hasIdColumn ? ["id"] : inspection.primary_key.length > 0 ? [inspection.primary_key[0]!] : [];
      const upsertData: Record<string, unknown> = {};
      if (upsertKey.length > 0) {
        upsertData[upsertKey[0]!] = 1;
      }
      upsertData[writeCandidate] = sampleValue;

      try {
        buildWriteQuery({
          dbTarget: target,
          operation: "UPDATE",
          table: entry.logical_name,
          data: { [writeCandidate]: sampleValue },
          where: [{ column: upsertKey[0] ?? "id", operator: "=", value: 1 }],
          reason: "coverage_validation: update policy check"
        });

        buildWriteQuery({
          dbTarget: target,
          operation: "UPSERT",
          table: entry.logical_name,
          data: upsertData,
          upsertKey: upsertKey.length > 0 ? upsertKey : undefined,
          reason: "coverage_validation: upsert policy check"
        });

        writePolicy = policy.writeMode === "read_only" ? "fail" : "pass";
      } catch (error) {
        const message = parseToolErrorMessage(error).toLowerCase();
        if (policy.writeMode === "read_only" && message.includes("read-only")) {
          writePolicy = "pass";
        } else {
          writePolicy = "fail";
        }
      }
    }
  }

  try {
    await adapter.executeStructuredQuery(
      {
        dbTarget: target,
        table: entry.logical_name,
        columns: ["__vee_invalid_column__"],
        limit: 1
      },
      `vee:${target}:coverage:error-msg:${entry.logical_name}`,
      { auditMode }
    );
    errorMessages = "fail";
  } catch (error) {
    const message = parseToolErrorMessage(error).toLowerCase();
    errorMessages =
      message.includes("not in the allowed") || message.includes("unsafe column") || message.includes("blocked")
        ? "pass"
        : "fail";
  }

  if (status === "ok" && rowCount === 0) {
    status = "vazia";
  }
  if (blockedGuard === "fail" && status === "ok") {
    status = "bloqueada";
  }
  if (writePolicy === "fail" && status === "ok") {
    warnings.push("Write policy check failed for this table.");
  }

  return {
    logical_name: entry.logical_name,
    resolved_name: entry.resolved_name,
    kind: entry.kind,
    status,
    row_count: rowCount,
    checked_at: checkedAt,
    schema_summary: {
      object_type: inspection.object_type,
      columns_total: inspection.columns.length,
      missing_allowlist_columns: missingAllowlistColumns
    },
    validations: {
      read_probe: readProbe,
      blocked_column_guard: blockedGuard,
      query_controls: queryControls,
      write_policy: writePolicy,
      error_messages: errorMessages
    },
    warnings,
    error: errorMessage
  };
}

async function validateNamedViewInternal(params: {
  adapter: DatabaseReadOnlyAdapter;
  target: DbTarget;
  entry: AllowlistEntry;
  auditMode: boolean;
}): Promise<CoverageEntryResult> {
  const { adapter, target, entry, auditMode } = params;
  const checkedAt = new Date().toISOString();
  const inspection = await adapter.inspectSchemaObject(entry.resolved_name, `vee:${target}:view:${entry.logical_name}`, {
    auditMode
  });

  if (!inspection.exists) {
    return {
      logical_name: entry.logical_name,
      resolved_name: entry.resolved_name,
      kind: entry.kind,
      status: "schema_divergente",
      row_count: null,
      checked_at: checkedAt,
      schema_summary: {
        object_type: "view",
        columns_total: 0,
        missing_allowlist_columns: []
      },
      validations: {
        read_probe: "fail",
        blocked_column_guard: "skipped",
        query_controls: "fail",
        write_policy: "skipped",
        error_messages: "pass"
      },
      warnings: [],
      error: `Named view "${entry.logical_name}" resolves to "${entry.resolved_name}" but was not found.`
    };
  }

  let status: CoverageStatus = "ok";
  let rowCount = 0;
  let errorMessage: string | null = null;
  try {
    const result = await adapter.executeStructuredQuery(
      { dbTarget: target, table: entry.logical_name, limit: 1 },
      `vee:${target}:view-probe:${entry.logical_name}`,
      { auditMode }
    );
    rowCount = result.total;
    if (result.total === 0) {
      status = "vazia";
    }
  } catch (error) {
    const message = parseToolErrorMessage(error);
    status = classifyCoverageError(message);
    errorMessage = message;
  }

  return {
    logical_name: entry.logical_name,
    resolved_name: entry.resolved_name,
    kind: entry.kind,
    status,
    row_count: rowCount,
    checked_at: checkedAt,
    schema_summary: {
      object_type: inspection.object_type,
      columns_total: inspection.columns.length,
      missing_allowlist_columns: []
    },
    validations: {
      read_probe: status === "erro" ? "fail" : "pass",
      blocked_column_guard: "skipped",
      query_controls: status === "erro" ? "fail" : "pass",
      write_policy: "skipped",
      error_messages: "pass"
    },
    warnings: [],
    error: errorMessage
  };
}

function summarizeCoverage(results: CoverageEntryResult[]): Record<CoverageStatus, number> {
  const summary: Record<CoverageStatus, number> = {
    ok: 0,
    vazia: 0,
    schema_divergente: 0,
    bloqueada: 0,
    erro: 0
  };
  for (const result of results) {
    summary[result.status] += 1;
  }
  return summary;
}

function createCoverageJob(params: {
  dbTarget: DbTarget;
  includeNamedViews: boolean;
  auditMode: boolean;
  queue: AllowlistEntry[];
}): CoverageJobState {
  const now = new Date().toISOString();
  return {
    job_id: randomUUID(),
    db_target: params.dbTarget,
    created_at: now,
    updated_at: now,
    status: "queued",
    include_named_views: params.includeNamedViews,
    audit_mode: params.auditMode,
    queue: params.queue,
    cursor: 0,
    results: {},
    last_error: null,
    pause_reason: null
  };
}

function toCoverageJobPayload(job: CoverageJobState): Record<string, unknown> {
  const resultList = Object.values(job.results).sort((a, b) => a.logical_name.localeCompare(b.logical_name));
  return {
    job_id: job.job_id,
    db_target: job.db_target,
    status: job.status,
    created_at: job.created_at,
    updated_at: job.updated_at,
    include_named_views: job.include_named_views,
    audit_mode: job.audit_mode,
    progress: {
      total: job.queue.length,
      processed: job.cursor,
      remaining: Math.max(job.queue.length - job.cursor, 0),
      percent: job.queue.length > 0 ? Number(((job.cursor / job.queue.length) * 100).toFixed(2)) : 100
    },
    summary: summarizeCoverage(resultList),
    pause_reason: job.pause_reason,
    last_error: job.last_error,
    results: resultList
  };
}

async function runCoverageBatchInternal(params: {
  job: CoverageJobState;
  adapter: DatabaseReadOnlyAdapter;
  target: DbTarget;
  batchSize: number;
  maxRuntimeMs: number;
}): Promise<Record<string, unknown>> {
  const { job, adapter, target } = params;
  const startedAt = Date.now();
  const safeBatchSize = Math.max(1, Math.min(params.batchSize, 200));
  const safeRuntimeMs = Math.max(250, Math.min(params.maxRuntimeMs, 120000));
  let processed = 0;

  job.status = "running";
  job.pause_reason = null;
  job.last_error = null;
  job.updated_at = new Date().toISOString();

  while (job.cursor < job.queue.length && processed < safeBatchSize) {
    const elapsed = Date.now() - startedAt;
    if (elapsed >= safeRuntimeMs) {
      job.status = "paused";
      job.pause_reason = "runtime_budget_reached";
      break;
    }

    const entry = job.queue[job.cursor];
    if (!entry) {
      break;
    }

    try {
      const result =
        entry.kind === "table"
          ? await validateTableBehaviorInternal({
              adapter,
              target,
              entry,
              auditMode: job.audit_mode
            })
          : await validateNamedViewInternal({
              adapter,
              target,
              entry,
              auditMode: job.audit_mode
            });

      job.results[entry.logical_name] = result;
      job.cursor += 1;
      processed += 1;
      job.updated_at = new Date().toISOString();
    } catch (error) {
      const message = parseToolErrorMessage(error);
      const status = classifyCoverageError(message);
      job.results[entry.logical_name] = {
        logical_name: entry.logical_name,
        resolved_name: entry.resolved_name,
        kind: entry.kind,
        status,
        row_count: null,
        checked_at: new Date().toISOString(),
        schema_summary: {
          object_type: entry.kind === "named_view" ? "view" : "table",
          columns_total: 0,
          missing_allowlist_columns: []
        },
        validations: {
          read_probe: "fail",
          blocked_column_guard: "skipped",
          query_controls: "fail",
          write_policy: "skipped",
          error_messages: "fail"
        },
        warnings: [],
        error: message
      };
      job.cursor += 1;
      processed += 1;
      job.last_error = message;
      if (message.toLowerCase().includes("rate limit exceeded")) {
        job.status = "paused";
        job.pause_reason = "rate_limit_reached";
        break;
      }
    }
  }

  if (job.cursor >= job.queue.length) {
    job.status = "completed";
    job.pause_reason = null;
  } else if (job.status === "running") {
    job.status = "paused";
    job.pause_reason = "batch_completed";
  }

  job.updated_at = new Date().toISOString();

  return {
    processed_in_batch: processed,
    elapsed_ms: Date.now() - startedAt,
    ...toCoverageJobPayload(job)
  };
}

function buildCoverageMarkdown(report: Record<string, unknown>, title?: string): string {
  const generatedAt = new Date().toISOString();
  const reportTitle = title?.trim() ? title.trim() : "DB Coverage Report";
  return [
    `# ${reportTitle}`,
    "",
    `Generated at: ${generatedAt}`,
    "",
    "```json",
    JSON.stringify(report, null, 2),
    "```",
    ""
  ].join("\n");
}

function resolveWritablePath(targetPath: string): string {
  const trimmed = targetPath.trim();
  if (!trimmed) {
    throw new Error("path is required.");
  }
  if (path.isAbsolute(trimmed)) {
    return trimmed;
  }
  if (!OBSIDIAN_ROOT_PATH) {
    throw new Error("Relative path requires OBSIDIAN_ROOT_PATH to be configured.");
  }
  return path.join(OBSIDIAN_ROOT_PATH, trimmed);
}

async function writeMarkdownDocument(targetPath: string, content: string): Promise<{ ok: boolean; path: string }> {
  const fullPath = resolveWritablePath(targetPath);
  const write = await filesystemLocalAdapter.writeFile(fullPath, content);
  return { ok: write.ok, path: write.path };
}

async function appendMarkdownDocument(targetPath: string, content: string): Promise<{ ok: boolean; path: string }> {
  const fullPath = resolveWritablePath(targetPath);
  let existing = "";
  try {
    const current = await filesystemLocalAdapter.readFile(fullPath);
    existing = current.content;
  } catch {
    existing = "";
  }
  const separator = existing.length > 0 && !existing.endsWith("\n") ? "\n" : "";
  const write = await filesystemLocalAdapter.writeFile(fullPath, `${existing}${separator}${content}`);
  return { ok: write.ok, path: write.path };
}

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
        "Returns the active filesystem allowlist environment plus configured read roots and write paths. Use this to validate which directories are accessible before calling other vee_fs_* tools.",
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
          const result = filesystemLocalAdapter.listAllowedPaths();
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
        "Lists the contents of a local directory. Returns each entry's name, type (file/dir/link), size in bytes, and last modified time. Path must be absolute and under a configured FS_ALLOWED_ROOTS prefix.",
      inputSchema: {
        path: z
          .string()
          .min(1)
          .describe("Absolute path to the directory to list (e.g. /workspace/project)"),
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
          const result = await filesystemLocalAdapter.listDirectory(path, max_entries);
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
        "Reads the contents of a local file. Truncates at FS_MAX_FILE_BYTES (default 256 KB). Returns content, total size, and whether it was truncated. Path must be absolute and under a configured FS_ALLOWED_ROOTS prefix.",
      inputSchema: {
        path: z
          .string()
          .min(1)
          .describe("Absolute path to the file to read (e.g. /workspace/project/.env)"),
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
          const result = await filesystemLocalAdapter.readFile(path, max_bytes);
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
        "Searches for a text pattern in local files under a path. Returns matching file paths, line numbers, and matched lines. Path must be absolute and under a configured FS_ALLOWED_ROOTS prefix.",
      inputSchema: {
        pattern: z
          .string()
          .min(1)
          .max(200)
          .describe(
            "Search text pattern (substring match)"
          ),
        path: z
          .string()
          .min(1)
          .describe("Absolute path to search in — file or directory (e.g. /workspace/project)"),
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
          .describe("If true, search is case-insensitive. Default false."),
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
          const result = await filesystemLocalAdapter.searchText(pattern, path, {
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
        "Writes content to a local file. Requires FS_WRITE_ENABLED=true and path must match FS_WRITE_ALLOWED_PATHS (if configured). Creates or overwrites the file.",
      inputSchema: {
        path: z
          .string()
          .min(1)
          .describe("Absolute path to the file to write (e.g. /workspace/project/config.json)"),
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
          const result = await filesystemLocalAdapter.writeFile(path, content);
          return {
            content: [{ type: "text", text: JSON.stringify(result, null, 2) }],
            structuredContent: result
          };
        }
      })
  );

  // ─── DB generic tools (v0.3) ───────────────────────────────────────────────

  server.registerTool(
    "vee_db_list_allowlist",
    {
      title: "DB — List Allowlist",
      description:
        "Lists the effective DB allowlist used by vee_db_query/vee_db_write, including tables, named views, blocked columns, and write modes. " +
        "Includes entries from base code and optional env overlays (DB_ALLOWLIST_EXTRA_TABLE_POLICIES_JSON / DB_ALLOWLIST_EXTRA_NAMED_VIEWS_JSON).",
      inputSchema: {
        db_target: dbTargetSchema
      }
    },
    async ({ db_target }) =>
      runAuditedTool({
        toolName: "vee_db_list_allowlist",
        permission: "read_only",
        isWrite: false,
        args: { db_target: db_target ?? DB_DEFAULT_TARGET },
        errorContext: "Failed to list DB allowlist",
        handler: async () => {
          const target = db_target ?? DB_DEFAULT_TARGET;
          const entries = listQueryAllowlist(target);
          const writeGroups = listWritePolicyGroups(target);
          const tables = entries.filter(entry => entry.kind === "table");
          const namedViews = entries.filter(entry => entry.kind === "named_view");
          const blockedColumns = tables
            .filter(entry => entry.blocked_columns.length > 0)
            .map(entry => ({ table: entry.logical_name, blocked_columns: entry.blocked_columns }));
          const mmccSafeExecuteConfigured = dedupeStrings([
            ...parseCsvEnvVar("DB_SAFE_EXECUTE_TABLES_MMCC"),
            ...parseCsvEnvVar("DB_SAFE_EXECUTE_TABLES")
          ]);
          const mmccApprovalRequiredConfigured = dedupeStrings([
            ...parseCsvEnvVar("DB_APPROVAL_REQUIRED_TABLES_MMCC"),
            ...parseCsvEnvVar("DB_APPROVAL_REQUIRED_TABLES")
          ]);

          const payload = {
            db_target: target,
            totals: {
              entries: entries.length,
              tables: tables.length,
              named_views: namedViews.length,
              sensitive_tables: tables.filter(entry => entry.sensitive).length,
              with_blocked_columns: blockedColumns.length
            },
            overlays: {
              env_table_overrides: tables.filter(entry => entry.source === "env").length,
              env_named_view_overrides: namedViews.filter(entry => entry.source === "env").length
            },
            policy_groups: {
              safe_execute: writeGroups.safe_execute,
              approval_required: writeGroups.approval_required,
              read_only: writeGroups.read_only,
              mm_criativos_baseline: {
                safe_execute: MMCRIATIVOS_SAFE_EXECUTE_BASE,
                approval_required: MMCRIATIVOS_APPROVAL_REQUIRED_BASE
              },
              mmcc_baseline: {
                safe_execute: MMCC_SAFE_EXECUTE_BASE,
                approval_required: MMCC_APPROVAL_REQUIRED_BASE
              },
              mmcc_configured: {
                safe_execute: mmccSafeExecuteConfigured,
                approval_required: mmccApprovalRequiredConfigured
              }
            },
            blocked_columns: blockedColumns,
            entries
          };

          return {
            content: [{ type: "text", text: JSON.stringify(payload, null, 2) }],
            structuredContent: payload
          };
        }
      })
  );

  server.registerTool(
    "vee_db_inspect_schema_object",
    {
      title: "DB - Inspect Schema Object",
      description:
        "Inspects one allowlisted table/view and returns object type, columns, nullability, defaults, PK and FK metadata.",
      inputSchema: {
        db_target: dbTargetSchema,
        object_name: z.string().min(1).describe("Allowlisted table or named view"),
        audit_mode: z.boolean().optional().describe("Use audit rate-limit mode for metadata reads (default true)")
      }
    },
    async ({ db_target, object_name, audit_mode }) =>
      runAuditedTool({
        toolName: "vee_db_inspect_schema_object",
        permission: "read_only",
        isWrite: false,
        args: { db_target: db_target ?? DB_DEFAULT_TARGET, object_name, audit_mode: audit_mode ?? true },
        errorContext: `Failed to inspect schema object "${object_name}"`,
        handler: async () => {
          const { target, adapter } = resolveReadOnlyAdapter(db_target);
          const entry = resolveAllowlistEntryOrThrow(object_name, target);
          const inspection = await adapter.inspectSchemaObject(
            entry.resolved_name,
            `vee:${target}:schema-inspect:${entry.logical_name}`,
            { auditMode: audit_mode ?? true }
          );

          const payload = {
            db_target: target,
            allowlist_entry: entry,
            inspection
          };
          return {
            content: [{ type: "text", text: JSON.stringify(payload, null, 2) }],
            structuredContent: payload
          };
        }
      })
  );

  server.registerTool(
    "vee_db_list_table_indexes",
    {
      title: "DB - List Table Indexes",
      description: "Lists indexes for one allowlisted table/view (name, uniqueness, type, cardinality, ordered columns).",
      inputSchema: {
        db_target: dbTargetSchema,
        object_name: z.string().min(1).describe("Allowlisted table or named view"),
        audit_mode: z.boolean().optional().describe("Use audit rate-limit mode for metadata reads (default true)")
      }
    },
    async ({ db_target, object_name, audit_mode }) =>
      runAuditedTool({
        toolName: "vee_db_list_table_indexes",
        permission: "read_only",
        isWrite: false,
        args: { db_target: db_target ?? DB_DEFAULT_TARGET, object_name, audit_mode: audit_mode ?? true },
        errorContext: `Failed to list indexes for "${object_name}"`,
        handler: async () => {
          const { target, adapter } = resolveReadOnlyAdapter(db_target);
          const entry = resolveAllowlistEntryOrThrow(object_name, target);
          const indexes = await adapter.listSchemaObjectIndexes(
            entry.resolved_name,
            `vee:${target}:schema-index:${entry.logical_name}`,
            { auditMode: audit_mode ?? true }
          );
          const payload = {
            db_target: target,
            allowlist_entry: entry,
            indexes
          };
          return {
            content: [{ type: "text", text: JSON.stringify(payload, null, 2) }],
            structuredContent: payload
          };
        }
      })
  );

  server.registerTool(
    "vee_db_list_table_relations",
    {
      title: "DB - List Table Relations",
      description: "Lists outgoing and incoming FK relations for one allowlisted table/view.",
      inputSchema: {
        db_target: dbTargetSchema,
        object_name: z.string().min(1).describe("Allowlisted table or named view"),
        audit_mode: z.boolean().optional().describe("Use audit rate-limit mode for metadata reads (default true)")
      }
    },
    async ({ db_target, object_name, audit_mode }) =>
      runAuditedTool({
        toolName: "vee_db_list_table_relations",
        permission: "read_only",
        isWrite: false,
        args: { db_target: db_target ?? DB_DEFAULT_TARGET, object_name, audit_mode: audit_mode ?? true },
        errorContext: `Failed to list relations for "${object_name}"`,
        handler: async () => {
          const { target, adapter } = resolveReadOnlyAdapter(db_target);
          const entry = resolveAllowlistEntryOrThrow(object_name, target);
          const relations = await adapter.listSchemaObjectRelations(
            entry.resolved_name,
            `vee:${target}:schema-rel:${entry.logical_name}`,
            { auditMode: audit_mode ?? true }
          );
          const payload = {
            db_target: target,
            allowlist_entry: entry,
            relations
          };
          return {
            content: [{ type: "text", text: JSON.stringify(payload, null, 2) }],
            structuredContent: payload
          };
        }
      })
  );

  server.registerTool(
    "vee_db_list_allowlist_schema",
    {
      title: "DB - List Allowlist Schema",
      description:
        "Lists all allowlisted tables/views with full schema metadata (object type, columns, PK/FK, write policy, blocked columns).",
      inputSchema: {
        db_target: dbTargetSchema,
        include_named_views: z.boolean().optional().describe("Include named views (default true)"),
        table_filter: z.array(z.string().min(1)).optional().describe("Optional allowlist filter by logical/resolved names"),
        audit_mode: z.boolean().optional().describe("Use audit rate-limit mode for metadata reads (default true)")
      }
    },
    async ({ db_target, include_named_views, table_filter, audit_mode }) =>
      runAuditedTool({
        toolName: "vee_db_list_allowlist_schema",
        permission: "read_only",
        isWrite: false,
        args: {
          db_target: db_target ?? DB_DEFAULT_TARGET,
          include_named_views: include_named_views ?? true,
          table_filter,
          audit_mode: audit_mode ?? true
        },
        errorContext: "Failed to list allowlist schema metadata",
        handler: async () => {
          const includeViews = include_named_views ?? true;
          const { target, adapter } = resolveReadOnlyAdapter(db_target);
          const queue = getCoverageQueue(target, includeViews, table_filter);
          const metadata = await adapter.listSchemaObjectsMetadata(
            queue.map(entry => resolveActualTableName(entry.resolved_name, target)),
            `vee:${target}:allowlist-schema`,
            { auditMode: audit_mode ?? true }
          );
          const metadataByObject = new Map(metadata.map(item => [item.object_name, item]));
          const entries = queue.map(entry => ({
            ...entry,
            schema: metadataByObject.get(resolveActualTableName(entry.resolved_name, target)) ?? null
          }));
          const payload = {
            db_target: target,
            totals: {
              entries: entries.length,
              tables: entries.filter(entry => entry.kind === "table").length,
              named_views: entries.filter(entry => entry.kind === "named_view").length,
              missing_in_schema: entries.filter(entry => !entry.schema || !entry.schema.exists).length
            },
            entries
          };
          return {
            content: [{ type: "text", text: JSON.stringify(payload, null, 2) }],
            structuredContent: payload
          };
        }
      })
  );

  server.registerTool(
    "vee_db_discover_relationships",
    {
      title: "DB - Discover Relationships",
      description:
        "Discovers relationships between allowlisted tables using FK metadata and optional heuristic *_id inference.",
      inputSchema: {
        db_target: dbTargetSchema,
        tables: z.array(z.string().min(1)).optional().describe("Optional subset of allowlisted tables"),
        include_heuristics: z.boolean().optional().describe("Include *_id heuristic relationships (default true)"),
        audit_mode: z.boolean().optional().describe("Use audit rate-limit mode for metadata reads (default true)")
      }
    },
    async ({ db_target, tables, include_heuristics, audit_mode }) =>
      runAuditedTool({
        toolName: "vee_db_discover_relationships",
        permission: "read_only",
        isWrite: false,
        args: {
          db_target: db_target ?? DB_DEFAULT_TARGET,
          tables,
          include_heuristics: include_heuristics ?? true,
          audit_mode: audit_mode ?? true
        },
        errorContext: "Failed to discover table relationships",
        handler: async () => {
          const { target, adapter } = resolveReadOnlyAdapter(db_target);
          const requestedEntries = (tables && tables.length > 0
            ? tables.map(name => resolveAllowlistEntryOrThrow(name, target))
            : getCoverageQueue(target, false)
          ).filter(entry => entry.kind === "table");
          const objectNames = [...new Set(requestedEntries.map(entry => resolveActualTableName(entry.resolved_name, target)))];
          const relationships = await adapter.discoverRelationships(
            objectNames,
            `vee:${target}:rel-discovery`,
            { includeHeuristics: include_heuristics ?? true, auditMode: audit_mode ?? true }
          );
          const payload = {
            db_target: target,
            include_heuristics: include_heuristics ?? true,
            tables: objectNames,
            total_relationships: relationships.length,
            relationships
          };
          return {
            content: [{ type: "text", text: JSON.stringify(payload, null, 2) }],
            structuredContent: payload
          };
        }
      })
  );

  server.registerTool(
    "vee_db_test_join_assisted",
    {
      title: "DB - Test Join Assisted",
      description:
        "Tests a basic JOIN between two allowlisted tables using provided columns or auto-discovered relationship metadata.",
      inputSchema: {
        db_target: dbTargetSchema,
        left_table: z.string().min(1).describe("Base table/view for SELECT"),
        right_table: z.string().min(1).describe("Table/view to JOIN"),
        left_column: z.string().min(1).optional().describe("Join column from left_table"),
        right_column: z.string().min(1).optional().describe("Join column from right_table"),
        join_type: z.enum(["INNER", "LEFT", "RIGHT"]).optional().describe("JOIN type (default INNER)"),
        limit: z.number().int().min(1).max(50).optional().describe("Rows to return (default 5)"),
        audit_mode: z.boolean().optional().describe("Use audit rate-limit mode for test query (default true)")
      }
    },
    async ({ db_target, left_table, right_table, left_column, right_column, join_type, limit, audit_mode }) =>
      runAuditedTool({
        toolName: "vee_db_test_join_assisted",
        permission: "read_only",
        isWrite: false,
        args: {
          db_target: db_target ?? DB_DEFAULT_TARGET,
          left_table,
          right_table,
          left_column,
          right_column,
          join_type: join_type ?? "INNER",
          limit: limit ?? 5,
          audit_mode: audit_mode ?? true
        },
        errorContext: `Failed to run assisted join between "${left_table}" and "${right_table}"`,
        handler: async () => {
          const { target, adapter } = resolveReadOnlyAdapter(db_target);
          const leftEntry = resolveAllowlistEntryOrThrow(left_table, target);
          const rightEntry = resolveAllowlistEntryOrThrow(right_table, target);
          let joinLeftColumn = left_column?.trim().toLowerCase();
          let joinRightColumn = right_column?.trim().toLowerCase();
          let relationshipHint: Record<string, unknown> | null = null;

          if (!joinLeftColumn || !joinRightColumn) {
            const discovered = await adapter.discoverRelationships(
              [
                resolveActualTableName(leftEntry.resolved_name, target),
                resolveActualTableName(rightEntry.resolved_name, target)
              ],
              `vee:${target}:join-hint:${leftEntry.logical_name}:${rightEntry.logical_name}`,
              { includeHeuristics: true, auditMode: audit_mode ?? true }
            );

            const leftResolved = resolveActualTableName(leftEntry.resolved_name, target);
            const rightResolved = resolveActualTableName(rightEntry.resolved_name, target);
            const direct = discovered.find(
              rel => rel.source_table === leftResolved && rel.target_table === rightResolved
            );
            const inverse = discovered.find(
              rel => rel.source_table === rightResolved && rel.target_table === leftResolved
            );

            if (direct) {
              joinLeftColumn = joinLeftColumn ?? direct.source_column;
              joinRightColumn = joinRightColumn ?? direct.target_column;
              relationshipHint = direct;
            } else if (inverse) {
              joinLeftColumn = joinLeftColumn ?? inverse.target_column;
              joinRightColumn = joinRightColumn ?? inverse.source_column;
              relationshipHint = inverse;
            }
          }

          if (!joinLeftColumn || !joinRightColumn) {
            throw new Error(
              `Could not infer join columns between "${left_table}" and "${right_table}". Provide left_column and right_column.`
            );
          }

          const onClause = `${leftEntry.logical_name}.${joinLeftColumn} = ${rightEntry.logical_name}.${joinRightColumn}`;
          const result = await adapter.executeStructuredQuery(
            {
              dbTarget: target,
              table: leftEntry.logical_name,
              columns: [
                `${leftEntry.logical_name}.${joinLeftColumn}`,
                `${rightEntry.logical_name}.${joinRightColumn}`
              ],
              joins: [
                {
                  type: join_type ?? "INNER",
                  table: rightEntry.logical_name,
                  on: onClause
                }
              ],
              limit: limit ?? 5
            },
            `vee:${target}:join-test:${leftEntry.logical_name}:${rightEntry.logical_name}`,
            { auditMode: audit_mode ?? true }
          );

          const payload = {
            db_target: target,
            join: {
              left_table: leftEntry.logical_name,
              right_table: rightEntry.logical_name,
              on: onClause,
              join_type: join_type ?? "INNER",
              relationship_hint: relationshipHint
            },
            result
          };
          return {
            content: [{ type: "text", text: JSON.stringify(payload, null, 2) }],
            structuredContent: payload
          };
        }
      })
  );

  server.registerTool(
    "vee_db_validate_named_views",
    {
      title: "DB - Validate Named Views",
      description:
        "Validates allowlisted named views by checking existence, schema visibility and minimal read probe.",
      inputSchema: {
        db_target: dbTargetSchema,
        views: z.array(z.string().min(1)).optional().describe("Optional subset of named views"),
        audit_mode: z.boolean().optional().describe("Use audit rate-limit mode for test query (default true)")
      }
    },
    async ({ db_target, views, audit_mode }) =>
      runAuditedTool({
        toolName: "vee_db_validate_named_views",
        permission: "read_only",
        isWrite: false,
        args: { db_target: db_target ?? DB_DEFAULT_TARGET, views, audit_mode: audit_mode ?? true },
        errorContext: "Failed to validate named views",
        handler: async () => {
          const { target, adapter } = resolveReadOnlyAdapter(db_target);
          const selected = (views && views.length > 0
            ? views.map(name => resolveAllowlistEntryOrThrow(name, target))
            : getCoverageQueue(target, true)
          ).filter(entry => entry.kind === "named_view");
          const results: CoverageEntryResult[] = [];
          for (const entry of selected) {
            const result = await validateNamedViewInternal({
              adapter,
              target,
              entry,
              auditMode: audit_mode ?? true
            });
            results.push(result);
          }
          const payload = {
            db_target: target,
            total: results.length,
            summary: summarizeCoverage(results),
            results
          };
          return {
            content: [{ type: "text", text: JSON.stringify(payload, null, 2) }],
            structuredContent: payload
          };
        }
      })
  );

  server.registerTool(
    "vee_db_validate_table_behavior",
    {
      title: "DB - Validate Table Behavior",
      description:
        "Validates read behavior, blocked-column guard, WHERE/ORDER/LIMIT controls, write policy checks, and error-message patterns for one allowlisted table.",
      inputSchema: {
        db_target: dbTargetSchema,
        table: z.string().min(1).describe("Allowlisted table logical/resolved name"),
        audit_mode: z.boolean().optional().describe("Use audit rate-limit mode for validation probes (default true)")
      }
    },
    async ({ db_target, table, audit_mode }) =>
      runAuditedTool({
        toolName: "vee_db_validate_table_behavior",
        permission: "read_only",
        isWrite: false,
        args: { db_target: db_target ?? DB_DEFAULT_TARGET, table, audit_mode: audit_mode ?? true },
        errorContext: `Failed to validate table behavior for "${table}"`,
        handler: async () => {
          const { target, adapter } = resolveReadOnlyAdapter(db_target);
          const entry = resolveAllowlistEntryOrThrow(table, target);
          if (entry.kind !== "table") {
            throw new Error(`"${table}" resolves to a named view. Use vee_db_validate_named_views for views.`);
          }
          const result = await validateTableBehaviorInternal({
            adapter,
            target,
            entry,
            auditMode: audit_mode ?? true
          });
          const payload = {
            db_target: target,
            result
          };
          return {
            content: [{ type: "text", text: JSON.stringify(payload, null, 2) }],
            structuredContent: payload
          };
        }
      })
  );

  server.registerTool(
    "vee_db_schedule_coverage_scan",
    {
      title: "DB - Schedule Coverage Scan",
      description:
        "Creates a queued coverage job for allowlisted tables/views. Supports partial execution and resume via job_id.",
      inputSchema: {
        db_target: dbTargetSchema,
        include_named_views: z.boolean().optional().describe("Include named views in queue (default false)"),
        table_filter: z.array(z.string().min(1)).optional().describe("Optional subset by logical/resolved name"),
        audit_mode: z.boolean().optional().describe("Use audit rate-limit mode for scan probes (default true)"),
        run_initial_batch: z.boolean().optional().describe("Run first batch immediately (default true)"),
        initial_batch_size: z.number().int().min(1).max(200).optional().describe("Initial batch size (default 10)"),
        initial_max_runtime_ms: z.number().int().min(250).max(120000).optional().describe("Initial batch runtime budget")
      }
    },
    async ({
      db_target,
      include_named_views,
      table_filter,
      audit_mode,
      run_initial_batch,
      initial_batch_size,
      initial_max_runtime_ms
    }) =>
      runAuditedTool({
        toolName: "vee_db_schedule_coverage_scan",
        permission: "read_only",
        isWrite: false,
        args: {
          db_target: db_target ?? DB_DEFAULT_TARGET,
          include_named_views: include_named_views ?? false,
          table_filter,
          audit_mode: audit_mode ?? true,
          run_initial_batch: run_initial_batch ?? true,
          initial_batch_size: initial_batch_size ?? 10,
          initial_max_runtime_ms: initial_max_runtime_ms ?? 15000
        },
        errorContext: "Failed to schedule coverage scan",
        handler: async () => {
          cleanupCoverageJobs();
          const includeViews = include_named_views ?? false;
          const { target, adapter } = resolveReadOnlyAdapter(db_target);
          const queue = getCoverageQueue(target, includeViews, table_filter);
          if (queue.length === 0) {
            throw new Error("Coverage queue is empty after filters.");
          }
          const job = createCoverageJob({
            dbTarget: target,
            includeNamedViews: includeViews,
            auditMode: audit_mode ?? true,
            queue
          });
          coverageJobs.set(job.job_id, job);

          let payload = toCoverageJobPayload(job);
          if (run_initial_batch ?? true) {
            payload = await runCoverageBatchInternal({
              job,
              adapter,
              target,
              batchSize: initial_batch_size ?? 10,
              maxRuntimeMs: initial_max_runtime_ms ?? 15000
            });
          }

          return {
            content: [{ type: "text", text: JSON.stringify(payload, null, 2) }],
            structuredContent: payload
          };
        }
      })
  );

  server.registerTool(
    "vee_db_run_coverage_batch",
    {
      title: "DB - Run Coverage Batch",
      description:
        "Runs the next batch for a scheduled coverage job with queue/pause semantics and resumable progress.",
      inputSchema: {
        job_id: z.string().min(1).describe("Coverage job id returned by vee_db_schedule_coverage_scan"),
        batch_size: z.number().int().min(1).max(200).optional().describe("How many entries to process"),
        max_runtime_ms: z.number().int().min(250).max(120000).optional().describe("Runtime budget in milliseconds")
      }
    },
    async ({ job_id, batch_size, max_runtime_ms }) =>
      runAuditedTool({
        toolName: "vee_db_run_coverage_batch",
        permission: "read_only",
        isWrite: false,
        args: { job_id, batch_size: batch_size ?? 10, max_runtime_ms: max_runtime_ms ?? 15000 },
        errorContext: `Failed to run coverage batch for job "${job_id}"`,
        handler: async () => {
          cleanupCoverageJobs();
          const job = coverageJobs.get(job_id);
          if (!job) {
            throw new Error(`Coverage job "${job_id}" not found or expired.`);
          }
          const { target, adapter } = resolveReadOnlyAdapter(job.db_target);
          const payload = await runCoverageBatchInternal({
            job,
            adapter,
            target,
            batchSize: batch_size ?? 10,
            maxRuntimeMs: max_runtime_ms ?? 15000
          });
          return {
            content: [{ type: "text", text: JSON.stringify(payload, null, 2) }],
            structuredContent: payload
          };
        }
      })
  );

  server.registerTool(
    "vee_db_get_coverage_status",
    {
      title: "DB - Get Coverage Status",
      description:
        "Returns status for one coverage job or a summary list of active jobs, including resumable progress details.",
      inputSchema: {
        job_id: z.string().optional().describe("Coverage job id. If omitted, returns all active jobs."),
        include_results: z.boolean().optional().describe("Include per-table results (default true)")
      }
    },
    async ({ job_id, include_results }) =>
      runAuditedTool({
        toolName: "vee_db_get_coverage_status",
        permission: "read_only",
        isWrite: false,
        args: { job_id: job_id ?? null, include_results: include_results ?? true },
        errorContext: "Failed to get coverage status",
        handler: async () => {
          cleanupCoverageJobs();
          if (job_id) {
            const job = coverageJobs.get(job_id);
            if (!job) {
              throw new Error(`Coverage job "${job_id}" not found or expired.`);
            }
            const payload = toCoverageJobPayload(job);
            if (include_results === false) {
              const compact = { ...payload };
              delete compact.results;
              return {
                content: [{ type: "text", text: JSON.stringify(compact, null, 2) }],
                structuredContent: compact
              };
            }
            return {
              content: [{ type: "text", text: JSON.stringify(payload, null, 2) }],
              structuredContent: payload
            };
          }

          const jobs = [...coverageJobs.values()]
            .sort((a, b) => b.updated_at.localeCompare(a.updated_at))
            .map(job => {
              const payload = toCoverageJobPayload(job);
              if (include_results === false) {
                const compact = { ...payload };
                delete compact.results;
                return compact;
              }
              return payload;
            });

          const payload = { total_jobs: jobs.length, jobs };
          return {
            content: [{ type: "text", text: JSON.stringify(payload, null, 2) }],
            structuredContent: payload
          };
        }
      })
  );

  server.registerTool(
    "vee_db_generate_coverage_report",
    {
      title: "DB - Generate Coverage Report",
      description:
        "Generates a consolidated coverage report from a scheduled job or by running a full ad-hoc scan.",
      inputSchema: {
        job_id: z.string().optional().describe("Existing coverage job id"),
        db_target: dbTargetSchema,
        include_named_views: z.boolean().optional().describe("For ad-hoc mode, include named views (default false)"),
        table_filter: z.array(z.string().min(1)).optional().describe("For ad-hoc mode, limit queue to subset"),
        audit_mode: z.boolean().optional().describe("For ad-hoc mode, use audit rate-limit mode (default true)")
      }
    },
    async ({ job_id, db_target, include_named_views, table_filter, audit_mode }) =>
      runAuditedTool({
        toolName: "vee_db_generate_coverage_report",
        permission: "read_only",
        isWrite: false,
        args: {
          job_id: job_id ?? null,
          db_target: db_target ?? DB_DEFAULT_TARGET,
          include_named_views: include_named_views ?? false,
          table_filter,
          audit_mode: audit_mode ?? true
        },
        errorContext: "Failed to generate coverage report",
        handler: async () => {
          cleanupCoverageJobs();
          let reportPayload: Record<string, unknown>;

          if (job_id) {
            const job = coverageJobs.get(job_id);
            if (!job) {
              throw new Error(`Coverage job "${job_id}" not found or expired.`);
            }
            reportPayload = toCoverageJobPayload(job);
          } else {
            const includeViews = include_named_views ?? false;
            const { target, adapter } = resolveReadOnlyAdapter(db_target);
            const queue = getCoverageQueue(target, includeViews, table_filter);
            if (queue.length === 0) {
              throw new Error("Coverage queue is empty after filters.");
            }
            const tempJob = createCoverageJob({
              dbTarget: target,
              includeNamedViews: includeViews,
              auditMode: audit_mode ?? true,
              queue
            });
            while (tempJob.status !== "completed") {
              await runCoverageBatchInternal({
                job: tempJob,
                adapter,
                target,
                batchSize: 100,
                maxRuntimeMs: 120000
              });
              if (tempJob.status === "paused" && tempJob.pause_reason === "rate_limit_reached") {
                break;
              }
            }
            reportPayload = toCoverageJobPayload(tempJob);
          }

          const payload = {
            generated_at: new Date().toISOString(),
            report: reportPayload
          };
          return {
            content: [{ type: "text", text: JSON.stringify(payload, null, 2) }],
            structuredContent: payload
          };
        }
      })
  );

  server.registerTool(
    "vee_db_save_coverage_report_markdown",
    {
      title: "DB - Save Coverage Report Markdown",
      description:
        "Saves a consolidated coverage report as markdown to an allowed filesystem path (absolute path or vault-relative path).",
      inputSchema: {
        path: z.string().min(1).describe("Output path (absolute or OBSIDIAN_ROOT_PATH-relative)"),
        job_id: z.string().optional().describe("Coverage job id to persist"),
        report: z.record(z.string(), z.unknown()).optional().describe("Explicit report payload (used when job_id is omitted)"),
        title: z.string().optional().describe("Optional markdown title")
      }
    },
    async ({ path: output_path, job_id, report, title }) =>
      runAuditedTool({
        toolName: "vee_db_save_coverage_report_markdown",
        permission: "safe_execute",
        isWrite: true,
        args: { path: output_path, job_id: job_id ?? null, has_report: !!report, title: title ?? null },
        errorContext: `Failed to save coverage report markdown to "${output_path}"`,
        handler: async () => {
          cleanupCoverageJobs();
          let resolvedReport: Record<string, unknown>;
          if (report) {
            resolvedReport = report;
          } else if (job_id) {
            const job = coverageJobs.get(job_id);
            if (!job) {
              throw new Error(`Coverage job "${job_id}" not found or expired.`);
            }
            resolvedReport = toCoverageJobPayload(job);
          } else {
            throw new Error("Provide either report or job_id.");
          }

          const markdown = buildCoverageMarkdown(resolvedReport, title);
          const write = await writeMarkdownDocument(output_path, markdown);
          const payload = {
            ok: true,
            path: write.path,
            bytes: Buffer.byteLength(markdown, "utf8")
          };
          return {
            content: [{ type: "text", text: JSON.stringify(payload, null, 2) }],
            structuredContent: payload
          };
        }
      })
  );

  server.registerTool(
    "vee_db_update_allowlist_inventory_doc",
    {
      title: "DB - Update Allowlist Inventory Doc",
      description:
        "Builds and writes a live markdown inventory of approved allowlisted tables/views and key schema metadata.",
      inputSchema: {
        db_target: dbTargetSchema,
        path: z.string().min(1).describe("Output path (absolute or OBSIDIAN_ROOT_PATH-relative)"),
        include_named_views: z.boolean().optional().describe("Include named views (default true)"),
        audit_mode: z.boolean().optional().describe("Use audit rate-limit mode for metadata reads (default true)")
      }
    },
    async ({ db_target, path: output_path, include_named_views, audit_mode }) =>
      runAuditedTool({
        toolName: "vee_db_update_allowlist_inventory_doc",
        permission: "safe_execute",
        isWrite: true,
        args: {
          db_target: db_target ?? DB_DEFAULT_TARGET,
          path: output_path,
          include_named_views: include_named_views ?? true,
          audit_mode: audit_mode ?? true
        },
        errorContext: `Failed to update allowlist inventory document "${output_path}"`,
        handler: async () => {
          const includeViews = include_named_views ?? true;
          const { target, adapter } = resolveReadOnlyAdapter(db_target);
          const queue = getCoverageQueue(target, includeViews);
          const metadata = await adapter.listSchemaObjectsMetadata(
            queue.map(entry => resolveActualTableName(entry.resolved_name, target)),
            `vee:${target}:inventory-doc`,
            { auditMode: audit_mode ?? true }
          );
          const metadataByObject = new Map(metadata.map(item => [item.object_name, item]));

          const lines: string[] = [];
          lines.push("# DB Allowlist Inventory");
          lines.push("");
          lines.push(`Generated at: ${new Date().toISOString()}`);
          lines.push(`DB target: ${target}`);
          lines.push("");
          lines.push("| logical_name | resolved_name | kind | object_type | exists | write_mode | blocked_columns |");
          lines.push("| --- | --- | --- | --- | --- | --- | --- |");

          for (const entry of queue) {
            const schema = metadataByObject.get(resolveActualTableName(entry.resolved_name, target));
            lines.push(
              `| ${entry.logical_name} | ${entry.resolved_name} | ${entry.kind} | ${schema?.object_type ?? "-"} | ${schema?.exists ? "yes" : "no"} | ${entry.write_mode} | ${(entry.blocked_columns ?? []).join(", ")} |`
            );
          }

          lines.push("");
          lines.push("## Raw");
          lines.push("");
          lines.push("```json");
          lines.push(
            JSON.stringify(
              queue.map(entry => ({
                ...entry,
                schema: metadataByObject.get(resolveActualTableName(entry.resolved_name, target)) ?? null
              })),
              null,
              2
            )
          );
          lines.push("```");
          lines.push("");

          const markdown = lines.join("\n");
          const write = await writeMarkdownDocument(output_path, markdown);
          const payload = {
            ok: true,
            db_target: target,
            path: write.path,
            entries: queue.length,
            bytes: Buffer.byteLength(markdown, "utf8")
          };
          return {
            content: [{ type: "text", text: JSON.stringify(payload, null, 2) }],
            structuredContent: payload
          };
        }
      })
  );

  server.registerTool(
    "vee_db_append_schema_observation",
    {
      title: "DB - Append Schema Observation",
      description:
        "Appends timestamped schema observations for an allowlisted table/view to a markdown file, keeping a living inventory log.",
      inputSchema: {
        db_target: dbTargetSchema,
        object_name: z.string().min(1).describe("Allowlisted table/view name"),
        observation: z.string().min(1).describe("Observation text"),
        path: z.string().min(1).describe("Output path (absolute or OBSIDIAN_ROOT_PATH-relative)"),
        audit_mode: z.boolean().optional().describe("Use audit rate-limit mode for metadata reads (default true)")
      }
    },
    async ({ db_target, object_name, observation, path: output_path, audit_mode }) =>
      runAuditedTool({
        toolName: "vee_db_append_schema_observation",
        permission: "safe_execute",
        isWrite: true,
        args: {
          db_target: db_target ?? DB_DEFAULT_TARGET,
          object_name,
          path: output_path,
          audit_mode: audit_mode ?? true
        },
        errorContext: `Failed to append schema observation for "${object_name}"`,
        handler: async () => {
          const { target, adapter } = resolveReadOnlyAdapter(db_target);
          const entry = resolveAllowlistEntryOrThrow(object_name, target);
          const inspection = await adapter.inspectSchemaObject(
            entry.resolved_name,
            `vee:${target}:obs:${entry.logical_name}`,
            { auditMode: audit_mode ?? true }
          );

          const block = [
            `## ${new Date().toISOString()} - ${entry.logical_name}`,
            "",
            `- resolved_name: ${entry.resolved_name}`,
            `- kind: ${entry.kind}`,
            `- object_type: ${inspection.object_type}`,
            `- exists: ${inspection.exists}`,
            `- columns_total: ${inspection.columns.length}`,
            `- foreign_keys: ${inspection.foreign_keys.length}`,
            `- note: ${observation.trim()}`,
            ""
          ].join("\n");

          const write = await appendMarkdownDocument(output_path, block);
          const payload = {
            ok: true,
            db_target: target,
            object_name: entry.logical_name,
            path: write.path
          };
          return {
            content: [{ type: "text", text: JSON.stringify(payload, null, 2) }],
            structuredContent: payload
          };
        }
      })
  );

  server.registerTool(
    "vee_db_query",
    {
      title: "DB — Structured Query",
      description:
        "Executes a structured SELECT against allowed tables or named views (project_timeline, active_projects, pending_work). " +
        "Supports WHERE filters, JOINs, ORDER BY, and pagination. No raw SQL — all inputs are validated. " +
        "Tables with blocked columns require an explicit 'columns' list. Returns pagination/performance metadata and normalized row schema. Max 100 rows.",
      inputSchema: {
        db_target: dbTargetSchema,
        table: z
          .string()
          .min(1)
          .describe(
            "Table name (e.g. projects, project_tasks) or named view (project_timeline, active_projects, pending_work)"
          ),
        columns: z
          .array(z.string().min(1))
          .optional()
          .describe("Columns to return. Required for tables with blocked columns."),
        where: z.array(whereConditionSchema).optional().describe("WHERE conditions (AND-joined)"),
        joins: z.array(joinClauseSchema).optional().describe("JOIN clauses"),
        order_by: z.array(orderBySchema).optional().describe("ORDER BY clauses"),
        limit: z.number().int().min(1).max(100).optional().describe("Max rows (default 20, max 100)"),
        offset: z.number().int().min(0).optional().describe("Row offset for pagination")
      }
    },
    async ({ db_target, table, columns, where, joins, order_by, limit, offset }) =>
      runAuditedTool({
        toolName: "vee_db_query",
        permission: "read_only",
        isWrite: false,
        args: {
          db_target: db_target ?? DB_DEFAULT_TARGET,
          table,
          columns,
          where_count: where?.length ?? 0,
          joins_count: joins?.length ?? 0
        },
        errorContext: `Failed to execute structured query on "${table}"`,
        handler: async () => {
          const { target, adapter } = resolveReadOnlyAdapter(db_target);
          const result = await adapter.executeStructuredQuery(
            {
              dbTarget: target,
              table,
              columns,
              where: where as Parameters<DatabaseReadOnlyAdapter["executeStructuredQuery"]>[0]["where"],
              joins: joins as Parameters<DatabaseReadOnlyAdapter["executeStructuredQuery"]>[0]["joins"],
              orderBy: order_by as Parameters<DatabaseReadOnlyAdapter["executeStructuredQuery"]>[0]["orderBy"],
              limit: limit ?? 20,
              offset
            },
            `vee:${target}`
          );
          const payload = { db_target: target, ...result };
          return {
            content: [{ type: "text", text: JSON.stringify(payload, null, 2) }],
            structuredContent: payload
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
        "Accepts payload aliases (data, values, set, payload.data/values/set). " +
        "Reason is mandatory in format \"context: detailed justification\". " +
        "UPDATE without WHERE is rejected. DELETE / DROP / TRUNCATE are never allowed.",
      inputSchema: {
        db_target: dbTargetSchema,
        operation: z.enum(["INSERT", "UPDATE", "UPSERT"]).describe("Write operation type"),
        table: z.string().min(1).describe("Target table name (must be in allowed list)"),
        data: z
          .record(z.string(), z.unknown())
          .optional()
          .describe("Column-value pairs to write (preferred payload field)"),
        values: z
          .record(z.string(), z.unknown())
          .optional()
          .describe("Legacy alias for data (accepted for compatibility)"),
        set: z
          .record(z.string(), z.unknown())
          .optional()
          .describe("Legacy alias for data (accepted for compatibility)"),
        payload: z
          .record(z.string(), z.unknown())
          .optional()
          .describe("Optional envelope; accepts payload.data / payload.values / payload.set"),
        where: z
          .array(whereConditionSchema)
          .optional()
          .describe("WHERE conditions (required for UPDATE)"),
        reason: z
          .string()
          .min(1)
          .describe("Mandatory justification in format \"context: detailed reason\""),
        upsert_key: z
          .array(z.string())
          .optional()
          .describe("Unique-key columns for UPSERT (excluded from ON DUPLICATE KEY UPDATE)"),
        approval_id: z
          .string()
          .optional()
          .describe(
            "For protected tables: provide the approval_id returned from the first call once it is approved"
          ),
        actor: z
          .string()
          .optional()
          .describe("Who initiated this write (default: Vee). Used for timeline/audit attribution."),
        trace_id: z
          .string()
          .optional()
          .describe("Optional correlation ID to link write, decision, event, and audit."),
        project_id: z.number().int().optional().describe("Optional project linkage for timeline."),
        session_id: z.string().optional().describe("Optional session linkage for timeline."),
        incident_id: z.string().optional().describe("Optional incident linkage for timeline."),
        decision_id: z.string().optional().describe("Optional linked decision_id for traceability.")
      }
    },
    async ({
      db_target,
      operation,
      table,
      data,
      values,
      set,
      payload,
      where,
      reason,
      upsert_key,
      approval_id,
      actor,
      trace_id,
      project_id,
      session_id,
      incident_id,
      decision_id
    }) =>
      runAuditedTool({
        toolName: "vee_db_write",
        permission: "safe_execute",
        isWrite: true,
        args: {
          db_target: db_target ?? DB_DEFAULT_TARGET,
          operation,
          table,
          actor: normalizeActorName(actor),
          trace_id: trace_id ?? null,
          reason_preview: reason.slice(0, 160),
          payload_shapes: {
            has_data: !!data,
            has_values: !!values,
            has_set: !!set,
            has_payload: !!payload
          }
        },
        errorContext: `Failed to execute structured write on "${table}"`,
        handler: async () => {
          const requestedTarget = db_target ?? DB_DEFAULT_TARGET;
          const { data: writeData, source: payloadSource } = extractWriteDataPayload({
            data,
            values,
            set,
            payload
          });
          const normalizedReason = normalizeWriteReason(reason);
          const normalizedActor = normalizeActorName(actor);
          const normalizedTraceId = normalizeTraceId(trace_id);
          const policy = resolveTablePolicy(table, requestedTarget);
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
                summary: `${operation} on ${table}: ${normalizedReason}`,
                request_payload: {
                  db_target: requestedTarget,
                  operation,
                  table,
                  data: writeData,
                  payload_source: payloadSource,
                  where,
                  reason: normalizedReason,
                  upsert_key,
                  actor: normalizedActor,
                  trace_id: normalizedTraceId,
                  project_id: project_id ?? null,
                  session_id: session_id ?? null,
                  incident_id: incident_id ?? null,
                  decision_id: decision_id ?? null
                },
                meta: {
                  source: "vee-mcp-server",
                  requested_at: new Date().toISOString(),
                  actor: normalizedActor,
                  trace_id: normalizedTraceId
                }
              });
              return buildApprovalCreationResult({
                approvalId: approval.approval_id,
                status: approval.status,
                message: `Write to protected table "${table}" requires approval. Approval created — share the approval_id with the approver.`,
                extra: {
                  db_target: requestedTarget,
                  table,
                  operation,
                  reason: normalizedReason,
                  actor: normalizedActor,
                  trace_id: normalizedTraceId
                }
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

            const savedTarget = normalizeDbTarget(saved["db_target"]) ?? DB_DEFAULT_TARGET;
            if (db_target && db_target !== savedTarget) {
              throw new Error(
                `Approval ${approval_id} is for db_target "${savedTarget}", but received "${db_target}".`
              );
            }

            const { adapter: approvedWriteAdapter } = resolveWriteAdapter(savedTarget);
            const savedData = (saved["data"] as Record<string, unknown> | undefined) ?? {};
            const savedWhere = saved["where"] as WhereCondition[] | undefined;
            const savedActor = normalizeActorName(
              typeof saved["actor"] === "string" ? saved["actor"] : normalizedActor
            );
            const savedTraceId = normalizeTraceId(
              typeof saved["trace_id"] === "string" ? saved["trace_id"] : normalizedTraceId
            );
            const writeResult = await approvedWriteAdapter.executeStructuredWrite({
              dbTarget: savedTarget,
              operation: saved["operation"] as "INSERT" | "UPDATE" | "UPSERT",
              table: saved["table"] as string,
              data: savedData,
              where: savedWhere,
              reason: saved["reason"] as string,
              upsertKey: saved["upsert_key"] as string[] | undefined
            });

            const timelineEntityId = resolveTimelineEntityId({
              where: savedWhere,
              data: savedData,
              before: writeResult.before,
              after: writeResult.after
            });
            const timelineProjectId = resolveTimelineProjectId({
              project_id: coercePositiveInt(saved["project_id"]),
              data: savedData,
              before: writeResult.before,
              after: writeResult.after
            });
            let timelineEventId: string | null = null;
            if (timelineEntityId !== null) {
              const timelineEvent = await approvedWriteAdapter.recordProjectEvent({
                entityType: inferEntityTypeFromTable(table),
                entityId: timelineEntityId,
                actor: savedActor,
                action: `db_write_${operation.toLowerCase()}`,
                payload: {
                  trace_id: savedTraceId,
                  table,
                  operation,
                  write_mode: policy.writeMode,
                  affected_rows: writeResult.affected_rows,
                  reason: writeResult.reason,
                  decision_id: saved["decision_id"] ?? null,
                  before: writeResult.before,
                  after: writeResult.after
                },
                context: {
                  source: "vee_db_write",
                  source_agent: savedActor,
                  db_target: savedTarget,
                  approval_id,
                  project_id: timelineProjectId,
                  session_id: saved["session_id"] ?? null,
                  incident_id: saved["incident_id"] ?? null,
                  decision_id: saved["decision_id"] ?? null
                }
              });
              timelineEventId = (timelineEvent.after?.event_id as string | undefined) ?? null;
            }

            await veeControlAdapter.markApprovalExecuted(approval_id, {
              db_target: savedTarget,
              table,
              operation,
              affected_rows: writeResult.affected_rows,
              trace_id: savedTraceId
            });

            const resultPayload = {
              approval_id,
              db_target: savedTarget,
              write_mode: policy.writeMode,
              payload_source: saved["payload_source"] ?? "approval_payload.data",
              actor: savedActor,
              trace_id: savedTraceId,
              timeline_event_id: timelineEventId,
              ...writeResult,
              ok: true
            };
            return {
              content: [{ type: "text", text: JSON.stringify(resultPayload, null, 2) }],
              structuredContent: resultPayload
            };
          }

          // ── safe_execute: run directly ────────────────────────────────────
          const { target, adapter } = resolveWriteAdapter(db_target);
          const safeWhere = where as WhereCondition[] | undefined;
          const writeResult = await adapter.executeStructuredWrite({
            dbTarget: target,
            operation,
            table,
            data: writeData,
            where: safeWhere,
            reason: normalizedReason,
            upsertKey: upsert_key
          });
          const timelineEntityId = resolveTimelineEntityId({
            where: safeWhere,
            data: writeData,
            before: writeResult.before,
            after: writeResult.after
          });
          const timelineProjectId = resolveTimelineProjectId({
            project_id: project_id ?? null,
            data: writeData,
            before: writeResult.before,
            after: writeResult.after
          });
          let timelineEventId: string | null = null;
          if (timelineEntityId !== null) {
            const timelineEvent = await adapter.recordProjectEvent({
              entityType: inferEntityTypeFromTable(table),
              entityId: timelineEntityId,
              actor: normalizedActor,
              action: `db_write_${operation.toLowerCase()}`,
              payload: {
                trace_id: normalizedTraceId,
                table,
                operation,
                write_mode: policy.writeMode,
                affected_rows: writeResult.affected_rows,
                reason: writeResult.reason,
                decision_id: decision_id ?? null,
                before: writeResult.before,
                after: writeResult.after
              },
              context: {
                source: "vee_db_write",
                source_agent: normalizedActor,
                db_target: target,
                project_id: timelineProjectId,
                session_id: session_id ?? null,
                incident_id: incident_id ?? null,
                decision_id: decision_id ?? null
              }
            });
            timelineEventId = (timelineEvent.after?.event_id as string | undefined) ?? null;
          }

          const resultPayload = {
            db_target: target,
            write_mode: policy.writeMode,
            payload_source: payloadSource,
            actor: normalizedActor,
            trace_id: normalizedTraceId,
            timeline_event_id: timelineEventId,
            ...writeResult
          };
          return {
            content: [{ type: "text", text: JSON.stringify(resultPayload, null, 2) }],
            structuredContent: resultPayload
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
        db_target: dbTargetSchema,
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
          .optional()
          .describe("Who triggered this event (default: Vee)"),
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
          .describe("Surrounding context (e.g. { session_id, tool_name })"),
        trace_id: z
          .string()
          .optional()
          .describe("Optional correlation ID to link event, decision, and db writes"),
        project_id: z.number().int().optional().describe("Optional project linkage"),
        session_id: z.string().optional().describe("Optional session linkage"),
        incident_id: z.string().optional().describe("Optional incident linkage"),
        decision_id: z.string().optional().describe("Optional linked decision ID"),
        source_tool: z.string().optional().describe("Optional source tool name"),
        source_agent: z.string().optional().describe("Optional source agent name")
      }
    },
    async ({
      db_target,
      entity_type,
      entity_id,
      actor,
      action,
      payload,
      context,
      trace_id,
      project_id,
      session_id,
      incident_id,
      decision_id,
      source_tool,
      source_agent
    }) =>
      runAuditedTool({
        toolName: "vee_db_record_event",
        permission: "safe_execute",
        isWrite: true,
        args: {
          db_target: db_target ?? DB_DEFAULT_TARGET,
          entity_type,
          entity_id,
          actor: normalizeActorName(actor),
          action,
          trace_id: trace_id ?? null
        },
        errorContext: "Failed to record project event",
        handler: async () => {
          const normalizedActor = normalizeActorName(actor);
          const normalizedTraceId = normalizeTraceId(trace_id);
          const normalizedEntityType = normalizeEntityType(entity_type);
          const normalizedAction = action.trim();
          if (!normalizedAction) {
            throw new Error("action is required.");
          }

          const eventPayload: Record<string, unknown> = {
            ...(payload ?? {})
          };
          if (eventPayload["trace_id"] === undefined) {
            eventPayload["trace_id"] = normalizedTraceId;
          }
          if (decision_id && eventPayload["decision_id"] === undefined) {
            eventPayload["decision_id"] = decision_id;
          }

          const eventContext: Record<string, unknown> = {
            ...(context ?? {})
          };
          if (eventContext["trace_id"] === undefined) {
            eventContext["trace_id"] = normalizedTraceId;
          }
          if (eventContext["project_id"] === undefined && project_id !== undefined) {
            eventContext["project_id"] = project_id;
          }
          if (eventContext["session_id"] === undefined && session_id !== undefined) {
            eventContext["session_id"] = session_id;
          }
          if (eventContext["incident_id"] === undefined && incident_id !== undefined) {
            eventContext["incident_id"] = incident_id;
          }
          if (eventContext["decision_id"] === undefined && decision_id !== undefined) {
            eventContext["decision_id"] = decision_id;
          }
          if (eventContext["source_tool"] === undefined) {
            eventContext["source_tool"] = source_tool ?? "vee_db_record_event";
          }
          if (eventContext["source_agent"] === undefined) {
            eventContext["source_agent"] = source_agent ?? normalizedActor;
          }

          const { target, adapter } = resolveWriteAdapter(db_target);
          const result = await adapter.recordProjectEvent({
            entityType: normalizedEntityType,
            entityId: entity_id,
            actor: normalizedActor,
            action: normalizedAction,
            payload: eventPayload,
            context: eventContext
          });
          const payloadResult = {
            db_target: target,
            actor: normalizedActor,
            trace_id: normalizedTraceId,
            ...result
          };
          return {
            content: [{ type: "text", text: JSON.stringify(payloadResult, null, 2) }],
            structuredContent: payloadResult
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
        db_target: dbTargetSchema,
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
          .optional()
          .describe("Who made this decision (default: Vee)"),
        project_id: z
          .number()
          .int()
          .optional()
          .describe("Optional: associate with a project ID"),
        entity_type: z
          .string()
          .optional()
          .describe("Optional entity type to attach a timeline event (default: project/decision)"),
        entity_id: z.number().int().optional().describe("Optional entity id to attach timeline event"),
        trace_id: z
          .string()
          .optional()
          .describe("Optional correlation ID to link decision, event, and db writes"),
        session_id: z.string().optional().describe("Optional session linkage"),
        incident_id: z.string().optional().describe("Optional incident linkage"),
        record_event: z
          .boolean()
          .optional()
          .describe("If true (default), also appends a timeline event linked to this decision")
      }
    },
    async ({
      db_target,
      title,
      context,
      rationale,
      outcome,
      actor,
      project_id,
      entity_type,
      entity_id,
      trace_id,
      session_id,
      incident_id,
      record_event
    }) =>
      runAuditedTool({
        toolName: "vee_db_record_decision",
        permission: "safe_execute",
        isWrite: true,
        args: {
          db_target: db_target ?? DB_DEFAULT_TARGET,
          title,
          actor: normalizeActorName(actor),
          project_id,
          trace_id: trace_id ?? null
        },
        errorContext: "Failed to record operational decision",
        handler: async () => {
          const normalizedActor = normalizeActorName(actor);
          const normalizedTraceId = normalizeTraceId(trace_id);
          const { target, adapter } = resolveWriteAdapter(db_target);
          const result = await adapter.recordOperationalDecision({
            title,
            context,
            rationale,
            outcome,
            actor: normalizedActor,
            projectId: project_id ?? null
          });
          const decisionId = firstDefinedString(result.after?.decision_id) ?? null;
          const decisionPk = firstDefinedNumber(result.after?.id);
          const linkedEntityType = entity_type
            ? normalizeEntityType(entity_type)
            : project_id
            ? "project"
            : "decision";
          const linkedEntityId = entity_id ?? project_id ?? decisionPk;
          let timelineEventId: string | null = null;
          if ((record_event ?? true) && linkedEntityId !== null) {
            const timelineEvent = await adapter.recordProjectEvent({
              entityType: linkedEntityType,
              entityId: linkedEntityId,
              actor: normalizedActor,
              action: "operational_decision_recorded",
              payload: {
                trace_id: normalizedTraceId,
                decision_id: decisionId,
                title,
                rationale,
                outcome
              },
              context: {
                source: "vee_db_record_decision",
                source_agent: normalizedActor,
                db_target: target,
                project_id: project_id ?? null,
                session_id: session_id ?? null,
                incident_id: incident_id ?? null,
                decision_id: decisionId
              }
            });
            timelineEventId = (timelineEvent.after?.event_id as string | undefined) ?? null;
          }

          const payloadResult = {
            db_target: target,
            actor: normalizedActor,
            trace_id: normalizedTraceId,
            timeline_event_id: timelineEventId,
            ...result
          };
          return {
            content: [{ type: "text", text: JSON.stringify(payloadResult, null, 2) }],
            structuredContent: payloadResult
          };
        }
      })
  );

  server.registerTool(
    "vee_db_record_block",
    {
      title: "DB - Record Entity Block",
      description:
        "Creates a block entry in vee_blocks and appends a correlated timeline event for traceability.",
      inputSchema: {
        db_target: dbTargetSchema,
        entity_type: z.string().min(1).describe("Entity type being blocked (e.g. project, task, incident)"),
        entity_id: z.number().int().min(1).describe("Entity ID being blocked"),
        reason: z.string().min(3).describe("Why this entity is blocked"),
        blocked_by: z.string().optional().describe("Who blocked it (default: Vee)"),
        trace_id: z.string().optional().describe("Optional correlation ID"),
        project_id: z.number().int().optional().describe("Optional project linkage"),
        session_id: z.string().optional().describe("Optional session linkage"),
        incident_id: z.string().optional().describe("Optional incident linkage"),
        decision_id: z.string().optional().describe("Optional linked decision ID")
      }
    },
    async ({
      db_target,
      entity_type,
      entity_id,
      reason,
      blocked_by,
      trace_id,
      project_id,
      session_id,
      incident_id,
      decision_id
    }) =>
      runAuditedTool({
        toolName: "vee_db_record_block",
        permission: "safe_execute",
        isWrite: true,
        args: {
          db_target: db_target ?? DB_DEFAULT_TARGET,
          entity_type,
          entity_id,
          blocked_by: normalizeActorName(blocked_by),
          trace_id: trace_id ?? null
        },
        errorContext: "Failed to record entity block",
        handler: async () => {
          const normalizedActor = normalizeActorName(blocked_by);
          const normalizedTraceId = normalizeTraceId(trace_id);
          const normalizedEntityType = normalizeEntityType(entity_type);
          const { target, adapter } = resolveWriteAdapter(db_target);

          const blockResult = await adapter.recordEntityBlock({
            entityType: normalizedEntityType,
            entityId: entity_id,
            reason: reason.trim(),
            blockedBy: normalizedActor
          });

          const timelineEvent = await adapter.recordProjectEvent({
            entityType: normalizedEntityType,
            entityId: entity_id,
            actor: normalizedActor,
            action: "blocked",
            payload: {
              trace_id: normalizedTraceId,
              reason: reason.trim(),
              decision_id: decision_id ?? null,
              block_id: blockResult.after?.id ?? null
            },
            context: {
              source: "vee_db_record_block",
              source_agent: normalizedActor,
              db_target: target,
              project_id: project_id ?? null,
              session_id: session_id ?? null,
              incident_id: incident_id ?? null,
              decision_id: decision_id ?? null
            }
          });

          const payloadResult = {
            db_target: target,
            actor: normalizedActor,
            trace_id: normalizedTraceId,
            timeline_event_id: (timelineEvent.after?.event_id as string | undefined) ?? null,
            ...blockResult
          };
          return {
            content: [{ type: "text", text: JSON.stringify(payloadResult, null, 2) }],
            structuredContent: payloadResult
          };
        }
      })
  );

  server.registerTool(
    "vee_db_unblock_entity",
    {
      title: "DB - Unblock Entity",
      description:
        "Marks active blocks as unblocked in vee_blocks and appends a correlated unblocked event.",
      inputSchema: {
        db_target: dbTargetSchema,
        entity_type: z.string().min(1).describe("Entity type being unblocked"),
        entity_id: z.number().int().min(1).describe("Entity ID being unblocked"),
        unblock_reason: z.string().min(3).describe("Reason for unblocking"),
        unblocked_by: z.string().optional().describe("Who unblocked (default: Vee)"),
        trace_id: z.string().optional().describe("Optional correlation ID"),
        project_id: z.number().int().optional().describe("Optional project linkage"),
        session_id: z.string().optional().describe("Optional session linkage"),
        incident_id: z.string().optional().describe("Optional incident linkage"),
        decision_id: z.string().optional().describe("Optional linked decision ID")
      }
    },
    async ({
      db_target,
      entity_type,
      entity_id,
      unblock_reason,
      unblocked_by,
      trace_id,
      project_id,
      session_id,
      incident_id,
      decision_id
    }) =>
      runAuditedTool({
        toolName: "vee_db_unblock_entity",
        permission: "safe_execute",
        isWrite: true,
        args: {
          db_target: db_target ?? DB_DEFAULT_TARGET,
          entity_type,
          entity_id,
          unblocked_by: normalizeActorName(unblocked_by),
          trace_id: trace_id ?? null
        },
        errorContext: "Failed to unblock entity",
        handler: async () => {
          const normalizedActor = normalizeActorName(unblocked_by);
          const normalizedTraceId = normalizeTraceId(trace_id);
          const normalizedEntityType = normalizeEntityType(entity_type);
          const { target, adapter } = resolveWriteAdapter(db_target);

          const unblockResult = await adapter.unblockEntity({
            entityType: normalizedEntityType,
            entityId: entity_id,
            unblockReason: unblock_reason.trim(),
            unblockedBy: normalizedActor
          });

          let timelineEventId: string | null = null;
          if (unblockResult.affected_rows > 0) {
            const timelineEvent = await adapter.recordProjectEvent({
              entityType: normalizedEntityType,
              entityId: entity_id,
              actor: normalizedActor,
              action: "unblocked",
              payload: {
                trace_id: normalizedTraceId,
                reason: unblock_reason.trim(),
                decision_id: decision_id ?? null
              },
              context: {
                source: "vee_db_unblock_entity",
                source_agent: normalizedActor,
                db_target: target,
                project_id: project_id ?? null,
                session_id: session_id ?? null,
                incident_id: incident_id ?? null,
                decision_id: decision_id ?? null
              }
            });
            timelineEventId = (timelineEvent.after?.event_id as string | undefined) ?? null;
          }

          const payloadResult = {
            db_target: target,
            actor: normalizedActor,
            trace_id: normalizedTraceId,
            timeline_event_id: timelineEventId,
            ...unblockResult
          };
          return {
            content: [{ type: "text", text: JSON.stringify(payloadResult, null, 2) }],
            structuredContent: payloadResult
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
        db_target: dbTargetSchema,
        entity_type: z
          .string()
          .optional()
          .describe("Filter by entity type (e.g. project, task, appointment)"),
        entity_id: z.number().int().optional().describe("Filter by entity ID"),
        actor: z.string().optional().describe("Filter by actor name"),
        action: z.string().optional().describe("Filter by action (exact match)"),
        project_id: z.number().int().optional().describe("Filter by context.project_id"),
        session_id: z.string().optional().describe("Filter by context.session_id"),
        incident_id: z.string().optional().describe("Filter by context.incident_id"),
        decision_id: z.string().optional().describe("Filter by payload/context decision_id"),
        trace_id: z.string().optional().describe("Filter by payload/context trace_id"),
        from_timestamp: z.string().optional().describe("Optional lower timestamp bound (ISO date/time)"),
        to_timestamp: z.string().optional().describe("Optional upper timestamp bound (ISO date/time)"),
        limit: z
          .number()
          .int()
          .min(1)
          .max(200)
          .optional()
          .describe("Max events to return (default 50)")
      }
    },
    async ({
      db_target,
      entity_type,
      entity_id,
      actor,
      action,
      project_id,
      session_id,
      incident_id,
      decision_id,
      trace_id,
      from_timestamp,
      to_timestamp,
      limit
    }) =>
      runAuditedTool({
        toolName: "vee_db_get_timeline",
        permission: "read_only",
        isWrite: false,
        args: {
          db_target: db_target ?? DB_DEFAULT_TARGET,
          entity_type,
          entity_id,
          actor,
          action,
          project_id,
          session_id,
          incident_id,
          decision_id,
          trace_id,
          limit
        },
        errorContext: "Failed to get entity timeline",
        handler: async () => {
          const where: WhereCondition[] = [];
          const normalizedEntityType = entity_type ? normalizeEntityType(entity_type) : null;
          if (normalizedEntityType) {
            where.push({ column: "entity_type", operator: "=", value: normalizedEntityType });
          }
          if (entity_id) where.push({ column: "entity_id", operator: "=", value: entity_id });
          if (actor) where.push({ column: "actor", operator: "=", value: actor });
          if (action) where.push({ column: "action", operator: "=", value: action });

          const traceFilter = trace_id ? trace_id.trim() : null;
          if (traceFilter && !/^[a-zA-Z0-9._:-]{8,120}$/.test(traceFilter)) {
            throw new Error("trace_id format is invalid. Use 8-120 chars: letters, numbers, . _ : -");
          }

          const { target, adapter } = resolveReadOnlyAdapter(db_target);
          const result = await adapter.executeStructuredQuery(
            {
              dbTarget: target,
              table: "vee_project_events",
              where,
              orderBy: [{ column: "created_at", direction: "DESC" }],
              limit: Math.min(200, (limit ?? 50) * 3)
            },
            `vee:${target}`
          );

          const fromMs = timestampToMs(normalizeTimelineTimestamp(from_timestamp) ?? from_timestamp ?? null);
          const toMs = timestampToMs(normalizeTimelineTimestamp(to_timestamp) ?? to_timestamp ?? null);
          if (from_timestamp && fromMs === null) {
            throw new Error("from_timestamp must be a valid ISO date/time.");
          }
          if (to_timestamp && toMs === null) {
            throw new Error("to_timestamp must be a valid ISO date/time.");
          }

          const filteredRows = result.rows.filter((row) => {
            const eventProjectId = extractCorrelationNumber(row, "project_id");
            const eventSessionId = extractCorrelationString(row, "session_id");
            const eventIncidentId = extractCorrelationString(row, "incident_id");
            const eventDecisionId = extractCorrelationString(row, "decision_id");
            const eventTraceId = extractCorrelationString(row, "trace_id");
            const eventTimestamp = timestampToMs(normalizeTimelineTimestamp(row["created_at"]));

            if (project_id !== undefined && eventProjectId !== project_id) return false;
            if (session_id && eventSessionId !== session_id) return false;
            if (incident_id && eventIncidentId !== incident_id) return false;
            if (decision_id && eventDecisionId !== decision_id) return false;
            if (traceFilter && eventTraceId !== traceFilter) return false;
            if (fromMs !== null && eventTimestamp !== null && eventTimestamp < fromMs) return false;
            if (toMs !== null && eventTimestamp !== null && eventTimestamp > toMs) return false;
            return true;
          });

          const selectedRows = filteredRows.slice(0, limit ?? 50);
          const payloadResult = {
            db_target: target,
            table: result.table,
            resolved_table: result.resolved_table,
            total: selectedRows.length,
            returned_rows: selectedRows.length,
            rows: selectedRows,
            views: {
              available_named_views: ["timeline_events"],
              filters_applied: {
                entity_type: normalizedEntityType,
                entity_id: entity_id ?? null,
                actor: actor ?? null,
                action: action ?? null,
                project_id: project_id ?? null,
                session_id: session_id ?? null,
                incident_id: incident_id ?? null,
                decision_id: decision_id ?? null,
                trace_id: traceFilter
              }
            },
            performance: result.performance,
            pagination: {
              limit: limit ?? 50,
              source_rows: result.rows.length,
              filtered_rows: filteredRows.length,
              returned_rows: selectedRows.length
            }
          };
          return {
            content: [{ type: "text", text: JSON.stringify(payloadResult, null, 2) }],
            structuredContent: payloadResult
          };
        }
      })
  );

  server.registerTool(
    "vee_db_get_operational_timeline",
    {
      title: "DB - Get Operational Timeline",
      description:
        "Returns a unified timeline merging events, decisions, blocks, status changes, and agent actions.",
      inputSchema: {
        db_target: dbTargetSchema,
        entity_type: z.string().optional().describe("Optional entity type filter"),
        entity_id: z.number().int().optional().describe("Optional entity ID filter"),
        project_id: z.number().int().optional().describe("Optional project filter"),
        actor: z.string().optional().describe("Optional actor filter"),
        action: z.string().optional().describe("Optional action filter"),
        session_id: z.string().optional().describe("Optional session filter"),
        incident_id: z.string().optional().describe("Optional incident filter"),
        decision_id: z.string().optional().describe("Optional decision filter"),
        trace_id: z.string().optional().describe("Optional correlation ID filter"),
        from_timestamp: z.string().optional().describe("Optional lower timestamp bound (ISO date/time)"),
        to_timestamp: z.string().optional().describe("Optional upper timestamp bound (ISO date/time)"),
        include_agent_actions: z
          .boolean()
          .optional()
          .describe("Include vee_mcp_calls as agent_action items (default true)"),
        limit: z.number().int().min(1).max(200).optional().describe("Max items returned (default 100)")
      }
    },
    async ({
      db_target,
      entity_type,
      entity_id,
      project_id,
      actor,
      action,
      session_id,
      incident_id,
      decision_id,
      trace_id,
      from_timestamp,
      to_timestamp,
      include_agent_actions,
      limit
    }) =>
      runAuditedTool({
        toolName: "vee_db_get_operational_timeline",
        permission: "read_only",
        isWrite: false,
        args: {
          db_target: db_target ?? DB_DEFAULT_TARGET,
          entity_type,
          entity_id,
          project_id,
          actor,
          action,
          session_id,
          incident_id,
          decision_id,
          trace_id,
          include_agent_actions,
          limit
        },
        errorContext: "Failed to get operational timeline",
        handler: async () => {
          const normalizedEntityType = entity_type ? normalizeEntityType(entity_type) : null;
          const traceFilter = trace_id ? trace_id.trim() : null;
          if (traceFilter && !/^[a-zA-Z0-9._:-]{8,120}$/.test(traceFilter)) {
            throw new Error("trace_id format is invalid. Use 8-120 chars: letters, numbers, . _ : -");
          }

          const fromMs = timestampToMs(normalizeTimelineTimestamp(from_timestamp) ?? from_timestamp ?? null);
          const toMs = timestampToMs(normalizeTimelineTimestamp(to_timestamp) ?? to_timestamp ?? null);
          if (from_timestamp && fromMs === null) {
            throw new Error("from_timestamp must be a valid ISO date/time.");
          }
          if (to_timestamp && toMs === null) {
            throw new Error("to_timestamp must be a valid ISO date/time.");
          }

          const { target, adapter } = resolveReadOnlyAdapter(db_target);
          const effectiveLimit = limit ?? 100;
          const perTableLimit = Math.min(200, Math.max(effectiveLimit, 80));
          const warnings: string[] = [];
          const items: OperationalTimelineItem[] = [];

          const pushItems = (list: OperationalTimelineItem[]) => {
            for (const item of list) {
              items.push(item);
            }
          };

          try {
            const where: WhereCondition[] = [];
            if (normalizedEntityType) where.push({ column: "entity_type", operator: "=", value: normalizedEntityType });
            if (entity_id !== undefined) where.push({ column: "entity_id", operator: "=", value: entity_id });
            if (actor) where.push({ column: "actor", operator: "=", value: actor });
            if (action) where.push({ column: "action", operator: "=", value: action });
            if (from_timestamp) where.push({ column: "created_at", operator: ">=", value: from_timestamp });
            if (to_timestamp) where.push({ column: "created_at", operator: "<=", value: to_timestamp });

            const events = await adapter.executeStructuredQuery(
              {
                dbTarget: target,
                table: "timeline_events",
                where,
                orderBy: [{ column: "created_at", direction: "DESC" }],
                limit: perTableLimit
              },
              `vee:${target}:timeline_events`
            );

            pushItems(
              events.rows.flatMap((row) => {
                const timestamp = normalizeTimelineTimestamp(row["created_at"]);
                if (!timestamp) return [];
                const entityTypeValue = firstDefinedString(row["entity_type"]) ?? "entity";
                const entityIdValue = firstDefinedNumber(row["entity_id"], row["id"]);
                return [
                  {
                    kind: "event",
                    timestamp,
                    actor: normalizeActorName(firstDefinedString(row["actor"]) ?? undefined),
                    action: firstDefinedString(row["action"]) ?? "event",
                    entity_type: entityTypeValue,
                    entity_id: entityIdValue,
                    project_id:
                      extractCorrelationNumber(row, "project_id") ??
                      (entityTypeValue === "project" ? entityIdValue : null),
                    trace_id: extractCorrelationString(row, "trace_id"),
                    source_table: "vee_project_events",
                    raw: row
                  }
                ];
              })
            );
          } catch (error) {
            warnings.push(`timeline_events unavailable: ${error instanceof Error ? error.message : String(error)}`);
          }

          try {
            const where: WhereCondition[] = [];
            if (project_id !== undefined) where.push({ column: "project_id", operator: "=", value: project_id });
            if (actor) where.push({ column: "actor", operator: "=", value: actor });
            if (from_timestamp) where.push({ column: "created_at", operator: ">=", value: from_timestamp });
            if (to_timestamp) where.push({ column: "created_at", operator: "<=", value: to_timestamp });

            const decisions = await adapter.executeStructuredQuery(
              {
                dbTarget: target,
                table: "timeline_decisions",
                where,
                orderBy: [{ column: "created_at", direction: "DESC" }],
                limit: perTableLimit
              },
              `vee:${target}:timeline_decisions`
            );

            pushItems(
              decisions.rows.flatMap((row) => {
                const timestamp = normalizeTimelineTimestamp(row["created_at"]);
                if (!timestamp) return [];
                return [
                  {
                    kind: "decision",
                    timestamp,
                    actor: normalizeActorName(firstDefinedString(row["actor"]) ?? undefined),
                    action: "operational_decision_recorded",
                    entity_type: "decision",
                    entity_id: firstDefinedNumber(row["id"], row["entity_id"]),
                    project_id: firstDefinedNumber(row["project_id"]),
                    trace_id: extractCorrelationString(row, "trace_id"),
                    source_table: "vee_operational_decisions",
                    raw: row
                  }
                ];
              })
            );
          } catch (error) {
            warnings.push(`timeline_decisions unavailable: ${error instanceof Error ? error.message : String(error)}`);
          }

          try {
            const where: WhereCondition[] = [];
            if (normalizedEntityType) where.push({ column: "entity_type", operator: "=", value: normalizedEntityType });
            if (entity_id !== undefined) where.push({ column: "entity_id", operator: "=", value: entity_id });
            if (actor) where.push({ column: "blocked_by", operator: "=", value: actor });

            const blocks = await adapter.executeStructuredQuery(
              {
                dbTarget: target,
                table: "timeline_blocks",
                where,
                orderBy: [{ column: "blocked_at", direction: "DESC" }],
                limit: perTableLimit
              },
              `vee:${target}:timeline_blocks`
            );

            pushItems(
              blocks.rows.flatMap((row) => {
                const entityTypeValue = firstDefinedString(row["entity_type"]) ?? "entity";
                const entityIdValue = firstDefinedNumber(row["entity_id"], row["id"]);
                const projectValue = extractCorrelationNumber(row, "project_id");
                const traceValue = extractCorrelationString(row, "trace_id");
                const blockedAt = normalizeTimelineTimestamp(row["blocked_at"] ?? row["created_at"]);
                const unblockedAt = normalizeTimelineTimestamp(row["unblocked_at"]);
                const output: OperationalTimelineItem[] = [];

                if (blockedAt) {
                  output.push({
                    kind: "block",
                    timestamp: blockedAt,
                    actor: normalizeActorName(firstDefinedString(row["blocked_by"]) ?? undefined),
                    action: "blocked",
                    entity_type: entityTypeValue,
                    entity_id: entityIdValue,
                    project_id: projectValue,
                    trace_id: traceValue,
                    source_table: "vee_blocks",
                    raw: row
                  });
                }

                if (unblockedAt) {
                  output.push({
                    kind: "block",
                    timestamp: unblockedAt,
                    actor: normalizeActorName(
                      firstDefinedString(row["unblocked_by"], row["blocked_by"]) ?? undefined
                    ),
                    action: "unblocked",
                    entity_type: entityTypeValue,
                    entity_id: entityIdValue,
                    project_id: projectValue,
                    trace_id: traceValue,
                    source_table: "vee_blocks",
                    raw: row
                  });
                }

                return output;
              })
            );
          } catch (error) {
            warnings.push(`timeline_blocks unavailable: ${error instanceof Error ? error.message : String(error)}`);
          }

          try {
            const where: WhereCondition[] = [];
            if (normalizedEntityType) where.push({ column: "entity_type", operator: "=", value: normalizedEntityType });
            if (entity_id !== undefined) where.push({ column: "entity_id", operator: "=", value: entity_id });
            if (actor) where.push({ column: "changed_by", operator: "=", value: actor });
            if (from_timestamp) where.push({ column: "created_at", operator: ">=", value: from_timestamp });
            if (to_timestamp) where.push({ column: "created_at", operator: "<=", value: to_timestamp });

            const statusRows = await adapter.executeStructuredQuery(
              {
                dbTarget: target,
                table: "timeline_status_changes",
                where,
                orderBy: [{ column: "created_at", direction: "DESC" }],
                limit: perTableLimit
              },
              `vee:${target}:timeline_status_changes`
            );

            pushItems(
              statusRows.rows.flatMap((row) => {
                const timestamp = normalizeTimelineTimestamp(row["created_at"]);
                if (!timestamp) return [];
                const oldStatus = firstDefinedString(row["old_status"]) ?? "unknown";
                const newStatus = firstDefinedString(row["new_status"]) ?? "unknown";
                return [
                  {
                    kind: "status_change",
                    timestamp,
                    actor: normalizeActorName(firstDefinedString(row["changed_by"]) ?? undefined),
                    action: `status_changed:${oldStatus}->${newStatus}`,
                    entity_type: firstDefinedString(row["entity_type"]) ?? "entity",
                    entity_id: firstDefinedNumber(row["entity_id"], row["id"]),
                    project_id: extractCorrelationNumber(row, "project_id"),
                    trace_id: extractCorrelationString(row, "trace_id"),
                    source_table: "vee_status_history",
                    raw: row
                  }
                ];
              })
            );
          } catch (error) {
            warnings.push(`timeline_status_changes unavailable: ${error instanceof Error ? error.message : String(error)}`);
          }

          if (include_agent_actions ?? true) {
            try {
              const where: WhereCondition[] = [];
              if (action) where.push({ column: "tool_name", operator: "=", value: action });
              if (from_timestamp) where.push({ column: "created_at", operator: ">=", value: from_timestamp });
              if (to_timestamp) where.push({ column: "created_at", operator: "<=", value: to_timestamp });

              const calls = await adapter.executeStructuredQuery(
                {
                  dbTarget: target,
                  table: "timeline_agent_actions",
                  where,
                  orderBy: [{ column: "created_at", direction: "DESC" }],
                  limit: perTableLimit
                },
                `vee:${target}:timeline_agent_actions`
              );

              pushItems(
                calls.rows.flatMap((row) => {
                  const timestamp = normalizeTimelineTimestamp(row["created_at"]);
                  if (!timestamp) return [];
                  const requestPayload = parseJsonLike(row["request_payload"]);
                  const responsePayload = parseJsonLike(row["response_payload"]);
                  const actorFromPayload = firstDefinedString(
                    requestPayload?.["actor"],
                    requestPayload?.["source_agent"],
                    responsePayload?.["actor"]
                  );

                  return [
                    {
                      kind: "agent_action",
                      timestamp,
                      actor: normalizeActorName(actorFromPayload ?? undefined),
                      action: firstDefinedString(row["tool_name"]) ?? "agent_action",
                      entity_type: "agent_call",
                      entity_id: firstDefinedNumber(row["id"]),
                      project_id: firstDefinedNumber(
                        requestPayload?.["project_id"],
                        responsePayload?.["project_id"],
                        extractCorrelationNumber(row, "project_id")
                      ),
                      trace_id: firstDefinedString(
                        requestPayload?.["trace_id"],
                        responsePayload?.["trace_id"],
                        extractCorrelationString(row, "trace_id")
                      ),
                      source_table: "vee_mcp_calls",
                      raw: row
                    }
                  ];
                })
              );
            } catch (error) {
              warnings.push(
                `timeline_agent_actions unavailable: ${error instanceof Error ? error.message : String(error)}`
              );
            }
          }

          const filtered = items.filter((item) => {
            if (normalizedEntityType && item.entity_type !== normalizedEntityType) return false;
            if (entity_id !== undefined && item.entity_id !== entity_id) return false;
            if (project_id !== undefined && item.project_id !== project_id) return false;
            if (actor && item.actor.toLowerCase() !== actor.toLowerCase()) return false;
            if (action && item.action.toLowerCase() !== action.toLowerCase()) return false;
            if (traceFilter && item.trace_id !== traceFilter) return false;

            const itemSessionId = extractCorrelationString(item.raw, "session_id");
            const itemIncidentId = extractCorrelationString(item.raw, "incident_id");
            const itemDecisionId = extractCorrelationString(item.raw, "decision_id");
            if (session_id && itemSessionId !== session_id) return false;
            if (incident_id && itemIncidentId !== incident_id) return false;
            if (decision_id && itemDecisionId !== decision_id) return false;

            const itemMs = timestampToMs(item.timestamp);
            if (fromMs !== null && itemMs !== null && itemMs < fromMs) return false;
            if (toMs !== null && itemMs !== null && itemMs > toMs) return false;

            return true;
          });

          filtered.sort((a, b) => {
            const leftMs = timestampToMs(a.timestamp);
            const rightMs = timestampToMs(b.timestamp);
            if (leftMs !== null && rightMs !== null) {
              return rightMs - leftMs;
            }
            return b.timestamp.localeCompare(a.timestamp);
          });

          const selected = filtered.slice(0, effectiveLimit);
          const sourceCounts: Record<string, number> = {};
          for (const item of selected) {
            sourceCounts[item.source_table] = (sourceCounts[item.source_table] ?? 0) + 1;
          }

          const payloadResult = {
            db_target: target,
            total: selected.length,
            rows: selected,
            summary: summarizeTimelineViews(selected),
            source_counts: sourceCounts,
            views: {
              available_named_views: [
                "timeline_events",
                "timeline_decisions",
                "timeline_blocks",
                "timeline_status_changes",
                "timeline_agent_actions"
              ],
              include_agent_actions: include_agent_actions ?? true
            },
            warnings,
            pagination: {
              limit: effectiveLimit,
              merged_rows: items.length,
              filtered_rows: filtered.length,
              returned_rows: selected.length
            }
          };
          return {
            content: [{ type: "text", text: JSON.stringify(payloadResult, null, 2) }],
            structuredContent: payloadResult
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
  const readTargets = getConfiguredReadTargets();
  const writeTargets = getConfiguredWriteTargets();
  const dbMode =
    readTargets.length > 0
      ? `db readonly enabled (${readTargets.join(", ")})`
      : "db readonly disabled";
  const dbWriteMode =
    writeTargets.length > 0
      ? `db write enabled (${writeTargets.join(", ")})`
      : "db write disabled";
  const claudeMode = CLAUDE_MCP_PUBLIC_URL ? "claude endpoint configured" : "claude endpoint auto";
  const fsMode =
    FS_ALLOWED_ROOTS.length > 0
      ? `fs enabled (env: ${FS_ALLOWLIST_ENV}, roots: ${FS_ALLOWED_ROOTS.join(", ")}${FS_WRITE_ENABLED ? ", writes ON" : ""})`
      : `fs disabled (env: ${FS_ALLOWLIST_ENV}, FS_ALLOWED_ROOTS not set)`;

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
  const enabledToolNames = capabilityCatalog
    .filter(tool => tool.status === "enabled")
    .map(tool => tool.name)
    .sort((a, b) => a.localeCompare(b));
  console.log(`[Vee MCP] enabled tools (${enabledToolNames.length}): ${enabledToolNames.join(", ")}`);
});
