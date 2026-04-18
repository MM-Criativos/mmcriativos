import mysql from "mysql2/promise";
import type { FieldPacket, RowDataPacket } from "mysql2/promise";

// ─── Config ──────────────────────────────────────────────────────────────────

export type DatabaseReadOnlyAdapterConfig = {
  host: string;
  port: number;
  user: string;
  password: string;
  database: string;
  maxRowsPerQuery: number;
  rateLimitPerMinute: number;
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
      database: config.database.trim()
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
    clientKey: string
  ): Promise<T[]> {
    this.assertConfigured();
    checkRateLimit(clientKey, this.config.rateLimitPerMinute);

    const [rows] = await this.getPool().execute(sql, params) as [RowDataPacket[], FieldPacket[]];
    const result = rows as unknown as T[];
    return result.slice(0, this.config.maxRowsPerQuery);
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
       LIMIT ?`,
      [sessionId, safeLimit],
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
    const sql = key
      ? `SELECT id, tenant_id, session_id, \`key\`, state, updated_at
         FROM agent_context_states WHERE session_id = ? AND \`key\` = ? LIMIT ?`
      : `SELECT id, tenant_id, session_id, \`key\`, state, updated_at
         FROM agent_context_states WHERE session_id = ? LIMIT ?`;

    const params = key
      ? [sessionId, key, this.config.maxRowsPerQuery]
      : [sessionId, this.config.maxRowsPerQuery];

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
         LIMIT ?`
      : `SELECT a.id, a.tenant_id, c.name AS client_name, a.scheduled_at, a.status, a.service, a.notes
         FROM appointments a
         LEFT JOIN contacts c ON c.id = a.client_id
         ORDER BY a.scheduled_at DESC
         LIMIT ?`;

    const params = tenantId !== null ? [tenantId, safeLimit] : [safeLimit];
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
    params.push(safeLimit);

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
       LIMIT ?`,
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
    const sql = keyFilter
      ? `SELECT \`key\`, value, tenant_id, updated_at
         FROM user_preferences WHERE tenant_id = ? AND \`key\` LIKE ? LIMIT ?`
      : `SELECT \`key\`, value, tenant_id, updated_at
         FROM user_preferences WHERE tenant_id = ? LIMIT ?`;

    const params = keyFilter
      ? [tenantId, `%${keyFilter}%`, this.config.maxRowsPerQuery]
      : [tenantId, this.config.maxRowsPerQuery];

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
    params.push(safeLimit);

    const rows = await this.query<Record<string, unknown>>(
      `SELECT id, call_id, tool_name, status, permission_level, is_write, duration_ms, error_message, created_at
       FROM vee_mcp_calls
       ${whereClause}
       ORDER BY created_at DESC
       LIMIT ?`,
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

  async closePool(): Promise<void> {
    if (this.pool) {
      await this.pool.end();
      this.pool = null;
    }
  }
}
