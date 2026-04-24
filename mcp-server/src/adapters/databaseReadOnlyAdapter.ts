import mysql from "mysql2/promise";
import type { FieldPacket, RowDataPacket } from "mysql2/promise";
import {
  buildSelectQuery,
  type StructuredQueryInput
} from "./structuredQueryBuilder.js";

// ─── Config ──────────────────────────────────────────────────────────────────

export type DatabaseReadOnlyAdapterConfig = {
  host: string;
  port: number;
  user: string;
  password: string;
  database: string;
  maxRowsPerQuery: number;
  rateLimitPerMinute: number;
  auditRateLimitPerMinute?: number;
};

// ─── Output types ─────────────────────────────────────────────────────────────

export type DbTenantProfile = {
  id: number;
  slug: string;
  name: string;
  status: string | null;
  plan: string | null;
  created_at: string | null;
};

export type DbClientProfile = {
  id: number;
  name: string;
  phone_masked: string | null;
  email_masked: string | null;
  role: string | null;
  is_active: boolean;
  client_name: string | null;
  client_slug: string | null;
};

export type DbAiMessage = {
  id: number;
  session_id: string | null;
  role: string;
  content: string;
  created_at: string | null;
};

export type DbContextState = {
  id: number;
  tenant_id: number | null;
  session_id: string | null;
  key: string;
  state: unknown;
  updated_at: string | null;
};

export type DbBookingSession = {
  id: number;
  tenant_id: number | null;
  client_id: number | null;
  status: string | null;
  service: string | null;
  data_summary: Record<string, unknown>;
  created_at: string | null;
  updated_at: string | null;
};

export type DbAppointment = {
  id: number;
  tenant_id: number | null;
  client_name: string | null;
  scheduled_at: string | null;
  status: string | null;
  service: string | null;
  notes: string | null;
};

export type DbProjectSummary = {
  id: number;
  name: string;
  slug: string;
  summary: string | null;
  client_name: string | null;
  service_name: string | null;
  finished_at: string | null;
  tasks_total: number;
  tasks_pending: number;
  tasks_in_progress: number;
  tasks_done: number;
  created_at: string | null;
};

export type DbPendingTask = {
  id: number;
  title: string;
  description: string | null;
  status: string;
  project_name: string | null;
  project_slug: string | null;
  client_name: string | null;
  assignee_name: string | null;
  created_at: string | null;
};

export type DbUserPreference = {
  key: string;
  value: string | null;
  tenant_id: number | null;
  updated_at: string | null;
};

export type DbAgentExecutionEntry = {
  id: number;
  call_id: string;
  tool_name: string;
  status: string;
  permission_level: string | null;
  is_write: boolean;
  duration_ms: number | null;
  error_message: string | null;
  created_at: string | null;
};

export type DbSchemaObjectType = "table" | "view";

export type DbSchemaColumn = {
  ordinal_position: number;
  name: string;
  data_type: string;
  column_type: string;
  nullable: boolean;
  default_value: string | null;
  extra: string | null;
  key: string | null;
  is_primary_key: boolean;
  is_foreign_key: boolean;
};

export type DbSchemaForeignKey = {
  constraint_name: string;
  column_name: string;
  referenced_table: string;
  referenced_column: string;
  update_rule: string | null;
  delete_rule: string | null;
};

export type DbSchemaIndex = {
  name: string;
  unique: boolean;
  index_type: string | null;
  cardinality: number | null;
  columns: Array<{
    name: string;
    seq_in_index: number;
    collation: string | null;
    sub_part: number | null;
  }>;
};

export type DbSchemaObjectInspection = {
  object_name: string;
  object_type: DbSchemaObjectType;
  exists: boolean;
  engine: string | null;
  estimated_rows: number | null;
  created_at: string | null;
  updated_at: string | null;
  columns: DbSchemaColumn[];
  primary_key: string[];
  foreign_keys: DbSchemaForeignKey[];
};

export type DbSchemaObjectRelations = {
  object_name: string;
  outgoing: DbSchemaForeignKey[];
  incoming: Array<{
    source_table: string;
    source_column: string;
    referenced_column: string;
    constraint_name: string;
    update_rule: string | null;
    delete_rule: string | null;
  }>;
};

export type DbRelationshipDiscoveryEdge = {
  source_table: string;
  source_column: string;
  target_table: string;
  target_column: string;
  relation_kind: "foreign_key" | "heuristic";
  confidence: "high" | "medium";
  constraint_name: string | null;
};

// ─── Rate limiter (in-memory sliding window) ─────────────────────────────────

const rateLimitStore = new Map<string, number[]>();

function checkRateLimit(clientKey: string, maxPerMinute: number): void {
  const now = Date.now();
  const windowStart = now - 60_000;
  const calls = (rateLimitStore.get(clientKey) ?? []).filter((t) => t > windowStart);

  if (calls.length >= maxPerMinute) {
    throw new Error(`Rate limit exceeded: max ${maxPerMinute} queries/min for key "${clientKey}".`);
  }

  calls.push(now);
  rateLimitStore.set(clientKey, calls);
}

// ─── Data masking ─────────────────────────────────────────────────────────────

function maskPhone(phone: string | null | undefined): string | null {
  if (!phone) return null;
  const s = String(phone).trim();
  if (s.length <= 4) return "***";
  return s.slice(0, 2) + "*".repeat(Math.max(1, s.length - 4)) + s.slice(-2);
}

