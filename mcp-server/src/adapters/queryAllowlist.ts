// ─── Table & Column Access Policy ────────────────────────────────────────────
// Single source of truth for all DB access control in vee_db_* tools.
// Every table, column allowlist, and write permission is declared here.

export type WriteMode = "safe_execute" | "approval_required" | "read_only";

export type TablePolicy = {
  /** Exact MySQL table or view name */
  table: string;
  /**
   * Columns the tool may SELECT or write.
   * Use "*" to mean all columns except those in blockedColumns.
   */
  allowedColumns: string[] | "*";
  /**
   * Columns that are ALWAYS excluded from SELECT results and rejected in writes,
   * even when allowedColumns is "*".
   */
  blockedColumns: string[];
  /** If true, queries against this table are tagged as sensitive in audit. */
  sensitive: boolean;
  /** Controls whether writes require approval or can be safe_execute. */
  writeMode: WriteMode;
};

/**
 * Canonical allowlist. Tables NOT listed here are rejected before any SQL is built.
 *
 * writeMode meanings:
 *   read_only         — tool may only SELECT; all writes blocked
 *   safe_execute      — writes execute directly (no approval needed)
 *   approval_required — writes require a vee_action_approvals entry approved first
 */
export const TABLE_POLICIES: Record<string, TablePolicy> = {
  // ── Core domain — protected (approval_required) ───────────────────────────
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

  // ── Core domain — safe (safe_execute) ────────────────────────────────────
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

  // ── Vee operational — read_only ───────────────────────────────────────────
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

  // ── Vee operational — safe_execute ────────────────────────────────────────
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

  // ── Layer 3 traceability — safe_execute ───────────────────────────────────
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

/**
 * Named views: logical name used in tool inputs → MySQL VIEW name.
 * Views are always read_only from the tool perspective.
 */
export const NAMED_VIEWS: Record<string, string> = {
  project_timeline: "v_project_timeline",
  active_projects: "v_active_projects",
  pending_work: "v_pending_work"
};

/**
 * Returns the table policy for a given logical name (table or named view).
 * Named views return a synthetic read_only policy pointing to the MySQL view.
 * Returns null if the name is not in the allowlist.
 */
export function resolveTablePolicy(tableOrView: string): TablePolicy | null {
  if (tableOrView in TABLE_POLICIES) {
    return TABLE_POLICIES[tableOrView] ?? null;
  }

  if (tableOrView in NAMED_VIEWS) {
    return {
      table: NAMED_VIEWS[tableOrView]!,
      allowedColumns: "*",
      blockedColumns: [],
      sensitive: false,
      writeMode: "read_only"
    };
  }

  return null;
}

/** Returns the actual MySQL table/view name after resolving named view aliases. */
export function resolveActualTableName(tableOrView: string): string {
  if (tableOrView in NAMED_VIEWS) {
    return NAMED_VIEWS[tableOrView]!;
  }
  return tableOrView;
}
