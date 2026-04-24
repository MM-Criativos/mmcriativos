// Structured SQL builder for vee_db_query / vee_db_write.
// All statements are parameterized and validated against the DB allowlist.

import {
  resolveTablePolicy,
  resolveActualTableName,
  normalizeDbTarget,
  type DbTarget,
  type TablePolicy
} from "./queryAllowlist.js";

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
  column: string;
  operator: WhereOperator;
  value?: SqlParam | SqlParam[];
};

export type JoinClause = {
  type?: "INNER" | "LEFT" | "RIGHT";
  table: string;
  on: string;
};

export type OrderByClause = {
  column: string;
  direction?: "ASC" | "DESC";
};

export type StructuredQueryInput = {
  dbTarget?: DbTarget;
  table: string;
  columns?: string[];
  where?: WhereCondition[];
  joins?: JoinClause[];
  orderBy?: OrderByClause[];
  limit?: number;
  offset?: number;
};

export type StructuredWriteInput = {
  dbTarget?: DbTarget;
  operation: "INSERT" | "UPDATE" | "UPSERT";
  table: string;
  data: Record<string, unknown>;
  where?: WhereCondition[];
  reason: string;
  upsertKey?: string[];
};

export type BuiltQuery = {
  sql: string;
  params: SqlParam[];
  sensitive: boolean;
  policy: TablePolicy;
  resolvedTable: string;
  appliedLimit?: number;
  appliedOffset?: number;
  involvedTables?: string[];
};

type ResolvedTableRef = {
  logicalName: string;
  actualName: string;
  policy: TablePolicy;
};

type QueryContext = {
  base: ResolvedTableRef;
  joins: ResolvedTableRef[];
  hasJoins: boolean;
  byName: Map<string, ResolvedTableRef>;
};

type ColumnRef = {
  tableRef: ResolvedTableRef;
  column: string;
  sql: string;
};

const COLUMN_NAME_REGEX = /^[a-z_][a-z0-9_]*(\.[a-z_][a-z0-9_]*)?$/i;
const JOIN_ON_REGEX =
  /^([a-z_][a-z0-9_]*)\.([a-z_][a-z0-9_]*)\s*=\s*([a-z_][a-z0-9_]*)\.([a-z_][a-z0-9_]*)$/i;

const ALLOWED_OPERATORS = new Set<WhereOperator>([
  "=", "!=", ">", ">=", "<", "<=",
  "LIKE", "NOT LIKE",
  "IN", "NOT IN",
  "IS NULL", "IS NOT NULL"
]);

function assertSafeColumnName(column: string, context: string): void {
  if (!COLUMN_NAME_REGEX.test(column)) {
    throw new Error(`Unsafe column name in ${context}: "${column}"`);
  }
}

function bareColumn(column: string): string {
  return column.includes(".") ? column.split(".")[1]! : column;
}

function normalizeIdentifier(value: string): string {
  return value.trim().toLowerCase();
}

function assertColumnAllowed(column: string, policy: TablePolicy, context: string): void {
  const bare = bareColumn(column);

  if (policy.blockedColumns.includes(bare)) {
    throw new Error(`Column "${bare}" is blocked in "${context}".`);
  }

  if (policy.allowedColumns !== "*" && !policy.allowedColumns.includes(bare)) {
    throw new Error(`Column "${bare}" is not in the allowed columns for "${context}".`);
  }
}

function registerTableRef(byName: Map<string, ResolvedTableRef>, ref: ResolvedTableRef): void {
  byName.set(normalizeIdentifier(ref.logicalName), ref);
  byName.set(normalizeIdentifier(ref.actualName), ref);
}

function resolveTableRef(byName: Map<string, ResolvedTableRef>, name: string, context: string): ResolvedTableRef {
  const key = normalizeIdentifier(name);
  const ref = byName.get(key);

  if (!ref) {
    const available = [...new Set([...byName.keys()])].sort().join(", ");
    throw new Error(`Unknown table reference "${name}" in ${context}. Available: ${available}`);
  }

  return ref;
}

