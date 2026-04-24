// Table and column access policy for vee_db_* tools.
// Every table/view exposed through generic DB tools must be allowlisted here
// or through DB_ALLOWLIST_EXTRA_* environment variables.

export type DbTarget = "mmcriativos" | "mmcc";
export type WriteMode = "safe_execute" | "approval_required" | "read_only";

export type TablePolicy = {
  table: string;
  allowedColumns: string[] | "*";
  blockedColumns: string[];
  sensitive: boolean;
  writeMode: WriteMode;
};

export type AllowlistSource = "base" | "env";

export type QueryAllowlistEntry = {
  logical_name: string;
  resolved_name: string;
  kind: "table" | "named_view";
  source: AllowlistSource;
  allowed_columns: string[] | "*";
  blocked_columns: string[];
  sensitive: boolean;
  write_mode: WriteMode;
};

export type WritePolicyGroups = {
  safe_execute: string[];
  approval_required: string[];
  read_only: string[];
};

type TargetAllowlist = {
  tablePolicies: Record<string, TablePolicy>;
  namedViews: Record<string, string>;
  tablePolicySources: Map<string, AllowlistSource>;
  namedViewSources: Map<string, AllowlistSource>;
  logicalViewByResolved: Map<string, string>;
};

const DB_TARGETS: readonly DbTarget[] = ["mmcriativos", "mmcc"];
const IDENTIFIER_REGEX = /^[a-z_][a-z0-9_]*$/i;
const WRITE_MODES: WriteMode[] = ["safe_execute", "approval_required", "read_only"];
const DEFAULT_TARGET: DbTarget = "mmcriativos";
const DEFAULT_SENSITIVE_BLOCKED_COLUMNS = [
  "password",
  "remember_token",
  "two_factor_secret",
  "two_factor_recovery_codes",
  "token",
  "access_token",
  "refresh_token",
  "api_key",
  "client_secret",
  "secret",
  "private_key",
  "encryption_key",
  "webhook_secret",
  "phone",
  "email",
  "cpf"
];

