import { randomUUID } from "node:crypto";
import mysql from "mysql2/promise";
import type { FieldPacket, RowDataPacket } from "mysql2/promise";
import {
  buildWriteQuery,
  buildSnapshotQuery,
  type StructuredWriteInput,
  type WhereCondition
} from "./structuredQueryBuilder.js";

// ─── Config ──────────────────────────────────────────────────────────────────

export type DatabaseWriteAdapterConfig = {
  host: string;
  port: number;
  user: string;
  password: string;
  database: string;
  enabled: boolean;
};

// ─── Result types ─────────────────────────────────────────────────────────────

export type DbWriteResult<T = Record<string, unknown>> = {
  ok: boolean;
  affected_rows: number;
  before: T | null;
  after: T | null;
};

export type DbProjectStatusBefore = {
  id: number;
  name: string;
  slug: string;
  status: string | null;
  finished_at: string | null;
};

export type DbTaskBefore = {
  id: number;
  title: string;
  status: string;
  progress_notes: string | null;
  completed_at: string | null;
};

export type DbCreatedTask = {
  id: number;
  title: string;
  status: string;
  project_id: number | null;
  description: string | null;
  assigned_to: number | null;
  created_at: string | null;
};

export type DbExecutionNote = {
  id: number;
  note_id: string;
  note_type: string;
  tool_name: string | null;
  session_id: string | null;
  project_id: number | null;
  title: string;
  created_at: string | null;
};

export type DbContextStateBefore = {
  id: number | null;
  session_id: string;
  key: string;
  state: unknown;
  updated_at: string | null;
};

export type DbIncident = {
  id: number;
  incident_id: string;
  title: string;
  severity: string;
  status: string;
  affected_service: string | null;
  created_at: string | null;
};

// ─── Layer 3 traceability types ───────────────────────────────────────────────

export type DbProjectEvent = {
  id: number;
  event_id: string;
  entity_type: string;
  entity_id: number;
  actor: string;
  action: string;
  created_at: string | null;
};

export type DbStatusHistory = {
  id: number;
  entity_type: string;
  entity_id: number;
  old_status: string | null;
  new_status: string;
  changed_by: string;
  reason: string | null;
  created_at: string | null;
};

export type DbOperationalDecision = {
  id: number;
  decision_id: string;
  title: string;
  actor: string;
  project_id: number | null;
  created_at: string | null;
};

export type DbBlockEntry = {
  id: number;
  entity_type: string;
  entity_id: number;
  reason: string;
  blocked_by: string;
  blocked_at: string | null;
};

export type DbStructuredWriteResult = DbWriteResult<Record<string, unknown>> & {
  table: string;
  resolved_table: string;
  operation: "INSERT" | "UPDATE" | "UPSERT";
  reason: string;
  normalized_data_keys: string[];
  required_fields_checked: string[];
  auto_filled_fields: string[];
};

type AutoFillStrategy = "uuid" | "now";

const DEFAULT_AUTO_FILL_FIELDS: Record<string, Record<string, AutoFillStrategy>> = {
  vee_operational_decisions: { decision_id: "uuid" },
  vee_project_events: { event_id: "uuid" },
  vee_execution_notes: { note_id: "uuid" },
  vee_incidents: { incident_id: "uuid" }
};

const DEFAULT_REQUIRED_FIELDS: Record<string, string[]> = {
  vee_execution_notes: ["note_type", "title", "content"],
  vee_project_events: ["entity_type", "entity_id", "actor", "action"],
  vee_status_history: ["entity_type", "entity_id", "new_status", "changed_by"],
  vee_operational_decisions: ["title", "context", "rationale", "outcome", "actor"],
  vee_blocks: ["entity_type", "entity_id", "reason", "blocked_by"]
};

const UPSERT_IDENTITY_FALLBACK_KEYS = ["id", "decision_id", "event_id", "note_id", "incident_id", "slug"];

const CONFIG_REQUIRED_FIELDS = parseRequiredFieldsByTableFromEnv();
const CONFIG_AUTO_FILL_FIELDS = parseAutoFillFieldsByTableFromEnv();

// ─── Adapter ──────────────────────────────────────────────────────────────────