function splitColumnRef(column: string): { tableName?: string; columnName: string } {
  const trimmed = column.trim();
  if (!trimmed.includes(".")) {
    return { columnName: trimmed };
  }

  const [tableName, columnName] = trimmed.split(".", 2);
  return { tableName, columnName };
}

function resolveColumnRef(column: string, context: QueryContext, clause: string): ColumnRef {
  assertSafeColumnName(column, clause);
  const { tableName, columnName } = splitColumnRef(column);

  if (!tableName) {
    assertColumnAllowed(columnName, context.base.policy, context.base.logicalName);
    return {
      tableRef: context.base,
      column: columnName,
      sql: `\`${context.base.actualName}\`.\`${columnName}\``
    };
  }

  const tableRef = resolveTableRef(context.byName, tableName, clause);
  assertColumnAllowed(columnName, tableRef.policy, tableRef.logicalName);

  return {
    tableRef,
    column: columnName,
    sql: `\`${tableRef.actualName}\`.\`${columnName}\``
  };
}

function buildQueryContext(input: StructuredQueryInput): QueryContext {
  const dbTarget = normalizeDbTarget(input.dbTarget);
  const basePolicy = resolveTablePolicy(input.table, dbTarget);
  if (!basePolicy) {
    throw new Error(`Table or view "${input.table}" is not in the allowed list.`);
  }

  const base: ResolvedTableRef = {
    logicalName: normalizeIdentifier(input.table),
    actualName: resolveActualTableName(input.table, dbTarget),
    policy: basePolicy
  };

  const byName = new Map<string, ResolvedTableRef>();
  registerTableRef(byName, base);

  const joins: ResolvedTableRef[] = [];
  for (const join of input.joins ?? []) {
    const joinPolicy = resolveTablePolicy(join.table, dbTarget);
    if (!joinPolicy) {
      throw new Error(`JOIN table "${join.table}" is not in the allowed table list.`);
    }

    const joinRef: ResolvedTableRef = {
      logicalName: normalizeIdentifier(join.table),
      actualName: resolveActualTableName(join.table, dbTarget),
      policy: joinPolicy
    };

    joins.push(joinRef);
    registerTableRef(byName, joinRef);
  }

  return {
    base,
    joins,
    hasJoins: joins.length > 0,
    byName
  };
}

function resolveSelectColumns(requestedColumns: string[] | undefined, context: QueryContext): string {
  const basePolicy = context.base.policy;

  if (!requestedColumns || requestedColumns.length === 0) {
    if (basePolicy.blockedColumns.length > 0) {
      throw new Error(
        `Table "${context.base.logicalName}" has blocked columns (${basePolicy.blockedColumns.join(", ")}). ` +
        `Provide an explicit "columns" list.`
      );
    }

    if (basePolicy.allowedColumns === "*") {
      return context.hasJoins ? `\`${context.base.actualName}\`.*` : "*";
    }

    return basePolicy.allowedColumns
      .map((column) => `\`${context.base.actualName}\`.\`${column}\``)
      .join(", ");
  }

  return requestedColumns
    .map((column) => resolveColumnRef(column, context, "SELECT").sql)
    .join(", ");
}