const BASE_TABLE_POLICIES_MMCRIATIVOS: Record<string, TablePolicy> = {
  tenants: {
    table: "tenants",
    allowedColumns: ["id", "slug", "name", "status", "plan", "created_at", "updated_at"],
    blockedColumns: [],
    sensitive: false,
    writeMode: "approval_required"
  },
  clients: {
    table: "clients",
    allowedColumns: ["id", "name", "slug", "logo", "website", "sector", "description", "created_at", "updated_at"],
    blockedColumns: [],
    sensitive: false,
    writeMode: "approval_required"
  },
  contacts: {
    table: "contacts",
    allowedColumns: "*",
    blockedColumns: ["phone", "email", "cpf"],
    sensitive: true,
    writeMode: "approval_required"
  },
  users: {
    table: "users",
    allowedColumns: ["id", "name", "role", "is_active", "created_at", "updated_at"],
    blockedColumns: ["password", "remember_token", "two_factor_secret", "two_factor_recovery_codes"],
    sensitive: true,
    writeMode: "approval_required"
  },
  services: {
    table: "services",
    allowedColumns: ["id", "name", "slug", "description", "created_at", "updated_at"],
    blockedColumns: [],
    sensitive: false,
    writeMode: "approval_required"
  },
  messages: {
    table: "messages",
    allowedColumns: "*",
    blockedColumns: [...DEFAULT_SENSITIVE_BLOCKED_COLUMNS],
    sensitive: true,
    writeMode: "approval_required"
  },
  password_resets: {
    table: "password_resets",
    allowedColumns: "*",
    blockedColumns: [...DEFAULT_SENSITIVE_BLOCKED_COLUMNS],
    sensitive: true,
    writeMode: "approval_required"
  },
  personal_access_tokens: {
    table: "personal_access_tokens",
    allowedColumns: "*",
    blockedColumns: [...DEFAULT_SENSITIVE_BLOCKED_COLUMNS],
    sensitive: true,
    writeMode: "approval_required"
  },
  oauth_access_tokens: {
    table: "oauth_access_tokens",
    allowedColumns: "*",
    blockedColumns: [...DEFAULT_SENSITIVE_BLOCKED_COLUMNS],
    sensitive: true,
    writeMode: "approval_required"
  },
  oauth_refresh_tokens: {
    table: "oauth_refresh_tokens",
    allowedColumns: "*",
    blockedColumns: [...DEFAULT_SENSITIVE_BLOCKED_COLUMNS],
    sensitive: true,
    writeMode: "approval_required"
  },
  oauth_clients: {
    table: "oauth_clients",
    allowedColumns: "*",
    blockedColumns: [...DEFAULT_SENSITIVE_BLOCKED_COLUMNS],
    sensitive: true,
    writeMode: "approval_required"
  },
  oauth_auth_codes: {
    table: "oauth_auth_codes",
    allowedColumns: "*",
    blockedColumns: [...DEFAULT_SENSITIVE_BLOCKED_COLUMNS],
    sensitive: true,
    writeMode: "approval_required"
  },
  api_credentials: {
    table: "api_credentials",
    allowedColumns: "*",
    blockedColumns: [...DEFAULT_SENSITIVE_BLOCKED_COLUMNS],
    sensitive: true,
    writeMode: "approval_required"
  },
  integration_credentials: {
    table: "integration_credentials",
    allowedColumns: "*",
    blockedColumns: [...DEFAULT_SENSITIVE_BLOCKED_COLUMNS],
    sensitive: true,
    writeMode: "approval_required"
  },
  integrations: {
    table: "integrations",
    allowedColumns: "*",
    blockedColumns: [...DEFAULT_SENSITIVE_BLOCKED_COLUMNS],
    sensitive: true,
    writeMode: "approval_required"
  },
  billing_subscriptions: {
    table: "billing_subscriptions",
    allowedColumns: "*",
    blockedColumns: [...DEFAULT_SENSITIVE_BLOCKED_COLUMNS],
    sensitive: true,
    writeMode: "approval_required"
  },
  billing_invoices: {
    table: "billing_invoices",
    allowedColumns: "*",
    blockedColumns: [...DEFAULT_SENSITIVE_BLOCKED_COLUMNS],
    sensitive: true,
    writeMode: "approval_required"
  },
  billing_transactions: {
    table: "billing_transactions",
    allowedColumns: "*",
    blockedColumns: [...DEFAULT_SENSITIVE_BLOCKED_COLUMNS],
    sensitive: true,
    writeMode: "approval_required"
  },
  subscriptions: {
    table: "subscriptions",
    allowedColumns: "*",
    blockedColumns: [...DEFAULT_SENSITIVE_BLOCKED_COLUMNS],
    sensitive: true,
    writeMode: "approval_required"
  },
  invoices: {
    table: "invoices",
    allowedColumns: "*",
    blockedColumns: [...DEFAULT_SENSITIVE_BLOCKED_COLUMNS],
    sensitive: true,
    writeMode: "approval_required"
  },
  payment_methods: {
    table: "payment_methods",
    allowedColumns: "*",
    blockedColumns: [...DEFAULT_SENSITIVE_BLOCKED_COLUMNS],
    sensitive: true,
    writeMode: "approval_required"
  },
  projects: {
    table: "projects",
    allowedColumns: "*",
    blockedColumns: [],
    sensitive: false,
    writeMode: "safe_execute"
  },
  project_tasks: {
    table: "project_tasks",
    allowedColumns: "*",
    blockedColumns: [],
    sensitive: false,
    writeMode: "safe_execute"
  },
  appointments: {
    table: "appointments",
    allowedColumns: "*",
    blockedColumns: [],
    sensitive: false,
    writeMode: "safe_execute"
  },
  booking_sessions: {
    table: "booking_sessions",
    allowedColumns: "*",
    blockedColumns: [],
    sensitive: false,
    writeMode: "safe_execute"
  },
  agent_context_states: {
    table: "agent_context_states",
    allowedColumns: "*",
    blockedColumns: [],
    sensitive: false,
    writeMode: "safe_execute"
  },
  user_preferences: {
    table: "user_preferences",
    allowedColumns: "*",
    blockedColumns: [],
    sensitive: false,
    writeMode: "safe_execute"
  },
  ai_messages: {
    table: "ai_messages",
    allowedColumns: ["id", "session_id", "role", "content", "created_at", "tenant_id"],
    blockedColumns: [],
    sensitive: false,
    writeMode: "read_only"
  },
  vee_mcp_calls: {
    table: "vee_mcp_calls",
    allowedColumns: "*",
    blockedColumns: [],
    sensitive: false,
    writeMode: "read_only"
  },
  vee_action_approvals: {
    table: "vee_action_approvals",
    allowedColumns: "*",
    blockedColumns: [],
    sensitive: false,
    writeMode: "read_only"
  },
  vee_execution_notes: {
    table: "vee_execution_notes",
    allowedColumns: "*",
    blockedColumns: [],
    sensitive: false,
    writeMode: "safe_execute"
  },
  vee_incidents: {
    table: "vee_incidents",
    allowedColumns: "*",
    blockedColumns: [],
    sensitive: false,
    writeMode: "safe_execute"
  },
  vee_project_events: {
    table: "vee_project_events",
    allowedColumns: "*",
    blockedColumns: [],
    sensitive: false,
    writeMode: "safe_execute"
  },
  vee_status_history: {
    table: "vee_status_history",
    allowedColumns: "*",
    blockedColumns: [],
    sensitive: false,
    writeMode: "safe_execute"
  },
  vee_operational_decisions: {
    table: "vee_operational_decisions",
    allowedColumns: "*",
    blockedColumns: [],
    sensitive: false,
    writeMode: "safe_execute"
  },
  vee_blocks: {
    table: "vee_blocks",
    allowedColumns: "*",
    blockedColumns: [],
    sensitive: false,
    writeMode: "safe_execute"
  }
};