function maskEmail(email: string | null | undefined): string | null {
  if (!email) return null;
  const parts = String(email).split("@");
  if (parts.length !== 2 || !parts[0] || !parts[1]) return "***";
  return `${parts[0].slice(0, 1)}***@${parts[1]}`;
}

// ─── Adapter ──────────────────────────────────────────────────────────────────

export class DatabaseReadOnlyAdapter {
  private readonly config: DatabaseReadOnlyAdapterConfig;
  private pool: mysql.Pool | null = null;

  constructor(config: DatabaseReadOnlyAdapterConfig) {
    this.config = {
      ...config,
      host: config.host.trim(),
      user: config.user.trim(),
      database: config.database.trim(),
      auditRateLimitPerMinute: Math.max(config.rateLimitPerMinute, config.auditRateLimitPerMinute ?? config.rateLimitPerMinute)
    };
  }

  isConfigured(): boolean {
    return this.config.host.length > 0 && this.config.user.length > 0 && this.config.database.length > 0;
  }

  private getPool(): mysql.Pool {
    if (!this.pool) {
      this.pool = mysql.createPool({
        host: this.config.host,
        port: this.config.port,
        user: this.config.user,
        password: this.config.password,
        database: this.config.database,
        connectionLimit: 3,
        connectTimeout: 10_000,
        charset: "utf8mb4"
      });
    }
    return this.pool;
  }

  private assertConfigured(): void {
    if (!this.isConfigured()) {
      throw new Error(
        "DB_READONLY_HOST, DB_READONLY_USER, and DB_READONLY_DATABASE are required for database tools."
      );
    }
  }

  private async query<T>(
    sql: string,
    params: (string | number | boolean | null)[],
    clientKey: string,
    options?: {
      auditMode?: boolean;
      maxRowsOverride?: number;
    }
  ): Promise<T[]> {
    this.assertConfigured();
    const maxPerMinute = options?.auditMode
      ? this.config.auditRateLimitPerMinute ?? this.config.rateLimitPerMinute
      : this.config.rateLimitPerMinute;
    checkRateLimit(clientKey, maxPerMinute);

    const [rows] = await this.getPool().execute(sql, params) as [RowDataPacket[], FieldPacket[]];
    const result = rows as unknown as T[];
    const maxRows = options?.maxRowsOverride ?? this.config.maxRowsPerQuery;
    return result.slice(0, Math.max(1, maxRows));
  }

  private safeStr(v: unknown): string | null {
    if (v === null || v === undefined) return null;
    return String(v);
  }

  private safeBool(v: unknown): boolean {
    return v === 1 || v === true || v === "1";
  }

  private safeNum(v: unknown): number | null {
    const n = Number(v);
    return Number.isFinite(n) ? n : null;
  }

  private normalizeIdentifier(value: string, label: string): string {
    const normalized = value.trim().toLowerCase();
    if (!/^[a-z_][a-z0-9_]*$/i.test(normalized)) {
      throw new Error(`Invalid ${label} "${value}". Use alphanumeric identifiers with underscores.`);
    }
    return normalized;
  }

  // ─── Tools ──────────────────────────────────────────────────────────────────

  /**
   * Returns a tenant profile by slug.
   * Table: tenants (id, slug, name, status, plan, created_at)
   */
  async getTenantBySlug(slug: string, clientKey: string): Promise<DbTenantProfile | null> {
    const rows = await this.query<Record<string, unknown>>(
      "SELECT id, slug, name, status, plan, created_at FROM tenants WHERE slug = ? LIMIT 1",
      [slug],
      clientKey
    );
    if (rows.length === 0) return null;
    const r = rows[0];
    return {
      id: this.safeNum(r["id"]) ?? 0,
      slug: this.safeStr(r["slug"]) ?? "",
      name: this.safeStr(r["name"]) ?? "",
      status: this.safeStr(r["status"]),
      plan: this.safeStr(r["plan"]),
      created_at: this.safeStr(r["created_at"])
    };
  }

  /**
   * Returns a contact/client by phone (masked in output).
   * Queries contacts table joined with clients.
   */
  async getClientByPhone(phone: string, clientKey: string): Promise<DbClientProfile | null> {
    const normalizedPhone = phone.replace(/\D/g, "");
    if (normalizedPhone.length < 6) {
      throw new Error("phone must have at least 6 digits.");
    }

    const rows = await this.query<Record<string, unknown>>(
      `SELECT ct.id, ct.name, ct.phone, ct.email, ct.role, ct.is_active,
              cl.name AS client_name, cl.slug AS client_slug
       FROM contacts ct
       LEFT JOIN clients cl ON cl.id = ct.client_id
       WHERE REGEXP_REPLACE(ct.phone, '[^0-9]', '') LIKE ? AND ct.is_active = 1
       LIMIT 1`,
      [`%${normalizedPhone}%`],
      clientKey
    );

    if (rows.length === 0) return null;
    const r = rows[0];
    return {
      id: this.safeNum(r["id"]) ?? 0,
      name: this.safeStr(r["name"]) ?? "",
      phone_masked: maskPhone(this.safeStr(r["phone"])),
      email_masked: maskEmail(this.safeStr(r["email"])),
      role: this.safeStr(r["role"]),
      is_active: this.safeBool(r["is_active"]),
      client_name: this.safeStr(r["client_name"]),
      client_slug: this.safeStr(r["client_slug"])
    };
  }

