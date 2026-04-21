// ─── Structured SQL Builder ───────────────────────────────────────────────────
// Builds parameterized SELECT / INSERT / UPDATE / UPSERT statements from
// structured inputs. No raw SQL reaches MySQL — all values are bound parameters.
//
// Security invariants:
//   • Every table name is validated against the allowlist before SQL is built.
//   • Column names are validated with a strict regex AND against table policy.
//   • JOIN ON clauses accept only the exact pattern "table.col = table.col".
//   • WHERE values are always bound as prepared-statement parameters.
//   • UPDATE without WHERE is rejected (prevents full-table updates).

import {
  resolveTablePolicy,
  resolveActualTableName,
  type TablePolicy
} from "./queryAllowlist.js";

// ─── Public input / output types ─────────────────────────────────────────────

export type WhereOperator =
  | "="
  | "!="
  | ">"
  | ">="
  | "<"
  | "<="
  | "LIKE"
  | "NOT LIKE"
  | "IN"
  | "NOT IN"
  | "IS NULL"
  | "IS NOT NULL";

export type SqlParam = string | number | boolean | null;

export type WhereCondition = {
  /** Column name (bare or table-qualified like "projects.id"). */
  column: string;
  operator: WhereOperator;
  /**
   * Bound value(s).
   * Omit / null for IS NULL / IS NOT NULL.
   * Array for IN / NOT IN.
   */
  value?: SqlParam | SqlParam[];
};

export type JoinClause = {
  type?: "INNER" | "LEFT" | "RIGHT";
  /** Table logical name — validated against allowlist. */
  table: string;
  /**
   * ON expression. Only the exact format "tableA.columnA = tableB.columnB"
   * is accepted. No functions, no subqueries.
   */
  on: string;
};

export type OrderByClause = {
  column: string;
  direction?: "ASC" | "DESC";
};

export type StructuredQueryInput = {
  table: string;
  columns?: string[];
  where?: WhereCondition[];
  joins?: JoinClause[];
  orderBy?: OrderByClause[];
  limit?: number;
  offset?: number;
};

export type StructuredWriteInput = {
  operation: "INSERT" | "UPDATE" | "UPSERT";
  table: string;
  data: Record<string, unknown>;
  /** Required for UPDATE. Allowed for UPSERT but not used in WHERE. */
  where?: WhereCondition[];
  reason: string;
  /** Unique-key columns for UPSERT — excluded from the ON DUPLICATE KEY UPDATE list. */
  upsertKey?: string[];
};

export type BuiltQuery = {
  sql: string;
  params: SqlParam[];
  sensitive: boolean;
  policy: TablePolicy;
  resolvedTable: string;
};

// ─── Internal constants ───────────────────────────────────────────────────────

/**
 * Strict regex for JOIN ON clauses.
 * Only accepts: tableA.columnA = tableB.columnB (optional surrounding spaces)
 */
const JOIN_ON_REGEX =
  /^[a-z_][a-z0-9_]*\.[a-z_][a-z0-9_]*\s*=\s*[a-z_][a-z0-9_]*\.[a-z_][a-z0-9_]*$/i;

/** Column names may be bare (col) or table-qualified (table.col). */
const COLUMN_NAME_REGEX = /^[a-z_][a-z0-9_]*(\.[a-z_][a-z0-9_]*)?$/i;

const ALLOWED_OPERATORS = new Set<WhereOperator>([
  "=", "!=", ">", ">=", "<", "<=",
  "LIKE", "NOT LIKE",
  "IN", "NOT IN",
  "IS NULL", "IS NOT NULL"
]);

// ─── Validation helpers ───────────────────────────────────────────────────────

function assertSafeColumnName(column: string, context: string): void {
  if (!COLUMN_NAME_REGEX.test(column)) {
    throw new Error(`Unsafe column name in ${context}: "${column}"`);
  }
}

function bareColumn(column: string): string {
  return column.includes(".") ? column.split(".")[1]! : column;
}

function assertColumnAllowed(column: string, policy: TablePolicy, context: string): void {
  const bare = bareColumn(column);

  if (policy.blockedColumns.includes(bare)) {
    throw new Error(`Column "${bare}" is blocked in "${context}".`);
  }

  if (
    policy.allowedColumns !== "*" &&
    !policy.allowedColumns.includes(bare)
  ) {
    throw new Error(`Column "${bare}" is not in the allowed columns for "${context}".`);
  }
}

// ─── SELECT column list resolver ─────────────────────────────────────────────