const BASE_TABLE_POLICIES_MMCC: Record<string, TablePolicy> = {
  tenants: {
    table: "tenants",
    allowedColumns: ["id", "slug", "name", "status", "plan", "created_at", "updated_at"],
    blockedColumns: [],
    sensitive: false,
    writeMode: "approval_required"
  },
  customers: {
    table: "customers",
    allowedColumns: "*",
    blockedColumns: ["phone", "email", "cpf"],
    sensitive: true,
    writeMode: "approval_required"
  },
  users: {
    table: "users",
    allowedColumns: ["id", "name", "role", "is_active", "created_at", "updated_at"],
    blockedColumns: ["password", "remember_token", "two_factor_secret", "two_factor_recovery_codes"],
    sensitive: true,
    writeMode: "approval_required"
  },
  ai_messages: {
    table: "ai_messages",
    allowedColumns: "*",
    blockedColumns: [],
    sensitive: false,
    writeMode: "safe_execute"
  },
  ai_context_states: {
    table: "ai_context_states",
    allowedColumns: "*",
    blockedColumns: [],
    sensitive: false,
    writeMode: "safe_execute"
  },
  ai_memory_snapshots: {
    table: "ai_memory_snapshots",
    allowedColumns: "*",
    blockedColumns: [],
    sensitive: false,
    writeMode: "safe_execute"
  },
  ai_usage_events: {
    table: "ai_usage_events",
    allowedColumns: "*",
    blockedColumns: [],
    sensitive: false,
    writeMode: "safe_execute"
  },
  agent_executions: {
    table: "agent_executions",
    allowedColumns: "*",
    blockedColumns: [],
    sensitive: false,
    writeMode: "safe_execute"
  },
  booking_sessions: {
    table: "booking_sessions",
    allowedColumns: "*",
    blockedColumns: [],
    sensitive: false,
    writeMode: "safe_execute"
  },
  appointments: {
    table: "appointments",
    allowedColumns: "*",
    blockedColumns: [],
    sensitive: false,
    writeMode: "safe_execute"
  },
  appointment_notes: {
    table: "appointment_notes",
    allowedColumns: "*",
    blockedColumns: [],
    sensitive: false,
    writeMode: "safe_execute"
  },
  appointment_confirmation_reminder_dispatches: {
    table: "appointment_confirmation_reminder_dispatches",
    allowedColumns: "*",
    blockedColumns: [],
    sensitive: false,
    writeMode: "safe_execute"
  },
  customer_appointment_reminder_dispatches: {
    table: "customer_appointment_reminder_dispatches",
    allowedColumns: "*",
    blockedColumns: [],
    sensitive: false,
    writeMode: "safe_execute"
  },
  professional_daily_schedule_dispatches: {
    table: "professional_daily_schedule_dispatches",
    allowedColumns: "*",
    blockedColumns: [],
    sensitive: false,
    writeMode: "safe_execute"
  }
};