  /**
   * Returns recent AI messages for a session.
   * Table: ai_messages (id, tenant_id, session_id, role, content, created_at)
   */
  async getRecentAiMessages(
    sessionId: string,
    limit: number,
    clientKey: string
  ): Promise<DbAiMessage[]> {
    const safeLimit = Math.max(1, Math.min(limit, this.config.maxRowsPerQuery));
    const rows = await this.query<Record<string, unknown>>(
      `SELECT id, session_id, role, content, created_at
       FROM ai_messages
       WHERE session_id = ?
       ORDER BY created_at DESC
       LIMIT ${safeLimit}`,
      [sessionId],
      clientKey
    );
    return rows.map((r) => ({
      id: this.safeNum(r["id"]) ?? 0,
      session_id: this.safeStr(r["session_id"]),
      role: this.safeStr(r["role"]) ?? "unknown",
      content: this.safeStr(r["content"]) ?? "",
      created_at: this.safeStr(r["created_at"])
    }));
  }

  /**
   * Returns the current context state for an agent session.
   * Table: agent_context_states (id, tenant_id, session_id, key, state JSON, updated_at)
   */
  async getContextState(
    sessionId: string,
    key: string | null,
    clientKey: string
  ): Promise<DbContextState[]> {
    const safeLimit = Math.max(1, this.config.maxRowsPerQuery);
    const sql = key
      ? `SELECT id, tenant_id, session_id, \`key\`, state, updated_at
         FROM agent_context_states WHERE session_id = ? AND \`key\` = ? LIMIT ${safeLimit}`
      : `SELECT id, tenant_id, session_id, \`key\`, state, updated_at
         FROM agent_context_states WHERE session_id = ? LIMIT ${safeLimit}`;

    const params = key
      ? [sessionId, key]
      : [sessionId];

    const rows = await this.query<Record<string, unknown>>(sql, params, clientKey);
    return rows.map((r) => {
      let state: unknown = this.safeStr(r["state"]);
      try {
        if (typeof state === "string") state = JSON.parse(state);
      } catch {
        // keep as string
      }
      return {
        id: this.safeNum(r["id"]) ?? 0,
        tenant_id: this.safeNum(r["tenant_id"]),
        session_id: this.safeStr(r["session_id"]),
        key: this.safeStr(r["key"]) ?? "",
        state,
        updated_at: this.safeStr(r["updated_at"])
      };
    });
  }

  /**
   * Returns a booking session by ID.
   * Table: booking_sessions (id, tenant_id, client_id, status, service, data JSON, created_at, updated_at)
   */
  async getBookingSession(sessionId: string | number, clientKey: string): Promise<DbBookingSession | null> {
    const rows = await this.query<Record<string, unknown>>(
      `SELECT id, tenant_id, client_id, status, service, data, created_at, updated_at
       FROM booking_sessions WHERE id = ? LIMIT 1`,
      [sessionId],
      clientKey
    );
    if (rows.length === 0) return null;
    const r = rows[0];
    let data: Record<string, unknown> = {};
    try {
      const raw = this.safeStr(r["data"]);
      if (raw) data = JSON.parse(raw) as Record<string, unknown>;
    } catch {
      // keep empty
    }
    return {
      id: this.safeNum(r["id"]) ?? 0,
      tenant_id: this.safeNum(r["tenant_id"]),
      client_id: this.safeNum(r["client_id"]),
      status: this.safeStr(r["status"]),
      service: this.safeStr(r["service"]),
      data_summary: data,
      created_at: this.safeStr(r["created_at"]),
      updated_at: this.safeStr(r["updated_at"])
    };
  }

  /**
   * Returns recent/upcoming appointments for a tenant.
   * Table: appointments (id, tenant_id, client_id, scheduled_at, status, service, notes)
   * Also joins clients or contacts for client name.
   */
  async getRecentAppointments(
    tenantId: number | null,
    limit: number,
    clientKey: string
  ): Promise<DbAppointment[]> {
    const safeLimit = Math.max(1, Math.min(limit, this.config.maxRowsPerQuery));
    const sql = tenantId !== null
      ? `SELECT a.id, a.tenant_id, c.name AS client_name, a.scheduled_at, a.status, a.service, a.notes
         FROM appointments a
         LEFT JOIN contacts c ON c.id = a.client_id
         WHERE a.tenant_id = ?
         ORDER BY a.scheduled_at DESC
         LIMIT ${safeLimit}`
      : `SELECT a.id, a.tenant_id, c.name AS client_name, a.scheduled_at, a.status, a.service, a.notes
         FROM appointments a
         LEFT JOIN contacts c ON c.id = a.client_id
         ORDER BY a.scheduled_at DESC
         LIMIT ${safeLimit}`;

    const params = tenantId !== null ? [tenantId] : [];
    const rows = await this.query<Record<string, unknown>>(sql, params, clientKey);
    return rows.map((r) => ({
      id: this.safeNum(r["id"]) ?? 0,
      tenant_id: this.safeNum(r["tenant_id"]),
      client_name: this.safeStr(r["client_name"]),
      scheduled_at: this.safeStr(r["scheduled_at"]),
      status: this.safeStr(r["status"]),
      service: this.safeStr(r["service"]),
      notes: this.safeStr(r["notes"])
    }));
  }

