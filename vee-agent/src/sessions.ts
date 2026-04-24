import type { RowDataPacket } from "mysql2/promise";
import { v4 as uuidv4 } from "uuid";
import { pool } from "./db.js";
import type { ChatCompletionMessageParam } from "openai/resources/chat/completions.js";

export type Session = {
  id: string;
  title: string;
  mode: "chat" | "cowork";
  created_at: string;
  updated_at: string;
};

export type StoredMessage = {
  id: number;
  session_id: string;
  role: "user" | "assistant" | "tool";
  content: string | null;
  tool_calls: string | null;
  tool_call_id: string | null;
  tool_name: string | null;
  created_at: string;
};

export type SessionMessage = {
  id: number;
  role: "user" | "assistant" | "tool";
  content: string | null;
  created_at: string;
  can_edit: boolean;
};

export async function createSession(mode: "chat" | "cowork", title?: string): Promise<Session> {
  const id = uuidv4();
  const sessionTitle = title ?? "Nova tarefa";
  await pool.execute(
    "INSERT INTO vee_sessions (id, title, mode) VALUES (?, ?, ?)",
    [id, sessionTitle, mode]
  );
  const session = await getSession(id);
  if (!session) throw new Error("Failed to create session");
  return session;
}

export async function getSession(id: string): Promise<Session | null> {
  const [rows] = await pool.execute<RowDataPacket[]>(
    "SELECT * FROM vee_sessions WHERE id = ?",
    [id]
  );
  return (rows[0] as Session) ?? null;
}

export async function listSessions(limit = 50): Promise<Session[]> {
  // LIMIT não pode ser bind parameter no mysql2 com prepared statements
  const safeLimit = Math.min(Math.max(1, Math.floor(limit)), 200);
  const [rows] = await pool.execute<RowDataPacket[]>(
    `SELECT * FROM vee_sessions ORDER BY updated_at DESC LIMIT ${safeLimit}`
  );
  return rows as Session[];
}

export async function updateSessionTitle(id: string, title: string): Promise<void> {
  await pool.execute("UPDATE vee_sessions SET title = ? WHERE id = ?", [title, id]);
}

export async function deleteSession(id: string): Promise<void> {
  await pool.execute("DELETE FROM vee_sessions WHERE id = ?", [id]);
}

export async function saveMessage(params: {
  session_id: string;
  role: "user" | "assistant" | "tool";
  content?: string | null;
  tool_calls?: unknown;
  tool_call_id?: string;
  tool_name?: string;
}): Promise<void> {
  await pool.execute(
    `INSERT INTO vee_messages
      (session_id, role, content, tool_calls, tool_call_id, tool_name)
     VALUES (?, ?, ?, ?, ?, ?)`,
    [
      params.session_id,
      params.role,
      params.content ?? null,
      params.tool_calls ? JSON.stringify(params.tool_calls) : null,
      params.tool_call_id ?? null,
      params.tool_name ?? null
    ]
  );
}

async function loadStoredMessages(session_id: string): Promise<StoredMessage[]> {
  const [rows] = await pool.execute<RowDataPacket[]>(
    "SELECT * FROM vee_messages WHERE session_id = ? ORDER BY id ASC",
    [session_id]
  );
  return rows as StoredMessage[];
}

export async function loadSessionMessages(session_id: string): Promise<SessionMessage[]> {
  const rows = await loadStoredMessages(session_id);
  const latestUserId = rows.reduce((acc, row) => (row.role === "user" ? row.id : acc), 0);

  return rows.map((row) => ({
    id: row.id,
    role: row.role,
    content: row.content ?? null,
    created_at: row.created_at,
    can_edit: row.role === "user" && row.id === latestUserId
  }));
}

export async function loadHistory(session_id: string): Promise<ChatCompletionMessageParam[]> {
  const rows = await loadStoredMessages(session_id);

  return rows.map((row): ChatCompletionMessageParam => {
    if (row.role === "tool") {
      return {
        role: "tool",
        tool_call_id: row.tool_call_id ?? "",
        content: row.content ?? ""
      };
    }

    if (row.role === "assistant") {
      const toolCalls: unknown[] | undefined = row.tool_calls
        ? (typeof row.tool_calls === "string"
            ? JSON.parse(row.tool_calls)
            : row.tool_calls)
        : undefined;
      return {
        role: "assistant",
        content: row.content ?? null,
        ...(toolCalls ? { tool_calls: toolCalls as never } : {})
      };
    }

    return {
      role: "user",
      content: row.content ?? ""
    };
  });
}

export async function editUserMessage(
  session_id: string,
  messageId: number,
  content: string
): Promise<{ ok: true; pruned_messages: number } | { ok: false; status: 404 | 422; error: string }> {
  const normalizedContent = content.trim();
  if (!normalizedContent) {
    return { ok: false, status: 422, error: "content is required" };
  }

  const conn = await pool.getConnection();
  try {
    await conn.beginTransaction();

    const [targetRows] = await conn.execute<RowDataPacket[]>(
      "SELECT id, role FROM vee_messages WHERE session_id = ? AND id = ? LIMIT 1",
      [session_id, messageId]
    );
    const target = (targetRows[0] as { id: number; role: string } | undefined) ?? null;

    if (!target) {
      await conn.rollback();
      return { ok: false, status: 404, error: "message not found" };
    }

    if (target.role !== "user") {
      await conn.rollback();
      return { ok: false, status: 422, error: "only user messages can be edited" };
    }

    const [lastRows] = await conn.execute<RowDataPacket[]>(
      "SELECT MAX(id) AS last_user_id FROM vee_messages WHERE session_id = ? AND role = 'user'",
      [session_id]
    );
    const lastUserId = Number((lastRows[0] as { last_user_id?: number | null } | undefined)?.last_user_id ?? 0);

    if (lastUserId !== messageId) {
      await conn.rollback();
      return { ok: false, status: 422, error: "only the latest user message can be edited" };
    }

    await conn.execute(
      "UPDATE vee_messages SET content = ? WHERE session_id = ? AND id = ?",
      [normalizedContent, session_id, messageId]
    );

    const [deleteResult] = await conn.execute(
      "DELETE FROM vee_messages WHERE session_id = ? AND id > ?",
      [session_id, messageId]
    );

    await conn.commit();

    const affectedRows =
      typeof deleteResult === "object" && deleteResult !== null && "affectedRows" in deleteResult
        ? Number((deleteResult as { affectedRows?: number }).affectedRows ?? 0)
        : 0;

    return { ok: true, pruned_messages: affectedRows };
  } catch (error) {
    await conn.rollback();
    throw error;
  } finally {
    conn.release();
  }
}