function resolveSelectColumns(
  requestedColumns: string[] | undefined,
  policy: TablePolicy,
  resolvedTable: string
): string {
  if (!requestedColumns || requestedColumns.length === 0) {
    if (policy.blockedColumns.length > 0) {
      // Cannot safely use SELECT * when there are blocked columns.
      // Caller must provide an explicit column list.
      throw new Error(
        `Table "${resolvedTable}" has blocked columns (${policy.blockedColumns.join(", ")}). ` +
        `Provide an explicit "columns" list.`
      );
    }
    if (policy.allowedColumns === "*") {
      return "*";
    }
    return policy.allowedColumns.map(c => `\`${c}\``).join(", ");
  }

  for (const col of requestedColumns) {
    assertSafeColumnName(col, resolvedTable);
    assertColumnAllowed(col, policy, resolvedTable);
  }

  return requestedColumns
    .map(c => (c.includes(".") ? c : `\`${c}\``))
    .join(", ");
}

// ─── WHERE clause builder ─────────────────────────────────────────────────────

function buildWhereClause(
  conditions: WhereCondition[],
  policy: TablePolicy,
  resolvedTable: string
): { fragment: string; params: SqlParam[] } {
  if (conditions.length === 0) {
    return { fragment: "", params: [] };
  }

  const parts: string[] = [];
  const params: SqlParam[] = [];

  for (const cond of conditions) {
    assertSafeColumnName(cond.column, resolvedTable);
    assertColumnAllowed(cond.column, policy, resolvedTable);

    if (!ALLOWED_OPERATORS.has(cond.operator)) {
      throw new Error(`Operator "${cond.operator}" is not allowed.`);
    }

    const col = cond.column.includes(".") ? cond.column : `\`${cond.column}\``;

    if (cond.operator === "IS NULL" || cond.operator === "IS NOT NULL") {
      parts.push(`${col} ${cond.operator}`);

    } else if (cond.operator === "IN" || cond.operator === "NOT IN") {
      const values = Array.isArray(cond.value) ? cond.value : [cond.value as SqlParam];
      if (values.length === 0) {
        throw new Error("IN / NOT IN clause requires at least one value.");
      }
      const placeholders = values.map(() => "?").join(", ");
      parts.push(`${col} ${cond.operator} (${placeholders})`);
      params.push(...values);

    } else {
      if (cond.value === undefined) {
        throw new Error(`Operator "${cond.operator}" requires a value.`);
      }
      parts.push(`${col} ${cond.operator} ?`);
      params.push(cond.value as SqlParam);
    }
  }

  return { fragment: `WHERE ${parts.join(" AND ")}`, params };
}

// ─── JOIN builder ─────────────────────────────────────────────────────────────

function buildJoinClauses(joins: JoinClause[]): string {
  return joins
    .map(join => {
      const joinPolicy = resolveTablePolicy(join.table);
      if (!joinPolicy) {
        throw new Error(
          `JOIN table "${join.table}" is not in the allowed table list.`
        );
      }
      if (!JOIN_ON_REGEX.test(join.on.trim())) {
        throw new Error(
          `Invalid JOIN ON clause: "${join.on}". ` +
          `Only "tableA.colA = tableB.colB" format is accepted.`
        );
      }
      const joinType = join.type ?? "INNER";
      const actualTable = resolveActualTableName(join.table);
      return `${joinType} JOIN \`${actualTable}\` ON ${join.on.trim()}`;
    })
    .join(" ");
}

// ─── ORDER BY builder ─────────────────────────────────────────────────────────

function buildOrderByClause(
  orderBy: OrderByClause[],
  policy: TablePolicy,
  resolvedTable: string
): string {
  if (orderBy.length === 0) return "";

  const parts = orderBy.map(o => {
    assertSafeColumnName(o.column, resolvedTable);
    assertColumnAllowed(o.column, policy, resolvedTable);
    const dir = o.direction === "DESC" ? "DESC" : "ASC";
    const col = o.column.includes(".") ? o.column : `\`${o.column}\``;
    return `${col} ${dir}`;
  });

  return `ORDER BY ${parts.join(", ")}`;
}

// ─── Public builders ──────────────────────────────────────────────────────────