  /**
   * Returns a project summary including task stats.
   * Uses mmcriativos tables: projects, clients, services, project_tasks.
   */
  async getProjectSummary(slugOrId: string, clientKey: string): Promise<DbProjectSummary | null> {
    const isNumeric = /^\d+$/.test(slugOrId);
    const whereClause = isNumeric ? "p.id = ?" : "p.slug = ?";

    const rows = await this.query<Record<string, unknown>>(
      `SELECT p.id, p.name, p.slug, p.summary, p.finished_at, p.created_at,
              cl.name AS client_name,
              sv.name AS service_name,
              COUNT(pt.id) AS tasks_total,
              SUM(CASE WHEN pt.status = 'pending' THEN 1 ELSE 0 END) AS tasks_pending,
              SUM(CASE WHEN pt.status = 'in_progress' THEN 1 ELSE 0 END) AS tasks_in_progress,
              SUM(CASE WHEN pt.status = 'done' THEN 1 ELSE 0 END) AS tasks_done
       FROM projects p
       LEFT JOIN clients cl ON cl.id = p.client_id
       LEFT JOIN services sv ON sv.id = p.service_id
       LEFT JOIN project_tasks pt ON pt.project_id = p.id
       WHERE ${whereClause}
       GROUP BY p.id
       LIMIT 1`,
      [slugOrId],
      clientKey
    );

    if (rows.length === 0) return null;
    const r = rows[0];
    return {
      id: this.safeNum(r["id"]) ?? 0,
      name: this.safeStr(r["name"]) ?? "",
      slug: this.safeStr(r["slug"]) ?? "",
      summary: this.safeStr(r["summary"]),
      client_name: this.safeStr(r["client_name"]),
      service_name: this.safeStr(r["service_name"]),
      finished_at: this.safeStr(r["finished_at"]),
      tasks_total: this.safeNum(r["tasks_total"]) ?? 0,
      tasks_pending: this.safeNum(r["tasks_pending"]) ?? 0,
      tasks_in_progress: this.safeNum(r["tasks_in_progress"]) ?? 0,
      tasks_done: this.safeNum(r["tasks_done"]) ?? 0,
      created_at: this.safeStr(r["created_at"])
    };
  }

  /**
   * Returns pending/in-progress tasks with project and client context.
   * Uses mmcriativos tables: project_tasks, projects, clients, users.
   */
  async getPendingTasks(
    options: {
      projectSlug?: string;
      status?: "pending" | "in_progress" | "all";
      limit?: number;
    },
    clientKey: string
  ): Promise<DbPendingTask[]> {
    const safeLimit = Math.max(1, Math.min(options.limit ?? 20, this.config.maxRowsPerQuery));
    const status = options.status ?? "pending";

    const conditions: string[] = [];
    const params: (string | number)[] = [];

    if (status !== "all") {
      conditions.push("pt.status = ?");
      params.push(status);
    } else {
      conditions.push("pt.status IN ('pending', 'in_progress')");
    }

    if (options.projectSlug) {
      conditions.push("p.slug = ?");
      params.push(options.projectSlug);
    }

    const whereClause = conditions.length > 0 ? `WHERE ${conditions.join(" AND ")}` : "";

    const rows = await this.query<Record<string, unknown>>(
      `SELECT pt.id, pt.title, pt.description, pt.status, pt.created_at,
              p.name AS project_name, p.slug AS project_slug,
              cl.name AS client_name,
              u.name AS assignee_name
       FROM project_tasks pt
       LEFT JOIN projects p ON p.id = pt.project_id
       LEFT JOIN clients cl ON cl.id = p.client_id
       LEFT JOIN users u ON u.id = pt.assigned_to
       ${whereClause}
       ORDER BY pt.created_at DESC
       LIMIT ${safeLimit}`,
      params,
      clientKey
    );

    return rows.map((r) => ({
      id: this.safeNum(r["id"]) ?? 0,
      title: this.safeStr(r["title"]) ?? "",
      description: this.safeStr(r["description"]),
      status: this.safeStr(r["status"]) ?? "pending",
      project_name: this.safeStr(r["project_name"]),
      project_slug: this.safeStr(r["project_slug"]),
      client_name: this.safeStr(r["client_name"]),
      assignee_name: this.safeStr(r["assignee_name"]),
      created_at: this.safeStr(r["created_at"])
    }));
  }

  /**
   * Returns saved preferences for a tenant.
   * Table: user_preferences (id, tenant_id, key, value, updated_at)
   */
  async getUserPreferences(
    tenantId: number,
    keyFilter: string | null,
    clientKey: string
  ): Promise<DbUserPreference[]> {
    const safeLimit = Math.max(1, this.config.maxRowsPerQuery);
    const sql = keyFilter
      ? `SELECT \`key\`, value, tenant_id, updated_at
         FROM user_preferences WHERE tenant_id = ? AND \`key\` LIKE ? LIMIT ${safeLimit}`
      : `SELECT \`key\`, value, tenant_id, updated_at
         FROM user_preferences WHERE tenant_id = ? LIMIT ${safeLimit}`;

    const params = keyFilter
      ? [tenantId, `%${keyFilter}%`]
      : [tenantId];

    const rows = await this.query<Record<string, unknown>>(sql, params, clientKey);
    return rows.map((r) => ({
      key: this.safeStr(r["key"]) ?? "",
      value: this.safeStr(r["value"]),
      tenant_id: this.safeNum(r["tenant_id"]),
      updated_at: this.safeStr(r["updated_at"])
    }));
  }