const BASE_NAMED_VIEWS_MMCRIATIVOS: Record<string, string> = {
  project_timeline: "v_project_timeline",
  v_project_timeline: "v_project_timeline",
  active_projects: "v_active_projects",
  pending_work: "v_pending_work",
  timeline_events: "vee_project_events",
  timeline_decisions: "vee_operational_decisions",
  timeline_blocks: "vee_blocks",
  timeline_status_changes: "vee_status_history",
  timeline_agent_actions: "vee_mcp_calls"
};

const BASE_NAMED_VIEWS_MMCC: Record<string, string> = {};

const BASE_TABLE_POLICIES_BY_TARGET: Record<DbTarget, Record<string, TablePolicy>> = {
  mmcriativos: BASE_TABLE_POLICIES_MMCRIATIVOS,
  mmcc: BASE_TABLE_POLICIES_MMCC
};

const BASE_NAMED_VIEWS_BY_TARGET: Record<DbTarget, Record<string, string>> = {
  mmcriativos: BASE_NAMED_VIEWS_MMCRIATIVOS,
  mmcc: BASE_NAMED_VIEWS_MMCC
};

const TARGET_ALLOWLISTS: Record<DbTarget, TargetAllowlist> = {
  mmcriativos: buildTargetAllowlist("mmcriativos"),
  mmcc: buildTargetAllowlist("mmcc")
};

export function normalizeDbTarget(value: unknown): DbTarget {
  if (typeof value !== "string") {
    return DEFAULT_TARGET;
  }
  const normalized = value.trim().toLowerCase();
  return DB_TARGETS.includes(normalized as DbTarget) ? (normalized as DbTarget) : DEFAULT_TARGET;
}

export function resolveTablePolicy(tableOrView: string, dbTarget?: DbTarget): TablePolicy | null {
  const target = normalizeDbTarget(dbTarget);
  const allowlist = TARGET_ALLOWLISTS[target];
  const key = normalizeIdentifier(tableOrView);
  if (!key) {
    return null;
  }

  if (key in allowlist.tablePolicies) {
    return allowlist.tablePolicies[key] ?? null;
  }

  if (key in allowlist.namedViews) {
    return {
      table: allowlist.namedViews[key]!,
      allowedColumns: "*",
      blockedColumns: [],
      sensitive: false,
      writeMode: "read_only"
    };
  }

  const logicalName = allowlist.logicalViewByResolved.get(key);
  if (logicalName) {
    return {
      table: allowlist.namedViews[logicalName]!,
      allowedColumns: "*",
      blockedColumns: [],
      sensitive: false,
      writeMode: "read_only"
    };
  }

  return null;
}

export function resolveActualTableName(tableOrView: string, dbTarget?: DbTarget): string {
  const target = normalizeDbTarget(dbTarget);
  const allowlist = TARGET_ALLOWLISTS[target];
  const key = normalizeIdentifier(tableOrView);
  if (!key) {
    return tableOrView;
  }

  if (key in allowlist.namedViews) {
    return allowlist.namedViews[key]!;
  }

  if (key in allowlist.tablePolicies) {
    return allowlist.tablePolicies[key]!.table;
  }

  const logicalName = allowlist.logicalViewByResolved.get(key);
  if (logicalName) {
    return allowlist.namedViews[logicalName] ?? tableOrView;
  }

  return tableOrView;
}