/** Builds a safe parameterized SELECT statement from structured input. */
export function buildSelectQuery(input: StructuredQueryInput, maxRows: number): BuiltQuery {
  const policy = resolveTablePolicy(input.table);
  if (!policy) {
    throw new Error(`Table or view "${input.table}" is not in the allowed list.`);
  }

  const resolvedTable = resolveActualTableName(input.table);
  const selectCols = resolveSelectColumns(input.columns, policy, resolvedTable);

  const { fragment: whereFragment, params: whereParams } = buildWhereClause(
    input.where ?? [],
    policy,
    resolvedTable
  );

  const joinFragment = buildJoinClauses(input.joins ?? []);
  const orderFragment = buildOrderByClause(input.orderBy ?? [], policy, resolvedTable);

  const safeLimit = Math.max(1, Math.min(input.limit ?? maxRows, maxRows));
  const safeOffset = Math.max(0, input.offset ?? 0);

  const parts = [
    `SELECT ${selectCols}`,
    `FROM \`${resolvedTable}\``,
    joinFragment || null,
    whereFragment || null,
    orderFragment || null,
    `LIMIT ?`,
    safeOffset > 0 ? `OFFSET ?` : null
  ].filter(Boolean);

  const params: SqlParam[] = [
    ...whereParams,
    safeLimit,
    ...(safeOffset > 0 ? [safeOffset] : [])
  ];

  return {
    sql: parts.join(" "),
    params,
    sensitive: policy.sensitive,
    policy,
    resolvedTable
  };
}

/** Builds a safe parameterized INSERT / UPDATE / UPSERT statement from structured input. */
export function buildWriteQuery(input: StructuredWriteInput): BuiltQuery {
  const policy = resolveTablePolicy(input.table);
  if (!policy) {
    throw new Error(`Table "${input.table}" is not in the allowed list.`);
  }
  if (policy.writeMode === "read_only") {
    throw new Error(`Table "${input.table}" is read-only and does not allow writes.`);
  }

  const resolvedTable = resolveActualTableName(input.table);
  const columns = Object.keys(input.data);

  if (columns.length === 0) {
    throw new Error("Write operation requires at least one data column.");
  }

  for (const col of columns) {
    assertSafeColumnName(col, resolvedTable);
    assertColumnAllowed(col, policy, resolvedTable);
  }

  const params: SqlParam[] = [];
  let sql: string;

  if (input.operation === "INSERT") {
    const colList = columns.map(c => `\`${c}\``).join(", ");
    const placeholders = columns.map(() => "?").join(", ");
    for (const col of columns) params.push(input.data[col] as SqlParam);
    sql = `INSERT INTO \`${resolvedTable}\` (${colList}) VALUES (${placeholders})`;

  } else if (input.operation === "UPDATE") {
    if (!input.where || input.where.length === 0) {
      throw new Error(
        "UPDATE requires at least one WHERE condition to prevent full-table updates."
      );
    }
    const setClauses = columns.map(c => `\`${c}\` = ?`).join(", ");
    for (const col of columns) params.push(input.data[col] as SqlParam);

    const { fragment: whereFragment, params: whereParams } = buildWhereClause(
      input.where,
      policy,
      resolvedTable
    );
    params.push(...whereParams);
    sql = `UPDATE \`${resolvedTable}\` SET ${setClauses} ${whereFragment}`;

  } else {
    // UPSERT — INSERT ... ON DUPLICATE KEY UPDATE
    const colList = columns.map(c => `\`${c}\``).join(", ");
    const placeholders = columns.map(() => "?").join(", ");
    for (const col of columns) params.push(input.data[col] as SqlParam);

    const keyColumns = new Set(input.upsertKey ?? []);
    const updateCols = columns.filter(c => !keyColumns.has(c));
    if (updateCols.length === 0) {
      throw new Error(
        "UPSERT requires at least one non-key column to update."
      );
    }
    const onDup = updateCols.map(c => `\`${c}\` = VALUES(\`${c}\`)`).join(", ");
    sql = `INSERT INTO \`${resolvedTable}\` (${colList}) VALUES (${placeholders}) ON DUPLICATE KEY UPDATE ${onDup}`;
  }

  return {
    sql,
    params,
    sensitive: policy.sensitive,
    policy,
    resolvedTable
  };
}

/**
 * Builds a minimal SELECT for the before/after snapshot of a write operation.
 * Used internally by the write adapter.
 */
export function buildSnapshotQuery(
  table: string,
  where: WhereCondition[],
  columns?: string[]
): { sql: string; params: SqlParam[] } {
  const built = buildSelectQuery({ table, where, columns, limit: 1 }, 1);
  return { sql: built.sql, params: built.params };
}