  /**
   * Returns Vee MCP call execution history.
   * Table: vee_mcp_calls (confirmed in mmcriativos).
   */
  async getAgentExecutionHistory(
    options: {
      toolName?: string;
      status?: string;
      onlyWrites?: boolean;
      limit?: number;
    },
    clientKey: string
  ): Promise<DbAgentExecutionEntry[]> {
    const safeLimit = Math.max(1, Math.min(options.limit ?? 20, this.config.maxRowsPerQuery));
    const conditions: string[] = [];
    const params: (string | number)[] = [];

    if (options.toolName) {
      conditions.push("tool_name LIKE ?");
      params.push(`%${options.toolName}%`);
    }
    if (options.status) {
      conditions.push("status = ?");
      params.push(options.status);
    }
    if (options.onlyWrites) {
      conditions.push("is_write = 1");
    }

    const whereClause = conditions.length > 0 ? `WHERE ${conditions.join(" AND ")}` : "";

    const rows = await this.query<Record<string, unknown>>(
      `SELECT id, call_id, tool_name, status, permission_level, is_write, duration_ms, error_message, created_at
       FROM vee_mcp_calls
       ${whereClause}
       ORDER BY created_at DESC
       LIMIT ${safeLimit}`,
      params,
      clientKey
    );

    return rows.map((r) => ({
      id: this.safeNum(r["id"]) ?? 0,
      call_id: this.safeStr(r["call_id"]) ?? "",
      tool_name: this.safeStr(r["tool_name"]) ?? "",
      status: this.safeStr(r["status"]) ?? "",
      permission_level: this.safeStr(r["permission_level"]),
      is_write: this.safeBool(r["is_write"]),
      duration_ms: this.safeNum(r["duration_ms"]),
      error_message: this.safeStr(r["error_message"]),
      created_at: this.safeStr(r["created_at"])
    }));
  }

  async inspectSchemaObject(
    objectName: string,
    clientKey: string,
    options?: { auditMode?: boolean }
  ): Promise<DbSchemaObjectInspection> {
    const normalizedObjectName = this.normalizeIdentifier(objectName, "object_name");

    const objectRows = await this.query<Record<string, unknown>>(
      `SELECT TABLE_NAME, TABLE_TYPE, ENGINE, TABLE_ROWS, CREATE_TIME, UPDATE_TIME
       FROM information_schema.TABLES
       WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ?
       LIMIT 1`,
      [this.config.database, normalizedObjectName],
      clientKey,
      { auditMode: options?.auditMode, maxRowsOverride: 1 }
    );

    if (objectRows.length === 0) {
      return {
        object_name: normalizedObjectName,
        object_type: "table",
        exists: false,
        engine: null,
        estimated_rows: null,
        created_at: null,
        updated_at: null,
        columns: [],
        primary_key: [],
        foreign_keys: []
      };
    }

    const objectRow = objectRows[0];
    const rawType = this.safeStr(objectRow["TABLE_TYPE"]) ?? "";
    const objectType: DbSchemaObjectType = rawType.toUpperCase() === "VIEW" ? "view" : "table";

    const foreignKeyRows = await this.query<Record<string, unknown>>(
      `SELECT k.COLUMN_NAME, k.REFERENCED_TABLE_NAME, k.REFERENCED_COLUMN_NAME, k.CONSTRAINT_NAME, rc.UPDATE_RULE, rc.DELETE_RULE
       FROM information_schema.KEY_COLUMN_USAGE k
       LEFT JOIN information_schema.REFERENTIAL_CONSTRAINTS rc
         ON rc.CONSTRAINT_SCHEMA = k.CONSTRAINT_SCHEMA
        AND rc.CONSTRAINT_NAME = k.CONSTRAINT_NAME
        AND rc.TABLE_NAME = k.TABLE_NAME
       WHERE k.TABLE_SCHEMA = ?
         AND k.TABLE_NAME = ?
         AND k.REFERENCED_TABLE_NAME IS NOT NULL
       ORDER BY k.CONSTRAINT_NAME, k.ORDINAL_POSITION`,
      [this.config.database, normalizedObjectName],
      clientKey,
      { auditMode: options?.auditMode, maxRowsOverride: 500 }
    );

    const foreignKeys: DbSchemaForeignKey[] = foreignKeyRows
      .map((row) => {
        const columnName = this.safeStr(row["COLUMN_NAME"]);
        const referencedTable = this.safeStr(row["REFERENCED_TABLE_NAME"]);
        const referencedColumn = this.safeStr(row["REFERENCED_COLUMN_NAME"]);
        const constraintName = this.safeStr(row["CONSTRAINT_NAME"]);
        if (!columnName || !referencedTable || !referencedColumn || !constraintName) {
          return null;
        }
        return {
          constraint_name: constraintName,
          column_name: columnName,
          referenced_table: referencedTable,
          referenced_column: referencedColumn,
          update_rule: this.safeStr(row["UPDATE_RULE"]),
          delete_rule: this.safeStr(row["DELETE_RULE"])
        };
      })
      .filter((item): item is DbSchemaForeignKey => item !== null);

    const fkColumnSet = new Set(foreignKeys.map((fk) => fk.column_name));

    const columnRows = await this.query<Record<string, unknown>>(
      `SELECT ORDINAL_POSITION, COLUMN_NAME, DATA_TYPE, COLUMN_TYPE, IS_NULLABLE, COLUMN_DEFAULT, EXTRA, COLUMN_KEY
       FROM information_schema.COLUMNS
       WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ?
       ORDER BY ORDINAL_POSITION`,
      [this.config.database, normalizedObjectName],
      clientKey,
      { auditMode: options?.auditMode, maxRowsOverride: 1000 }
    );

    const columns: DbSchemaColumn[] = columnRows
      .map((row) => {
        const columnName = this.safeStr(row["COLUMN_NAME"]);
        if (!columnName) {
          return null;
        }
        const key = this.safeStr(row["COLUMN_KEY"]);
        return {
          ordinal_position: this.safeNum(row["ORDINAL_POSITION"]) ?? 0,
          name: columnName,
          data_type: this.safeStr(row["DATA_TYPE"]) ?? "",
          column_type: this.safeStr(row["COLUMN_TYPE"]) ?? "",
          nullable: (this.safeStr(row["IS_NULLABLE"]) ?? "").toUpperCase() === "YES",
          default_value: this.safeStr(row["COLUMN_DEFAULT"]),
          extra: this.safeStr(row["EXTRA"]),
          key,
          is_primary_key: key === "PRI",
          is_foreign_key: fkColumnSet.has(columnName)
        };
      })
      .filter((item): item is DbSchemaColumn => item !== null)
      .sort((a, b) => a.ordinal_position - b.ordinal_position);

    const primaryKey = columns.filter(column => column.is_primary_key).map(column => column.name);

    return {
      object_name: normalizedObjectName,
      object_type: objectType,
      exists: true,
      engine: this.safeStr(objectRow["ENGINE"]),
      estimated_rows: this.safeNum(objectRow["TABLE_ROWS"]),
      created_at: this.safeStr(objectRow["CREATE_TIME"]),
      updated_at: this.safeStr(objectRow["UPDATE_TIME"]),
      columns,
      primary_key: primaryKey,
      foreign_keys: foreignKeys
    };
  }