export function listQueryAllowlist(dbTarget?: DbTarget): QueryAllowlistEntry[] {
  const target = normalizeDbTarget(dbTarget);
  const allowlist = TARGET_ALLOWLISTS[target];
  const entries: QueryAllowlistEntry[] = [];

  for (const [logicalName, policy] of Object.entries(allowlist.tablePolicies)) {
    entries.push({
      logical_name: logicalName,
      resolved_name: policy.table,
      kind: "table",
      source: allowlist.tablePolicySources.get(logicalName) ?? "base",
      allowed_columns: policy.allowedColumns,
      blocked_columns: policy.blockedColumns,
      sensitive: policy.sensitive,
      write_mode: policy.writeMode
    });
  }

  for (const [logicalName, actualName] of Object.entries(allowlist.namedViews)) {
    entries.push({
      logical_name: logicalName,
      resolved_name: actualName,
      kind: "named_view",
      source: allowlist.namedViewSources.get(logicalName) ?? "base",
      allowed_columns: "*",
      blocked_columns: [],
      sensitive: false,
      write_mode: "read_only"
    });
  }

  return entries.sort((a, b) => {
    if (a.kind !== b.kind) {
      return a.kind.localeCompare(b.kind);
    }
    return a.logical_name.localeCompare(b.logical_name);
  });
}

export function listWritePolicyGroups(dbTarget?: DbTarget): WritePolicyGroups {
  const target = normalizeDbTarget(dbTarget);
  const allowlist = TARGET_ALLOWLISTS[target];
  const groups: WritePolicyGroups = {
    safe_execute: [],
    approval_required: [],
    read_only: []
  };

  for (const [logicalName, policy] of Object.entries(allowlist.tablePolicies)) {
    if (policy.writeMode === "safe_execute") {
      groups.safe_execute.push(logicalName);
    } else if (policy.writeMode === "approval_required") {
      groups.approval_required.push(logicalName);
    } else {
      groups.read_only.push(logicalName);
    }
  }

  groups.safe_execute.sort((a, b) => a.localeCompare(b));
  groups.approval_required.sort((a, b) => a.localeCompare(b));
  groups.read_only.sort((a, b) => a.localeCompare(b));

  return groups;
}

function buildTargetAllowlist(dbTarget: DbTarget): TargetAllowlist {
  const tablePolicySources = new Map<string, AllowlistSource>();
  const namedViewSources = new Map<string, AllowlistSource>();
  const mergedTablePolicies = new Map<string, TablePolicy>();
  const mergedNamedViews = new Map<string, string>();

  for (const [rawName, rawPolicy] of Object.entries(BASE_TABLE_POLICIES_BY_TARGET[dbTarget])) {
    const name = normalizeIdentifier(rawName);
    if (!name) {
      continue;
    }
    const normalized = normalizeTablePolicy(name, rawPolicy);
    if (!normalized) {
      continue;
    }
    mergedTablePolicies.set(name, normalized);
    tablePolicySources.set(name, "base");
  }

  for (const [rawName, rawActual] of Object.entries(BASE_NAMED_VIEWS_BY_TARGET[dbTarget])) {
    const name = normalizeIdentifier(rawName);
    const actual = normalizeIdentifier(rawActual);
    if (!name || !actual) {
      continue;
    }
    mergedNamedViews.set(name, actual);
    namedViewSources.set(name, "base");
  }

  const extraTablePolicies = parseExtraTablePolicies(dbTarget);
  for (const [name, policy] of extraTablePolicies.entries()) {
    mergedTablePolicies.set(name, policy);
    tablePolicySources.set(name, "env");
  }

  const extraNamedViews = parseExtraNamedViews(dbTarget);
  for (const [name, actual] of extraNamedViews.entries()) {
    mergedNamedViews.set(name, actual);
    namedViewSources.set(name, "env");
  }

  applyWriteModeOverrides(mergedTablePolicies, tablePolicySources, dbTarget);

  const tablePolicies = Object.fromEntries(
    [...mergedTablePolicies.entries()].sort((a, b) => a[0].localeCompare(b[0]))
  );
  const namedViews = Object.fromEntries(
    [...mergedNamedViews.entries()].sort((a, b) => a[0].localeCompare(b[0]))
  );

  const logicalViewByResolved = new Map<string, string>();
  for (const [logicalName, actualName] of Object.entries(namedViews)) {
    logicalViewByResolved.set(actualName, logicalName);
  }

  return { tablePolicies, namedViews, tablePolicySources, namedViewSources, logicalViewByResolved };
}

