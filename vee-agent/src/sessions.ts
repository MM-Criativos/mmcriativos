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
  const [rows] = await pool.execute<RowDataPacket[]>(
    "SELECT * FROM vee_sessions ORDER BY updated_at DESC LIMIT ?",
    [limit]
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

export async function loadHistory(session_id: string): Promise<ChatCompletionMessageParam[]> {
  const [rows] = await pool.execute<RowDataPacket[]>(
    "SELECT * FROM vee_messages WHERE session_id = ? ORDER BY id ASC",
    [session_id]
  );

  return (rows as StoredMessage[]).map((row): ChatCompletionMessageParam => {
    if (row.role === "tool") {
      return {
        role: "tool",
        tool_call_id: row.tool_call_id ?? "",
        content: row.content ?? ""
      };
    }

    if (row.role === "assistant") {
      const toolCalls = row.tool_calls
        ? (JSON.parse(row.tool_calls) as unknown[])
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