  async listSchemaObjectIndexes(
    objectName: string,
    clientKey: string,
    options?: { auditMode?: boolean }
  ): Promise<{
    object_name: string;
    object_type: DbSchemaObjectType;
    exists: boolean;
    indexes: DbSchemaIndex[];
  }> {
    const inspection = await this.inspectSchemaObject(objectName, clientKey, options);
    if (!inspection.exists) {
      return {
        object_name: inspection.object_name,
        object_type: inspection.object_type,
        exists: false,
        indexes: []
      };
    }

    const indexRows = await this.query<Record<string, unknown>>(
      `SELECT INDEX_NAME, NON_UNIQUE, INDEX_TYPE, CARDINALITY, COLUMN_NAME, SEQ_IN_INDEX, COLLATION, SUB_PART
       FROM information_schema.STATISTICS
       WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ?
       ORDER BY INDEX_NAME, SEQ_IN_INDEX`,
      [this.config.database, inspection.object_name],
      clientKey,
      { auditMode: options?.auditMode, maxRowsOverride: 1000 }
    );

    const byIndex = new Map<string, DbSchemaIndex>();
    for (const row of indexRows) {
      const indexName = this.safeStr(row["INDEX_NAME"]);
      const columnName = this.safeStr(row["COLUMN_NAME"]);
      if (!indexName || !columnName) {
        continue;
      }

      let index = byIndex.get(indexName);
      if (!index) {
        index = {
          name: indexName,
          unique: this.safeNum(row["NON_UNIQUE"]) === 0,
          index_type: this.safeStr(row["INDEX_TYPE"]),
          cardinality: this.safeNum(row["CARDINALITY"]),
          columns: []
        };
        byIndex.set(indexName, index);
      }

      index.columns.push({
        name: columnName,
        seq_in_index: this.safeNum(row["SEQ_IN_INDEX"]) ?? 0,
        collation: this.safeStr(row["COLLATION"]),
        sub_part: this.safeNum(row["SUB_PART"])
      });
    }

    const indexes = [...byIndex.values()]
      .map(index => ({
        ...index,
        columns: index.columns.sort((a, b) => a.seq_in_index - b.seq_in_index)
      }))
      .sort((a, b) => a.name.localeCompare(b.name));

    return {
      object_name: inspection.object_name,
      object_type: inspection.object_type,
      exists: true,
      indexes
    };
  }