function buildWhereClause(
  conditions: WhereCondition[],
  context: QueryContext
): { fragment: string; params: SqlParam[] } {
  if (conditions.length === 0) {
    return { fragment: "", params: [] };
  }

  const parts: string[] = [];
  const params: SqlParam[] = [];

  for (const cond of conditions) {
    if (!ALLOWED_OPERATORS.has(cond.operator)) {
      throw new Error(`Operator "${cond.operator}" is not allowed.`);
    }

    const ref = resolveColumnRef(cond.column, context, "WHERE");

    if (cond.operator === "IS NULL" || cond.operator === "IS NOT NULL") {
      parts.push(`${ref.sql} ${cond.operator}`);
      continue;
    }

    if (cond.operator === "IN" || cond.operator === "NOT IN") {
      const values = Array.isArray(cond.value) ? cond.value : [cond.value as SqlParam];
      if (values.length === 0 || values.some((value) => value === undefined)) {
        throw new Error("IN / NOT IN clause requires at least one value.");
      }
      const placeholders = values.map(() => "?").join(", ");
      parts.push(`${ref.sql} ${cond.operator} (${placeholders})`);
      params.push(...values);
      continue;
    }

    if (cond.value === undefined) {
      throw new Error(`Operator "${cond.operator}" requires a value.`);
    }

    parts.push(`${ref.sql} ${cond.operator} ?`);
    params.push(cond.value as SqlParam);
  }

  return { fragment: `WHERE ${parts.join(" AND ")}`, params };
}

function buildJoinClauses(joins: JoinClause[], context: QueryContext): string {
  if (joins.length === 0) {
    return "";
  }

  const parts: string[] = [];
  const seenTables = new Map<string, ResolvedTableRef>();
  registerTableRef(seenTables, context.base);

  for (const join of joins) {
    const joinRef = resolveTableRef(context.byName, join.table, "JOIN table");
    registerTableRef(seenTables, joinRef);

    const match = JOIN_ON_REGEX.exec(join.on.trim());
    if (!match) {
      throw new Error(
        `Invalid JOIN ON clause: "${join.on}". Only "tableA.colA = tableB.colB" format is accepted.`
      );
    }

    const [, leftTable, leftColumn, rightTable, rightColumn] = match;
    const leftRef = resolveTableRef(seenTables, leftTable!, "JOIN ON");
    const rightRef = resolveTableRef(seenTables, rightTable!, "JOIN ON");

    assertColumnAllowed(leftColumn!, leftRef.policy, leftRef.logicalName);
    assertColumnAllowed(rightColumn!, rightRef.policy, rightRef.logicalName);

    if (leftRef.actualName !== joinRef.actualName && rightRef.actualName !== joinRef.actualName) {
      throw new Error(
        `JOIN ON clause for table "${join.table}" must reference the joined table on at least one side.`
      );
    }

    const joinType = join.type ?? "INNER";
    parts.push(
      `${joinType} JOIN \`${joinRef.actualName}\` ON ` +
      `\`${leftRef.actualName}\`.\`${leftColumn}\` = \`${rightRef.actualName}\`.\`${rightColumn}\``
    );
  }

  return parts.join(" ");
}

function buildOrderByClause(orderBy: OrderByClause[], context: QueryContext): string {
  if (orderBy.length === 0) {
    return "";
  }

  const parts = orderBy.map((item) => {
    const ref = resolveColumnRef(item.column, context, "ORDER BY");
    const direction = item.direction === "DESC" ? "DESC" : "ASC";
    return `${ref.sql} ${direction}`;
  });

  return `ORDER BY ${parts.join(", ")}`;
}

export function buildSelectQuery(input: StructuredQueryInput, maxRows: number): BuiltQuery {
  const context = buildQueryContext(input);

  const selectCols = resolveSelectColumns(input.columns, context);
  const joinFragment = buildJoinClauses(input.joins ?? [], context);
  const { fragment: whereFragment, params: whereParams } = buildWhereClause(input.where ?? [], context);
  const orderFragment = buildOrderByClause(input.orderBy ?? [], context);

  const safeLimit = Math.max(1, Math.min(input.limit ?? maxRows, maxRows));
  const safeOffset = Math.max(0, input.offset ?? 0);

  const parts = [
    `SELECT ${selectCols}`,
    `FROM \`${context.base.actualName}\``,
    joinFragment || null,
    whereFragment || null,
    orderFragment || null,
    `LIMIT ${safeLimit}`,
    safeOffset > 0 ? `OFFSET ${safeOffset}` : null
  ].filter(Boolean);

  const params: SqlParam[] = [...whereParams];

  const involvedTables = [
    context.base.actualName,
    ...context.joins.map((join) => join.actualName)
  ];

  return {
    sql: parts.join(" "),
    params,
    sensitive: context.base.policy.sensitive,
    policy: context.base.policy,
    resolvedTable: context.base.actualName,
    appliedLimit: safeLimit,
    appliedOffset: safeOffset,
    involvedTables: [...new Set(involvedTables)]
  };
}