function parseExtraTablePolicies(dbTarget: DbTarget): Map<string, TablePolicy> {
  const raw =
    parseJsonEnv(`DB_ALLOWLIST_EXTRA_TABLE_POLICIES_${dbTarget.toUpperCase()}_JSON`) ??
    parseJsonEnv("DB_ALLOWLIST_EXTRA_TABLE_POLICIES_JSON") ??
    parseJsonEnv(`DB_QUERY_EXTRA_TABLE_POLICIES_${dbTarget.toUpperCase()}_JSON`) ??
    parseJsonEnv("DB_QUERY_EXTRA_TABLE_POLICIES_JSON");

  const parsed = new Map<string, TablePolicy>();
  if (!raw || typeof raw !== "object" || Array.isArray(raw)) {
    return parsed;
  }

  for (const [rawName, rawPolicy] of Object.entries(raw)) {
    const name = normalizeIdentifier(rawName);
    if (!name) {
      continue;
    }
    const policy = normalizeTablePolicy(name, rawPolicy);
    if (!policy) {
      continue;
    }
    parsed.set(name, policy);
  }

  return parsed;
}

function parseExtraNamedViews(dbTarget: DbTarget): Map<string, string> {
  const raw =
    parseJsonEnv(`DB_ALLOWLIST_EXTRA_NAMED_VIEWS_${dbTarget.toUpperCase()}_JSON`) ??
    parseJsonEnv("DB_ALLOWLIST_EXTRA_NAMED_VIEWS_JSON") ??
    parseJsonEnv(`DB_QUERY_EXTRA_NAMED_VIEWS_${dbTarget.toUpperCase()}_JSON`) ??
    parseJsonEnv("DB_QUERY_EXTRA_NAMED_VIEWS_JSON");

  const parsed = new Map<string, string>();
  if (!raw || typeof raw !== "object" || Array.isArray(raw)) {
    return parsed;
  }

  for (const [rawName, rawActual] of Object.entries(raw)) {
    const name = normalizeIdentifier(rawName);
    const actual = normalizeIdentifier(rawActual);
    if (!name || !actual) {
      continue;
    }
    parsed.set(name, actual);
  }

  return parsed;
}