  async listSchemaObjectRelations(
    objectName: string,
    clientKey: string,
    options?: { auditMode?: boolean }
  ): Promise<DbSchemaObjectRelations & { exists: boolean; object_type: DbSchemaObjectType }> {
    const inspection = await this.inspectSchemaObject(objectName, clientKey, options);
    if (!inspection.exists) {
      return {
        object_name: inspection.object_name,
        object_type: inspection.object_type,
        exists: false,
        outgoing: [],
        incoming: []
      };
    }

    const incomingRows = await this.query<Record<string, unknown>>(
      `SELECT k.TABLE_NAME AS SOURCE_TABLE, k.COLUMN_NAME AS SOURCE_COLUMN, k.REFERENCED_COLUMN_NAME AS REFERENCED_COLUMN, k.CONSTRAINT_NAME, rc.UPDATE_RULE, rc.DELETE_RULE
       FROM information_schema.KEY_COLUMN_USAGE k
       LEFT JOIN information_schema.REFERENTIAL_CONSTRAINTS rc
         ON rc.CONSTRAINT_SCHEMA = k.CONSTRAINT_SCHEMA
        AND rc.CONSTRAINT_NAME = k.CONSTRAINT_NAME
        AND rc.TABLE_NAME = k.TABLE_NAME
       WHERE k.TABLE_SCHEMA = ?
         AND k.REFERENCED_TABLE_SCHEMA = ?
         AND k.REFERENCED_TABLE_NAME = ?
         AND k.REFERENCED_TABLE_NAME IS NOT NULL
       ORDER BY k.TABLE_NAME, k.CONSTRAINT_NAME, k.ORDINAL_POSITION`,
      [this.config.database, this.config.database, inspection.object_name],
      clientKey,
      { auditMode: options?.auditMode, maxRowsOverride: 1000 }
    );

    const incoming = incomingRows
      .map((row) => {
        const sourceTable = this.safeStr(row["SOURCE_TABLE"]);
        const sourceColumn = this.safeStr(row["SOURCE_COLUMN"]);
        const referencedColumn = this.safeStr(row["REFERENCED_COLUMN"]);
        const constraintName = this.safeStr(row["CONSTRAINT_NAME"]);
        if (!sourceTable || !sourceColumn || !referencedColumn || !constraintName) {
          return null;
        }
        return {
          source_table: sourceTable,
          source_column: sourceColumn,
          referenced_column: referencedColumn,
          constraint_name: constraintName,
          update_rule: this.safeStr(row["UPDATE_RULE"]),
          delete_rule: this.safeStr(row["DELETE_RULE"])
        };
      })
      .filter(
        (item): item is DbSchemaObjectRelations["incoming"][number] =>
          item !== null
      );

    return {
      object_name: inspection.object_name,
      object_type: inspection.object_type,
      exists: true,
      outgoing: inspection.foreign_keys,
      incoming
    };
  }

  async listSchemaObjectsMetadata(
    objectNames: string[],
    clientKey: string,
    options?: { auditMode?: boolean }
  ): Promise<DbSchemaObjectInspection[]> {
    const normalizedNames = [...new Set(objectNames.map(name => this.normalizeIdentifier(name, "object_name")))];
    const results: DbSchemaObjectInspection[] = [];
    for (const objectName of normalizedNames) {
      const inspection = await this.inspectSchemaObject(objectName, clientKey, options);
      results.push(inspection);
    }
    return results.sort((a, b) => a.object_name.localeCompare(b.object_name));
  }

  async discoverRelationships(
    objectNames: string[],
    clientKey: string,
    options?: {
      includeHeuristics?: boolean;
      auditMode?: boolean;
    }
  ): Promise<DbRelationshipDiscoveryEdge[]> {
    const normalizedNames = [...new Set(objectNames.map(name => this.normalizeIdentifier(name, "object_name")))];
    if (normalizedNames.length === 0) {
      return [];
    }

    const placeholders = normalizedNames.map(() => "?").join(", ");
    const fkRows = await this.query<Record<string, unknown>>(
      `SELECT TABLE_NAME AS SOURCE_TABLE, COLUMN_NAME AS SOURCE_COLUMN, REFERENCED_TABLE_NAME AS TARGET_TABLE, REFERENCED_COLUMN_NAME AS TARGET_COLUMN, CONSTRAINT_NAME
       FROM information_schema.KEY_COLUMN_USAGE
       WHERE TABLE_SCHEMA = ?
         AND REFERENCED_TABLE_NAME IS NOT NULL
         AND TABLE_NAME IN (${placeholders})
         AND REFERENCED_TABLE_NAME IN (${placeholders})
       ORDER BY TABLE_NAME, COLUMN_NAME`,
      [this.config.database, ...normalizedNames, ...normalizedNames],
      clientKey,
      { auditMode: options?.auditMode, maxRowsOverride: 2000 }
    );

    const byKey = new Map<string, DbRelationshipDiscoveryEdge>();
    for (const row of fkRows) {
      const sourceTable = this.safeStr(row["SOURCE_TABLE"]);
      const sourceColumn = this.safeStr(row["SOURCE_COLUMN"]);
      const targetTable = this.safeStr(row["TARGET_TABLE"]);
      const targetColumn = this.safeStr(row["TARGET_COLUMN"]);
      if (!sourceTable || !sourceColumn || !targetTable || !targetColumn) {
        continue;
      }
      const key = `${sourceTable}.${sourceColumn}->${targetTable}.${targetColumn}`;
      byKey.set(key, {
        source_table: sourceTable,
        source_column: sourceColumn,
        target_table: targetTable,
        target_column: targetColumn,
        relation_kind: "foreign_key",
        confidence: "high",
        constraint_name: this.safeStr(row["CONSTRAINT_NAME"])
      });
    }

    if (options?.includeHeuristics) {
      const heuristicRows = await this.query<Record<string, unknown>>(
        `SELECT TABLE_NAME AS SOURCE_TABLE, COLUMN_NAME AS SOURCE_COLUMN
         FROM information_schema.COLUMNS
         WHERE TABLE_SCHEMA = ?
           AND TABLE_NAME IN (${placeholders})
           AND COLUMN_NAME LIKE '%\\_id' ESCAPE '\\\\'
         ORDER BY TABLE_NAME, ORDINAL_POSITION`,
        [this.config.database, ...normalizedNames],
        clientKey,
        { auditMode: options.auditMode, maxRowsOverride: 5000 }
      );

      const nameSet = new Set(normalizedNames);
      for (const row of heuristicRows) {
        const sourceTable = this.safeStr(row["SOURCE_TABLE"]);
        const sourceColumn = this.safeStr(row["SOURCE_COLUMN"]);
        if (!sourceTable || !sourceColumn || sourceColumn === "id" || !sourceColumn.endsWith("_id")) {
          continue;
        }

        const base = sourceColumn.slice(0, -3);
        const candidates = [...new Set([base, `${base}s`, `${base}es`, base.endsWith("y") ? `${base.slice(0, -1)}ies` : ""])]
          .filter(Boolean);

        for (const candidate of candidates) {
          if (!nameSet.has(candidate) || candidate === sourceTable) {
            continue;
          }
          const key = `${sourceTable}.${sourceColumn}->${candidate}.id`;
          if (byKey.has(key)) {
            continue;
          }
          byKey.set(key, {
            source_table: sourceTable,
            source_column: sourceColumn,
            target_table: candidate,
            target_column: "id",
            relation_kind: "heuristic",
            confidence: "medium",
            constraint_name: null
          });
        }
      }
    }

    return [...byKey.values()].sort((a, b) => {
      const left = `${a.source_table}.${a.source_column}`;
      const right = `${b.source_table}.${b.source_column}`;
      if (left === right) {
        return `${a.target_table}.${a.target_column}`.localeCompare(`${b.target_table}.${b.target_column}`);
      }
      return left.localeCompare(right);
    });
  }