export class DatabaseWriteAdapter {
  private readonly config: DatabaseWriteAdapterConfig;
  private pool: mysql.Pool | null = null;

  constructor(config: DatabaseWriteAdapterConfig) {
    this.config = {
      ...config,
      host: config.host.trim(),
      user: config.user.trim(),
      database: config.database.trim()
    };
  }

  isConfigured(): boolean {
    return (
      this.config.enabled &&
      this.config.host.length > 0 &&
      this.config.user.length > 0 &&
      this.config.database.length > 0
    );
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

  private assertEnabled(): void {
    if (!this.config.enabled) {
      throw new Error(
        "DB writes are disabled. Set DB_WRITE_ENABLED=true to allow write operations."
      );
    }
    if (!this.config.host || !this.config.user || !this.config.database) {
      throw new Error(
        "DB_WRITE_HOST, DB_WRITE_USER, and DB_WRITE_DATABASE are required for write tools."
      );
    }
  }

  private async execute(
    sql: string,
    params: (string | number | boolean | null)[]
  ): Promise<mysql.ResultSetHeader> {
    const [result] = await this.getPool().execute(sql, params) as [mysql.ResultSetHeader, FieldPacket[]];
    return result;
  }

  private async queryOne<T>(
    sql: string,
    params: (string | number | boolean | null)[]
  ): Promise<T | null> {
    const [rows] = await this.getPool().execute(sql, params) as [RowDataPacket[], FieldPacket[]];
    return rows.length > 0 ? rows[0] as unknown as T : null;
  }

  private safeStr(v: unknown): string | null {
    if (v === null || v === undefined) return null;
    return String(v);
  }

  private safeNum(v: unknown): number | null {
    const n = Number(v);
    return Number.isFinite(n) ? n : null;
  }

  private normalizeStructuredWriteInput(input: StructuredWriteInput): {
    prepared: StructuredWriteInput;
    normalized_data_keys: string[];
    required_fields_checked: string[];
    auto_filled_fields: string[];
    identity_where: WhereCondition[] | null;
  } {
    const normalizedReason = input.reason.replace(/\s+/g, " ").trim();
    const tableKey = normalizeIdentifier(input.table) ?? input.table.trim().toLowerCase();

    const normalizedData: Record<string, unknown> = {};
    for (const [key, value] of Object.entries(input.data)) {
      normalizedData[key] = this.normalizeWriteValue(value);
    }

    const autoFilledFields: string[] = [];
    const autoFillConfig = getAutoFillStrategiesForTable(tableKey);
    for (const [field, strategy] of Object.entries(autoFillConfig)) {
      const current = normalizedData[field];
      if (current !== undefined && current !== null && current !== "") {
        continue;
      }
      normalizedData[field] = strategy === "uuid" ? randomUUID() : new Date().toISOString().slice(0, 19).replace("T", " ");
      autoFilledFields.push(field);
    }

    const requiredFieldsChecked = input.operation === "UPDATE" ? [] : getRequiredFieldsForTable(tableKey);
    const missingRequired = requiredFieldsChecked.filter((field) => {
      const value = normalizedData[field];
      return value === undefined || value === null || value === "";
    });
    if (missingRequired.length > 0) {
      throw new Error(
        `Missing required fields for "${input.table}" ${input.operation}: ${missingRequired.join(", ")}`
      );
    }

    const normalizedDataKeys = Object.keys(normalizedData).sort((a, b) => a.localeCompare(b));
    const identityWhere = this.buildIdentityWhereForData(normalizedData, input.upsertKey);

    return {
      prepared: {
        ...input,
        reason: normalizedReason,
        data: normalizedData
      },
      normalized_data_keys: normalizedDataKeys,
      required_fields_checked: requiredFieldsChecked,
      auto_filled_fields: autoFilledFields,
      identity_where: identityWhere
    };
  }

  private normalizeWriteValue(value: unknown): string | number | boolean | null {
    if (value === undefined || value === null) {
      return null;
    }
    if (typeof value === "string" || typeof value === "number" || typeof value === "boolean") {
      return value;
    }
    if (typeof value === "bigint") {
      return value.toString();
    }
    if (value instanceof Date) {
      return value.toISOString().slice(0, 19).replace("T", " ");
    }
    if (Array.isArray(value) || typeof value === "object") {
      return JSON.stringify(value);
    }
    throw new Error(`Unsupported value type for structured write: ${typeof value}`);
  }

  private buildIdentityWhereForData(
    data: Record<string, unknown>,
    upsertKey?: string[]
  ): WhereCondition[] | null {
    const selectedKeys: string[] = [];
    const explicitKeys = (upsertKey ?? []).filter((key) => key.trim().length > 0);

    if (
      explicitKeys.length > 0 &&
      explicitKeys.every((key) => {
        const value = data[key];
        return value !== undefined && value !== null && value !== "";
      })
    ) {
      selectedKeys.push(...explicitKeys);
    } else {
      for (const key of UPSERT_IDENTITY_FALLBACK_KEYS) {
        const value = data[key];
        if (value === undefined || value === null || value === "") {
          continue;
        }
        selectedKeys.push(key);
        break;
      }
    }

    if (selectedKeys.length === 0) {
      return null;
    }

    return selectedKeys.map((key) => ({
      column: key,
      operator: "=",
      value: data[key] as string | number | boolean | null
    }));
  }

  private async readSnapshot(
    table: string,
    where: WhereCondition[],
    columns: string[]
  ): Promise<Record<string, unknown> | null> {
    const snapshotColumns = [...new Set(columns.filter((column) => column.trim().length > 0))];
    const snap = buildSnapshotQuery(table, where, snapshotColumns.length > 0 ? snapshotColumns : undefined);
    return this.queryOne<Record<string, unknown>>(snap.sql, snap.params);
  }

  // ─── 1. update_project_status ─────────────────────────────────────────────

  /**
   * Updates the status of a project.
   * Valid statuses: active | paused | done | cancelled
   * Also sets finished_at when status = done, clears it otherwise.
   */
  async updateProjectStatus(
    slugOrId: string,
    status: "active" | "paused" | "done" | "cancelled",
    notes: string | null
  ): Promise<DbWriteResult<DbProjectStatusBefore>> {
    this.assertEnabled();

    const isNumeric = /^\d+$/.test(slugOrId);
    const whereClause = isNumeric ? "id = ?" : "slug = ?";

    const before = await this.queryOne<DbProjectStatusBefore>(
      `SELECT id, name, slug, status, finished_at FROM projects WHERE ${whereClause} LIMIT 1`,
      [slugOrId]
    );
    if (!before) throw new Error(`Project not found: ${slugOrId}`);

    const finishedAt = status === "done" ? new Date().toISOString().slice(0, 19).replace("T", " ") : null;

    const result = await this.execute(
      `UPDATE projects SET status = ?, finished_at = ? WHERE ${whereClause}`,
      [status, finishedAt, slugOrId]
    );

    const after = await this.queryOne<DbProjectStatusBefore>(
      `SELECT id, name, slug, status, finished_at FROM projects WHERE ${whereClause} LIMIT 1`,
      [slugOrId]
    );

    void notes; // reserved for future audit note

    return { ok: result.affectedRows > 0, affected_rows: result.affectedRows, before, after };
  }

  // ─── 2. create_internal_task ──────────────────────────────────────────────

  /**
   * Creates a new internal project task.
   * Links to an existing project via slugOrId.
   */
  async createInternalTask(params: {
    projectSlugOrId: string;
    title: string;
    description?: string;
    priority?: string;
    assignedTo?: number | null;
  }): Promise<DbWriteResult<DbCreatedTask>> {
    this.assertEnabled();

    const isNumeric = /^\d+$/.test(params.projectSlugOrId);
    const project = await this.queryOne<{ id: number }>(
      `SELECT id FROM projects WHERE ${isNumeric ? "id" : "slug"} = ? LIMIT 1`,
      [params.projectSlugOrId]
    );
    if (!project) throw new Error(`Project not found: ${params.projectSlugOrId}`);

    const result = await this.execute(
      `INSERT INTO project_tasks (project_id, title, description, status, assigned_to, progress_notes, created_at, updated_at)
       VALUES (?, ?, ?, 'pending', ?, ?, NOW(), NOW())`,
      [
        project.id,
        params.title.trim(),
        params.description ?? null,
        params.assignedTo ?? null,
        params.priority ? `[priority: ${params.priority}]` : null
      ]
    );

    const created = await this.queryOne<DbCreatedTask>(
      "SELECT id, title, status, project_id, description, assigned_to, created_at FROM project_tasks WHERE id = ? LIMIT 1",
      [result.insertId]
    );

    return { ok: result.affectedRows > 0, affected_rows: result.affectedRows, before: null, after: created };
  }

  // ─── 3. save_execution_note ───────────────────────────────────────────────

  /**
   * Saves a Vee execution note (diagnosis, fix attempt, summary, analysis).
   * Table: vee_execution_notes
   */
  async saveExecutionNote(params: {
    noteType: "diagnosis" | "fix_attempt" | "summary" | "analysis";
    title: string;
    content: string;
    toolName?: string;
    sessionId?: string;
    projectSlugOrId?: string;
    meta?: Record<string, unknown>;
  }): Promise<DbWriteResult<DbExecutionNote>> {
    this.assertEnabled();

    let projectId: number | null = null;
    if (params.projectSlugOrId) {
      const isNumeric = /^\d+$/.test(params.projectSlugOrId);
      const project = await this.queryOne<{ id: number }>(
        `SELECT id FROM projects WHERE ${isNumeric ? "id" : "slug"} = ? LIMIT 1`,
        [params.projectSlugOrId]
      );
      projectId = project?.id ?? null;
    }

    const noteId = randomUUID();
    const metaJson = params.meta ? JSON.stringify(params.meta) : null;

    const result = await this.execute(
      `INSERT INTO vee_execution_notes (note_id, note_type, tool_name, session_id, project_id, title, content, meta, created_at, updated_at)
       VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())`,
      [
        noteId,
        params.noteType,
        params.toolName ?? null,
        params.sessionId ?? null,
        projectId,
        params.title.trim(),
        params.content,
        metaJson
      ]
    );

    const created = await this.queryOne<DbExecutionNote>(
      "SELECT id, note_id, note_type, tool_name, session_id, project_id, title, created_at FROM vee_execution_notes WHERE id = ? LIMIT 1",
      [result.insertId]
    );

    return { ok: result.affectedRows > 0, affected_rows: result.affectedRows, before: null, after: created };
  }

  // ─── 4. update_context_state ──────────────────────────────────────────────

  /**
   * Upserts agent context state (key/value per session).
   * Table: agent_context_states
   */
  async updateContextState(params: {
    sessionId: string;
    key: string;
    state: unknown;
    tenantId?: number | null;
  }): Promise<DbWriteResult<DbContextStateBefore>> {
    this.assertEnabled();

    const before = await this.queryOne<DbContextStateBefore>(
      "SELECT id, session_id, `key`, state, updated_at FROM agent_context_states WHERE session_id = ? AND `key` = ? LIMIT 1",
      [params.sessionId, params.key]
    );

    const stateJson = typeof params.state === "string" ? params.state : JSON.stringify(params.state);

    let result: mysql.ResultSetHeader;
    if (before?.id) {
      result = await this.execute(
        "UPDATE agent_context_states SET state = ?, updated_at = NOW() WHERE id = ?",
        [stateJson, before.id]
      );
    } else {
      result = await this.execute(
        `INSERT INTO agent_context_states (tenant_id, session_id, \`key\`, state, created_at, updated_at)
         VALUES (?, ?, ?, ?, NOW(), NOW())`,
        [params.tenantId ?? null, params.sessionId, params.key, stateJson]
      );
    }

    const after = await this.queryOne<DbContextStateBefore>(
      "SELECT id, session_id, `key`, state, updated_at FROM agent_context_states WHERE session_id = ? AND `key` = ? LIMIT 1",
      [params.sessionId, params.key]
    );

    return { ok: result.affectedRows > 0, affected_rows: result.affectedRows, before, after };
  }

  // ─── 5. register_incident ─────────────────────────────────────────────────

  /**
   * Registers a new operational incident.
   * Table: vee_incidents
   */
  async registerIncident(params: {
    title: string;
    severity: "low" | "medium" | "high" | "critical";
    description: string;
    affectedService?: string;
    meta?: Record<string, unknown>;
  }): Promise<DbWriteResult<DbIncident>> {
    this.assertEnabled();

    const incidentId = randomUUID();
    const metaJson = params.meta ? JSON.stringify(params.meta) : null;

    const result = await this.execute(
      `INSERT INTO vee_incidents (incident_id, title, severity, status, description, affected_service, meta, created_at, updated_at)
       VALUES (?, ?, ?, 'open', ?, ?, ?, NOW(), NOW())`,
      [
        incidentId,
        params.title.trim(),
        params.severity,
        params.description,
        params.affectedService ?? null,
        metaJson
      ]
    );

    const created = await this.queryOne<DbIncident>(
      "SELECT id, incident_id, title, severity, status, affected_service, created_at FROM vee_incidents WHERE id = ? LIMIT 1",
      [result.insertId]
    );

    return { ok: result.affectedRows > 0, affected_rows: result.affectedRows, before: null, after: created };
  }

  // ─── 6. attach_task_to_project ────────────────────────────────────────────

  /**
   * Moves a task to a different project (or assigns project if unset).
   */
  async attachTaskToProject(
    taskId: number,
    projectSlugOrId: string
  ): Promise<DbWriteResult<{ task_id: number; old_project_id: number | null; new_project_id: number }>> {
    this.assertEnabled();

    const task = await this.queryOne<{ id: number; project_id: number | null; title: string }>(
      "SELECT id, project_id, title FROM project_tasks WHERE id = ? LIMIT 1",
      [taskId]
    );
    if (!task) throw new Error(`Task not found: ${taskId}`);

    const isNumeric = /^\d+$/.test(projectSlugOrId);
    const project = await this.queryOne<{ id: number }>(
      `SELECT id FROM projects WHERE ${isNumeric ? "id" : "slug"} = ? LIMIT 1`,
      [projectSlugOrId]
    );
    if (!project) throw new Error(`Project not found: ${projectSlugOrId}`);

    const result = await this.execute(
      "UPDATE project_tasks SET project_id = ?, updated_at = NOW() WHERE id = ?",
      [project.id, taskId]
    );

    return {
      ok: result.affectedRows > 0,
      affected_rows: result.affectedRows,
      before: { task_id: taskId, old_project_id: task.project_id, new_project_id: project.id },
      after: { task_id: taskId, old_project_id: task.project_id, new_project_id: project.id }
    };
  }

  // ─── 7. mark_task_as_blocked ──────────────────────────────────────────────

  /**
   * Marks a task as blocked with a required reason.
   * Appends reason to progress_notes.
   */
  async markTaskAsBlocked(
    taskId: number,
    reason: string
  ): Promise<DbWriteResult<DbTaskBefore>> {
    this.assertEnabled();

    const before = await this.queryOne<DbTaskBefore>(
      "SELECT id, title, status, progress_notes, completed_at FROM project_tasks WHERE id = ? LIMIT 1",
      [taskId]
    );
    if (!before) throw new Error(`Task not found: ${taskId}`);

    const timestamp = new Date().toISOString().slice(0, 19).replace("T", " ");
    const blockNote = `[BLOCKED ${timestamp}] ${reason.trim()}`;
    const updatedNotes = before.progress_notes
      ? `${before.progress_notes}\n${blockNote}`
      : blockNote;

    const result = await this.execute(
      "UPDATE project_tasks SET status = 'in_progress', progress_notes = ?, updated_at = NOW() WHERE id = ?",
      [updatedNotes, taskId]
    );

    const after = await this.queryOne<DbTaskBefore>(
      "SELECT id, title, status, progress_notes, completed_at FROM project_tasks WHERE id = ? LIMIT 1",
      [taskId]
    );

    return { ok: result.affectedRows > 0, affected_rows: result.affectedRows, before, after };
  }

  // ─── 8. mark_task_as_done ─────────────────────────────────────────────────

  /**
   * Marks a task as done, setting completed_at.
   */
  async markTaskAsDone(
    taskId: number,
    notes: string | null
  ): Promise<DbWriteResult<DbTaskBefore>> {
    this.assertEnabled();

    const before = await this.queryOne<DbTaskBefore>(
      "SELECT id, title, status, progress_notes, completed_at FROM project_tasks WHERE id = ? LIMIT 1",
      [taskId]
    );
    if (!before) throw new Error(`Task not found: ${taskId}`);

    const timestamp = new Date().toISOString().slice(0, 19).replace("T", " ");
    let updatedNotes = before.progress_notes ?? "";
    if (notes) {
      const doneNote = `[DONE ${timestamp}] ${notes.trim()}`;
      updatedNotes = updatedNotes ? `${updatedNotes}\n${doneNote}` : doneNote;
    }

    const result = await this.execute(
      "UPDATE project_tasks SET status = 'done', completed_at = NOW(), progress_notes = ?, updated_at = NOW() WHERE id = ?",
      [updatedNotes || null, taskId]
    );

    const after = await this.queryOne<DbTaskBefore>(
      "SELECT id, title, status, progress_notes, completed_at FROM project_tasks WHERE id = ? LIMIT 1",
      [taskId]
    );

    return { ok: result.affectedRows > 0, affected_rows: result.affectedRows, before, after };
  }

  async closePool(): Promise<void> {
    if (this.pool) {
      await this.pool.end();
      this.pool = null;
    }
  }

  // ─── Generic structured write ─────────────────────────────────────────────

  /**
   * Executes a structured INSERT / UPDATE / UPSERT against allowlisted tables.
   * Validates table, columns, and write mode before building SQL.
   * All values are bound as prepared-statement parameters.
   *
   * NOTE: This method should only be called for safe_execute tables.
   * For approval_required tables, server.ts handles the approval gate and
   * calls this method only after a valid approval is confirmed.
   */
  async executeStructuredWrite(
    input: StructuredWriteInput
  ): Promise<DbStructuredWriteResult> {
    this.assertEnabled();

    const normalized = this.normalizeStructuredWriteInput(input);
    const preparedInput = normalized.prepared;
    const built = buildWriteQuery(preparedInput);
    // buildWriteQuery throws on any policy violation before any DB call.

    const snapshotColumns = [...new Set([
      ...normalized.normalized_data_keys,
      ...(preparedInput.upsertKey ?? [])
    ])];

    let before: Record<string, unknown> | null = null;
    if (preparedInput.operation === "UPDATE" && preparedInput.where && preparedInput.where.length > 0) {
      before = await this.readSnapshot(preparedInput.table, preparedInput.where, normalized.normalized_data_keys);
    } else if (preparedInput.operation === "UPSERT" && normalized.identity_where) {
      before = await this.readSnapshot(preparedInput.table, normalized.identity_where, snapshotColumns);
    }

    const result = await this.execute(built.sql, built.params);

    let after: Record<string, unknown> | null = null;
    if (preparedInput.operation === "INSERT") {
      if (result.insertId) {
        after = await this.readSnapshot(
          preparedInput.table,
          [{ column: "id", operator: "=", value: result.insertId }],
          [...new Set(["id", ...normalized.normalized_data_keys])]
        );
      } else if (normalized.identity_where) {
        after = await this.readSnapshot(preparedInput.table, normalized.identity_where, snapshotColumns);
      }
    } else if (preparedInput.operation === "UPDATE" && preparedInput.where && preparedInput.where.length > 0) {
      after = await this.readSnapshot(preparedInput.table, preparedInput.where, normalized.normalized_data_keys);
    } else if (preparedInput.operation === "UPSERT" && normalized.identity_where) {
      after = await this.readSnapshot(preparedInput.table, normalized.identity_where, snapshotColumns);
    }

    return {
      ok: result.affectedRows > 0,
      affected_rows: result.affectedRows,
      before,
      after,
      table: preparedInput.table,
      resolved_table: built.resolvedTable,
      operation: preparedInput.operation,
      reason: preparedInput.reason,
      normalized_data_keys: normalized.normalized_data_keys,
      required_fields_checked: normalized.required_fields_checked,
      auto_filled_fields: normalized.auto_filled_fields
    };
  }

  // ─── Traceability: record_project_event ──────────────────────────────────

  async recordProjectEvent(params: {
    entityType: string;
    entityId: number;
    actor: string;
    action: string;
    payload?: Record<string, unknown> | null;
    context?: Record<string, unknown> | null;
  }): Promise<DbWriteResult<DbProjectEvent>> {
    this.assertEnabled();

    const eventId = randomUUID();
    const result = await this.execute(
      `INSERT INTO vee_project_events (event_id, entity_type, entity_id, actor, action, payload, context)
       VALUES (?, ?, ?, ?, ?, ?, ?)`,
      [
        eventId,
        params.entityType,
        params.entityId,
        params.actor,
        params.action,
        params.payload != null ? JSON.stringify(params.payload) : null,
        params.context != null ? JSON.stringify(params.context) : null
      ]
    );

    const after = await this.queryOne<DbProjectEvent>(
      "SELECT id, event_id, entity_type, entity_id, actor, action, created_at FROM vee_project_events WHERE id = ? LIMIT 1",
      [result.insertId]
    );

    return { ok: result.affectedRows > 0, affected_rows: result.affectedRows, before: null, after };
  }

  // ─── Traceability: record_status_change ──────────────────────────────────

  async recordStatusChange(params: {
    entityType: string;
    entityId: number;
    oldStatus: string | null;
    newStatus: string;
    changedBy: string;
    reason?: string | null;
  }): Promise<DbWriteResult<DbStatusHistory>> {
    this.assertEnabled();

    const result = await this.execute(
      `INSERT INTO vee_status_history (entity_type, entity_id, old_status, new_status, changed_by, reason)
       VALUES (?, ?, ?, ?, ?, ?)`,
      [
        params.entityType,
        params.entityId,
        params.oldStatus ?? null,
        params.newStatus,
        params.changedBy,
        params.reason ?? null
      ]
    );

    const after = await this.queryOne<DbStatusHistory>(
      "SELECT id, entity_type, entity_id, old_status, new_status, changed_by, reason, created_at FROM vee_status_history WHERE id = ? LIMIT 1",
      [result.insertId]
    );

    return { ok: result.affectedRows > 0, affected_rows: result.affectedRows, before: null, after };
  }

  // ─── Traceability: record_operational_decision ───────────────────────────

  async recordOperationalDecision(params: {
    title: string;
    context: string;
    rationale: string;
    outcome: string;
    actor: string;
    projectId?: number | null;
  }): Promise<DbWriteResult<DbOperationalDecision>> {
    this.assertEnabled();

    const decisionId = randomUUID();
    const result = await this.execute(
      `INSERT INTO vee_operational_decisions (decision_id, title, context, rationale, outcome, actor, project_id)
       VALUES (?, ?, ?, ?, ?, ?, ?)`,
      [
        decisionId,
        params.title,
        params.context,
        params.rationale,
        params.outcome,
        params.actor,
        params.projectId ?? null
      ]
    );

    const after = await this.queryOne<DbOperationalDecision>(
      "SELECT id, decision_id, title, actor, project_id, created_at FROM vee_operational_decisions WHERE id = ? LIMIT 1",
      [result.insertId]
    );

    return { ok: result.affectedRows > 0, affected_rows: result.affectedRows, before: null, after };
  }

  // ─── Traceability: record_entity_block ───────────────────────────────────

  async recordEntityBlock(params: {
    entityType: string;
    entityId: number;
    reason: string;
    blockedBy: string;
  }): Promise<DbWriteResult<DbBlockEntry>> {
    this.assertEnabled();

    const result = await this.execute(
      `INSERT INTO vee_blocks (entity_type, entity_id, reason, blocked_by)
       VALUES (?, ?, ?, ?)`,
      [params.entityType, params.entityId, params.reason, params.blockedBy]
    );

    const after = await this.queryOne<DbBlockEntry>(
      "SELECT id, entity_type, entity_id, reason, blocked_by, blocked_at FROM vee_blocks WHERE id = ? LIMIT 1",
      [result.insertId]
    );

    return { ok: result.affectedRows > 0, affected_rows: result.affectedRows, before: null, after };
  }

  // ─── Traceability: unblock_entity ────────────────────────────────────────

  async unblockEntity(params: {
    entityType: string;
    entityId: number;
    unblockReason: string;
    unblockedBy: string;
  }): Promise<DbWriteResult<{ entity_type: string; entity_id: number; unblocked_at: string | null }>> {
    this.assertEnabled();

    const before = await this.queryOne<DbBlockEntry>(
      "SELECT id, entity_type, entity_id, reason, blocked_by, blocked_at FROM vee_blocks WHERE entity_type = ? AND entity_id = ? AND unblocked_at IS NULL ORDER BY id DESC LIMIT 1",
      [params.entityType, params.entityId]
    );

    const result = await this.execute(
      "UPDATE vee_blocks SET unblocked_at = NOW(), unblock_reason = ?, unblocked_by = ? WHERE entity_type = ? AND entity_id = ? AND unblocked_at IS NULL",
      [params.unblockReason, params.unblockedBy, params.entityType, params.entityId]
    );

    const after =
      result.affectedRows > 0
        ? {
            entity_type: params.entityType,
            entity_id: params.entityId,
            unblocked_at: new Date().toISOString().slice(0, 19).replace("T", " ")
          }
        : null;

    return {
      ok: result.affectedRows > 0,
      affected_rows: result.affectedRows,
      before: before
        ? { entity_type: before.entity_type, entity_id: before.entity_id, unblocked_at: null }
        : null,
      after
    };
  }
}

function getRequiredFieldsForTable(table: string): string[] {
  const base = DEFAULT_REQUIRED_FIELDS[table] ?? [];
  const extra = CONFIG_REQUIRED_FIELDS[table] ?? [];
  return [...new Set([...base, ...extra])];
}

function getAutoFillStrategiesForTable(table: string): Record<string, AutoFillStrategy> {
  const merged: Record<string, AutoFillStrategy> = {};
  const base = DEFAULT_AUTO_FILL_FIELDS[table] ?? {};
  const extra = CONFIG_AUTO_FILL_FIELDS[table] ?? {};
  for (const [field, strategy] of Object.entries(base)) {
    merged[field] = strategy;
  }
  for (const [field, strategy] of Object.entries(extra)) {
    merged[field] = strategy;
  }
  return merged;
}

function parseRequiredFieldsByTableFromEnv(): Record<string, string[]> {
  const raw = parseJsonEnv("DB_WRITE_REQUIRED_FIELDS_JSON") ?? parseJsonEnv("DB_REQUIRED_FIELDS_JSON");
  if (!raw || typeof raw !== "object" || Array.isArray(raw)) {
    return {};
  }

  const parsed: Record<string, string[]> = {};
  for (const [rawTable, rawFields] of Object.entries(raw)) {
    const table = normalizeIdentifier(rawTable);
    if (!table || !Array.isArray(rawFields)) {
      continue;
    }

    const fields: string[] = [];
    const seen = new Set<string>();
    for (const field of rawFields) {
      const normalizedField = normalizeIdentifier(field);
      if (!normalizedField || seen.has(normalizedField)) {
        continue;
      }
      seen.add(normalizedField);
      fields.push(normalizedField);
    }
    parsed[table] = fields;
  }

  return parsed;
}

function parseAutoFillFieldsByTableFromEnv(): Record<string, Record<string, AutoFillStrategy>> {
  const raw = parseJsonEnv("DB_WRITE_AUTO_FILL_FIELDS_JSON");
  if (!raw || typeof raw !== "object" || Array.isArray(raw)) {
    return {};
  }

  const parsed: Record<string, Record<string, AutoFillStrategy>> = {};
  for (const [rawTable, rawConfig] of Object.entries(raw)) {
    const table = normalizeIdentifier(rawTable);
    if (!table || !rawConfig || typeof rawConfig !== "object" || Array.isArray(rawConfig)) {
      continue;
    }

    const config: Record<string, AutoFillStrategy> = {};
    for (const [rawField, rawStrategy] of Object.entries(rawConfig)) {
      const field = normalizeIdentifier(rawField);
      if (!field || typeof rawStrategy !== "string") {
        continue;
      }
      const strategy = rawStrategy.trim().toLowerCase();
      if (strategy !== "uuid" && strategy !== "now") {
        continue;
      }
      config[field] = strategy;
    }

    if (Object.keys(config).length > 0) {
      parsed[table] = config;
    }
  }

  return parsed;
}

function normalizeIdentifier(value: unknown): string | null {
  if (typeof value !== "string") {
    return null;
  }
  const normalized = value.trim().toLowerCase();
  if (!/^[a-z_][a-z0-9_]*$/i.test(normalized)) {
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