function applyWriteModeOverrides(
  merged: Map<string, TablePolicy>,
  sourceMap: Map<string, AllowlistSource>,
  dbTarget: DbTarget
): void {
  const suffix = dbTarget.toUpperCase();
  const safeExecuteTables = parseCsvIdentifiersFromEnv([
    `DB_SAFE_EXECUTE_TABLES_${suffix}`,
    "DB_SAFE_EXECUTE_TABLES"
  ]);
  const approvalRequiredTables = parseCsvIdentifiersFromEnv([
    `DB_APPROVAL_REQUIRED_TABLES_${suffix}`,
    "DB_APPROVAL_REQUIRED_TABLES"
  ]);
  const readOnlyTables = parseCsvIdentifiersFromEnv([
    `DB_READ_ONLY_TABLES_${suffix}`,
    "DB_READ_ONLY_TABLES"
  ]);

  for (const table of safeExecuteTables) {
    const existing = merged.get(table);
    if (existing) {
      merged.set(table, { ...existing, writeMode: "safe_execute" });
    } else {
      merged.set(table, {
        table,
        allowedColumns: "*",
        blockedColumns: [],
        sensitive: false,
        writeMode: "safe_execute"
      });
    }
    sourceMap.set(table, "env");
  }

  for (const table of approvalRequiredTables) {
    const existing = merged.get(table);
    if (existing) {
      merged.set(table, {
        ...existing,
        writeMode: "approval_required",
        sensitive: true,
        blockedColumns:
          existing.blockedColumns.length > 0
            ? existing.blockedColumns
            : [...DEFAULT_SENSITIVE_BLOCKED_COLUMNS]
      });
    } else {
      merged.set(table, {
        table,
        allowedColumns: "*",
        blockedColumns: [...DEFAULT_SENSITIVE_BLOCKED_COLUMNS],
        sensitive: true,
        writeMode: "approval_required"
      });
    }
    sourceMap.set(table, "env");
  }

  for (const table of readOnlyTables) {
    const existing = merged.get(table);
    if (existing) {
      merged.set(table, { ...existing, writeMode: "read_only" });
    } else {
      merged.set(table, {
        table,
        allowedColumns: "*",
        blockedColumns: [],
        sensitive: false,
        writeMode: "read_only"
      });
    }
    sourceMap.set(table, "env");
  }
}

function parseCsvIdentifiersFromEnv(variableNames: string[]): string[] {
  const values: string[] = [];
  const seen = new Set<string>();

  for (const variableName of variableNames) {
    const raw = (process.env[variableName] ?? "").trim();
    if (!raw) {
      continue;
    }

    for (const part of raw.split(",")) {
      const normalized = normalizeIdentifier(part);
      if (!normalized || seen.has(normalized)) {
        continue;
      }
      seen.add(normalized);
      values.push(normalized);
    }
  }

  return values;
}

function normalizeTablePolicy(logicalName: string, raw: unknown): TablePolicy | null {
  if (!raw || typeof raw !== "object" || Array.isArray(raw)) {
    return null;
  }

  const data = raw as Record<string, unknown>;
  const table = normalizeIdentifier(data["table"]);
  const allowedColumns = normalizeAllowedColumns(data["allowedColumns"]);
  const blockedColumns = normalizeColumnArray(data["blockedColumns"]);
  const sensitive = typeof data["sensitive"] === "boolean" ? data["sensitive"] : false;
  const writeMode = normalizeWriteMode(data["writeMode"]);

  if (!allowedColumns || !blockedColumns || !writeMode) {
    return null;
  }

  return {
    table: table ?? logicalName,
    allowedColumns,
    blockedColumns,
    sensitive,
    writeMode
  };
}

function normalizeAllowedColumns(value: unknown): string[] | "*" | null {
  if (value === "*") {
    return "*";
  }
  return normalizeColumnArray(value);
}

function normalizeColumnArray(value: unknown): string[] | null {
  if (!Array.isArray(value)) {
    return null;
  }

  const normalized: string[] = [];
  const seen = new Set<string>();

  for (const item of value) {
    const column = normalizeIdentifier(item);
    if (!column) {
      return null;
    }
    if (seen.has(column)) {
      continue;
    }
    seen.add(column);
    normalized.push(column);
  }

  return normalized;
}

function normalizeWriteMode(value: unknown): WriteMode | null {
  if (typeof value !== "string") {
    return null;
  }
  const normalized = value.trim().toLowerCase();
  return WRITE_MODES.includes(normalized as WriteMode) ? (normalized as WriteMode) : null;
}

function normalizeIdentifier(value: unknown): string | null {
  if (typeof value !== "string") {
    return null;
  }
  const normalized = value.trim().toLowerCase();
  if (!IDENTIFIER_REGEX.test(normalized)) {
    return null;
  }
  return normalized;
}

function parseJsonEnv(name: string): unknown {
  const raw = (process.env[name] ?? "").trim();
  if (!raw) {
    return null;
  }
  try {
    return JSON.parse(raw);
  } catch {
    return null;
  }
}