  async closePool(): Promise<void> {
    if (this.pool) {
      await this.pool.end();
      this.pool = null;
    }
  }

  // ─── Generic structured query ─────────────────────────────────────────────

  /**
   * Executes a structured SELECT query against allowlisted tables / named views.
   * Validates table, columns, JOIN tables, and WHERE operators before building SQL.
   * All values are bound as prepared-statement parameters — no SQL injection possible.
   */
  async executeStructuredQuery(
    input: StructuredQueryInput,
    clientKey: string,
    options?: { auditMode?: boolean }
  ): Promise<{
    table: string;
    resolved_table: string;
    columns_requested: string[] | null;
    sensitive_query: boolean;
    involved_tables: string[];
    performance: {
      execution_ms: number;
      max_rows_per_query: number;
      rate_limit_per_minute: number;
    };
    pagination: {
      limit: number;
      offset: number;
      returned_rows: number;
      has_more_possible: boolean;
      next_offset: number | null;
    };
    result_schema: string[];
    total: number;
    rows: Record<string, unknown>[];
  }> {
    this.assertConfigured();

    const built = buildSelectQuery(input, this.config.maxRowsPerQuery);
    // buildSelectQuery throws on any policy violation — no SQL reaches MySQL.

    const maxPerMinute = options?.auditMode
      ? this.config.auditRateLimitPerMinute ?? this.config.rateLimitPerMinute
      : this.config.rateLimitPerMinute;
    checkRateLimit(clientKey, maxPerMinute);
    const startedAt = process.hrtime.bigint();

    const [rawRows] = await this.getPool().execute(
      built.sql,
      built.params
    ) as [RowDataPacket[], FieldPacket[]];

    const limitedRows = (rawRows as unknown as Record<string, unknown>[]).slice(
      0,
      this.config.maxRowsPerQuery
    );
    const rows = limitedRows.map(row => this.normalizeRow(row));

    const executionMs = Number(process.hrtime.bigint() - startedAt) / 1_000_000;
    const appliedLimit = built.appliedLimit ?? this.config.maxRowsPerQuery;
    const appliedOffset = built.appliedOffset ?? 0;
    const hasMorePossible = rows.length >= appliedLimit;
    const resultSchema =
      rows.length > 0
        ? [...new Set(Object.keys(rows[0] ?? {}).map(key => String(key)))].sort()
        : [];

    return {
      table: input.table,
      resolved_table: built.resolvedTable,
      columns_requested: input.columns ?? null,
      sensitive_query: built.sensitive,
      involved_tables: built.involvedTables ?? [built.resolvedTable],
      performance: {
        execution_ms: Number(executionMs.toFixed(3)),
        max_rows_per_query: this.config.maxRowsPerQuery,
        rate_limit_per_minute: maxPerMinute
      },
      pagination: {
        limit: appliedLimit,
        offset: appliedOffset,
        returned_rows: rows.length,
        has_more_possible: hasMorePossible,
        next_offset: hasMorePossible ? appliedOffset + rows.length : null
      },
      result_schema: resultSchema,
      total: rows.length,
      rows
    };
  }

  private normalizeRow(row: Record<string, unknown>): Record<string, unknown> {
    const normalized: Record<string, unknown> = {};
    const keys = Object.keys(row).sort((a, b) => a.localeCompare(b));

    for (const key of keys) {
      normalized[key] = this.normalizeValue(row[key]);
    }

    return normalized;
  }

  private normalizeValue(value: unknown): unknown {
    if (value instanceof Date) {
      return value.toISOString();
    }

    if (typeof value === "bigint") {
      return value.toString();
    }

    if (Array.isArray(value)) {
      return value.map(item => this.normalizeValue(item));
    }

    if (value && typeof value === "object") {
      const source = value as Record<string, unknown>;
      const normalized: Record<string, unknown> = {};
      const keys = Object.keys(source).sort((a, b) => a.localeCompare(b));
      for (const key of keys) {
        normalized[key] = this.normalizeValue(source[key]);
      }
      return normalized;
    }

    return value;
  }
}