export function buildWriteQuery(input: StructuredWriteInput): BuiltQuery {
  const dbTarget = normalizeDbTarget(input.dbTarget);
  const policy = resolveTablePolicy(input.table, dbTarget);
  if (!policy) {
    throw new Error(`Table "${input.table}" is not in the allowed list.`);
  }
  if (policy.writeMode === "read_only") {
    throw new Error(`Table "${input.table}" is read-only and does not allow writes.`);
  }

  const resolvedTable = resolveActualTableName(input.table, dbTarget);
  const columns = Object.keys(input.data);

  if (columns.length === 0) {
    throw new Error("Write operation requires at least one data column.");
  }

  for (const column of columns) {
    assertSafeColumnName(column, resolvedTable);
    assertColumnAllowed(column, policy, resolvedTable);
  }

  const params: SqlParam[] = [];
  let sql: string;

  if (input.operation === "INSERT") {
    const colList = columns.map((column) => `\`${column}\``).join(", ");
    const placeholders = columns.map(() => "?").join(", ");
    for (const column of columns) {
      params.push(input.data[column] as SqlParam);
    }
    sql = `INSERT INTO \`${resolvedTable}\` (${colList}) VALUES (${placeholders})`;
  } else if (input.operation === "UPDATE") {
    if (!input.where || input.where.length === 0) {
      throw new Error("UPDATE requires at least one WHERE condition to prevent full-table updates.");
    }

    const setClauses = columns.map((column) => `\`${column}\` = ?`).join(", ");
    for (const column of columns) {
      params.push(input.data[column] as SqlParam);
    }

    const baseContext: QueryContext = {
      base: {
        logicalName: normalizeIdentifier(input.table),
        actualName: resolvedTable,
        policy
      },
      joins: [],
      hasJoins: false,
      byName: new Map()
    };
    registerTableRef(baseContext.byName, baseContext.base);

    const { fragment: whereFragment, params: whereParams } = buildWhereClause(input.where, baseContext);
    params.push(...whereParams);
    sql = `UPDATE \`${resolvedTable}\` SET ${setClauses} ${whereFragment}`;
  } else {
    const colList = columns.map((column) => `\`${column}\``).join(", ");
    const placeholders = columns.map(() => "?").join(", ");
    for (const column of columns) {
      params.push(input.data[column] as SqlParam);
    }

    const keyColumns = new Set(input.upsertKey ?? []);
    const updateCols = columns.filter((column) => !keyColumns.has(column));
    if (updateCols.length === 0) {
      throw new Error("UPSERT requires at least one non-key column to update.");
    }

    const onDuplicate = updateCols.map((column) => `\`${column}\` = VALUES(\`${column}\`)`).join(", ");
    sql =
      `INSERT INTO \`${resolvedTable}\` (${colList}) VALUES (${placeholders}) ` +
      `ON DUPLICATE KEY UPDATE ${onDuplicate}`;
  }

  return {
    sql,
    params,
    sensitive: policy.sensitive,
    policy,
    resolvedTable
  };
}

export function buildSnapshotQuery(
  table: string,
  where: WhereCondition[],
  columns?: string[],
  dbTarget?: DbTarget
): { sql: string; params: SqlParam[] } {
  const built = buildSelectQuery({ dbTarget, table, where, columns, limit: 1 }, 1);
  return { sql: built.sql, params: built.params };
}
