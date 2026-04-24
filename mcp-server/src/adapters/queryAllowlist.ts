// Table and column access policy for vee_db_* tools.
// Every table/view exposed through generic DB tools must be allowlisted here
// or through DB_ALLOWLIST_EXTRA_* environment variables.

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

const IDENTIFIER_REGEX = /^[a-z_][a-z0-9_]*$/i;
const WRITE_MODES: WriteMode[] = ["safe_execute", "approval_required", "read_only"];
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

const BASE_TABLE_POLICIES: Record<string, TablePolicy> = {
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

const BASE_NAMED_VIEWS: Record<string, string> = {
  project_timeline: "v_project_timeline",
  active_projects: "v_active_projects",
  pending_work: "v_pending_work",
  timeline_events: "vee_project_events",
  timeline_decisions: "vee_operational_decisions",
  timeline_blocks: "vee_blocks",
  timeline_status_changes: "vee_status_history",
  timeline_agent_actions: "vee_mcp_calls"
};

const tablePolicySources = new Map<string, AllowlistSource>();
const namedViewSources = new Map<string, AllowlistSource>();

export const TABLE_POLICIES: Record<string, TablePolicy> = buildTablePolicies();
export const NAMED_VIEWS: Record<string, string> = buildNamedViews();

export function resolveTablePolicy(tableOrView: string): TablePolicy | null {
  const key = normalizeIdentifier(tableOrView);
  if (!key) {
    return null;
  }

  if (key in TABLE_POLICIES) {
    return TABLE_POLICIES[key] ?? null;
  }

  if (key in NAMED_VIEWS) {
    return {
      table: NAMED_VIEWS[key]!,
      allowedColumns: "*",
      blockedColumns: [],
      sensitive: false,
      writeMode: "read_only"
    };
  }

  return null;
}

export function resolveActualTableName(tableOrView: string): string {
  const key = normalizeIdentifier(tableOrView);
  if (!key) {
    return tableOrView;
  }

  if (key in NAMED_VIEWS) {
    return NAMED_VIEWS[key]!;
  }

  if (key in TABLE_POLICIES) {
    return TABLE_POLICIES[key]!.table;
  }

  return tableOrView;
}

export function listQueryAllowlist(): QueryAllowlistEntry[] {
  const entries: QueryAllowlistEntry[] = [];

  for (const [logicalName, policy] of Object.entries(TABLE_POLICIES)) {
    entries.push({
      logical_name: logicalName,
      resolved_name: policy.table,
      kind: "table",
      source: tablePolicySources.get(logicalName) ?? "base",
      allowed_columns: policy.allowedColumns,
      blocked_columns: policy.blockedColumns,
      sensitive: policy.sensitive,
      write_mode: policy.writeMode
    });
  }

  for (const [logicalName, actualName] of Object.entries(NAMED_VIEWS)) {
    entries.push({
      logical_name: logicalName,
      resolved_name: actualName,
      kind: "named_view",
      source: namedViewSources.get(logicalName) ?? "base",
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

export function listWritePolicyGroups(): WritePolicyGroups {
  const groups: WritePolicyGroups = {
    safe_execute: [],
    approval_required: [],
    read_only: []
  };

  for (const [logicalName, policy] of Object.entries(TABLE_POLICIES)) {
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

function buildTablePolicies(): Record<string, TablePolicy> {
  const merged = new Map<string, TablePolicy>();

  for (const [rawName, rawPolicy] of Object.entries(BASE_TABLE_POLICIES)) {
    const name = normalizeIdentifier(rawName);
    if (!name) {
      continue;
    }
    const normalized = normalizeTablePolicy(name, rawPolicy);
    if (!normalized) {
      continue;
    }
    merged.set(name, normalized);
    tablePolicySources.set(name, "base");
  }

  const extras = parseExtraTablePolicies();
  for (const [name, policy] of extras) {
    merged.set(name, policy);
    tablePolicySources.set(name, "env");
  }

  applyWriteModeOverrides(merged);

  return Object.fromEntries([...merged.entries()].sort((a, b) => a[0].localeCompare(b[0])));
}

function buildNamedViews(): Record<string, string> {
  const merged = new Map<string, string>();

  for (const [rawName, rawActual] of Object.entries(BASE_NAMED_VIEWS)) {
    const name = normalizeIdentifier(rawName);
    const actual = normalizeIdentifier(rawActual);
    if (!name || !actual) {
      continue;
    }
    merged.set(name, actual);
    namedViewSources.set(name, "base");
  }

  const extras = parseExtraNamedViews();
  for (const [name, actual] of extras) {
    merged.set(name, actual);
    namedViewSources.set(name, "env");
  }

  return Object.fromEntries([...merged.entries()].sort((a, b) => a[0].localeCompare(b[0])));
}

function parseExtraTablePolicies(): Map<string, TablePolicy> {
  const raw = parseJsonEnv("DB_ALLOWLIST_EXTRA_TABLE_POLICIES_JSON")
    ?? parseJsonEnv("DB_QUERY_EXTRA_TABLE_POLICIES_JSON");

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

function parseExtraNamedViews(): Map<string, string> {
  const raw = parseJsonEnv("DB_ALLOWLIST_EXTRA_NAMED_VIEWS_JSON")
    ?? parseJsonEnv("DB_QUERY_EXTRA_NAMED_VIEWS_JSON");

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

function applyWriteModeOverrides(merged: Map<string, TablePolicy>): void {
  const safeExecuteTables = parseCsvIdentifiersFromEnv([
    "DB_SAFE_EXECUTE_TABLES_MMCRIATIVOS",
    "DB_SAFE_EXECUTE_TABLES_MMCC",
    "DB_SAFE_EXECUTE_TABLES"
  ]);
  const approvalRequiredTables = parseCsvIdentifiersFromEnv([
    "DB_APPROVAL_REQUIRED_TABLES_MMCRIATIVOS",
    "DB_APPROVAL_REQUIRED_TABLES_MMCC",
    "DB_APPROVAL_REQUIRED_TABLES"
  ]);
  const readOnlyTables = parseCsvIdentifiersFromEnv([
    "DB_READ_ONLY_TABLES_MMCRIATIVOS",
    "DB_READ_ONLY_TABLES_MMCC",
    "DB_READ_ONLY_TABLES"
  ]);

  for (const table of safeExecuteTables) {
    const existing = merged.get(table);
    if (existing) {
      merged.set(table, { ...existing, writeMode: "safe_execute" });
      tablePolicySources.set(table, "env");
      continue;
    }
    merged.set(table, {
      table,
      allowedColumns: "*",
      blockedColumns: [],
      sensitive: false,
      writeMode: "safe_execute"
    });
    tablePolicySources.set(table, "env");
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
      tablePolicySources.set(table, "env");
      continue;
    }
    merged.set(table, {
      table,
      allowedColumns: "*",
      blockedColumns: [...DEFAULT_SENSITIVE_BLOCKED_COLUMNS],
      sensitive: true,
      writeMode: "approval_required"
    });
    tablePolicySources.set(table, "env");
  }

  for (const table of readOnlyTables) {
    const existing = merged.get(table);
    if (existing) {
      merged.set(table, { ...existing, writeMode: "read_only" });
      tablePolicySources.set(table, "env");
      continue;
    }
    merged.set(table, {
      table,
      allowedColumns: "*",
      blockedColumns: [],
      sensitive: false,
      writeMode: "read_only"
    });
    tablePolicySources.set(table, "env");
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

  const normalized = normalizeColumnArray(value);
  return normalized;
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
