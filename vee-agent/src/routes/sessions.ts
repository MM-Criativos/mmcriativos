import type { Request, Response } from "express";
import {
  listSessions,
  getSession,
  createSession,
  updateSessionTitle,
  deleteSession,
  loadSessionMessages,
  editUserMessage
} from "../sessions.js";

export async function handleListSessions(req: Request, res: Response): Promise<void> {
  const limit = Math.min(Number.parseInt(String(req.query["limit"] ?? "50"), 10), 200);
  const sessions = await listSessions(Number.isNaN(limit) ? 50 : limit);
  res.json({ total: sessions.length, sessions });
}

export async function handleGetSession(req: Request, res: Response): Promise<void> {
  const { id } = req.params as { id: string };
  const session = await getSession(id);
  if (!session) {
    res.status(404).json({ error: "Session not found" });
    return;
  }
  const messages = await loadSessionMessages(id);
  res.json({ session, messages });
}

export async function handleCreateSession(req: Request, res: Response): Promise<void> {
  const body = req.body as { mode?: "chat" | "cowork"; title?: string };
  const mode = body.mode === "cowork" ? "cowork" : "chat";
  const session = await createSession(mode, body.title);
  res.status(201).json({ session });
}

export async function handleUpdateSession(req: Request, res: Response): Promise<void> {
  const { id } = req.params as { id: string };
  const body = req.body as { title?: string };
  const session = await getSession(id);
  if (!session) {
    res.status(404).json({ error: "Session not found" });
    return;
  }
  if (body.title) {
    await updateSessionTitle(id, body.title);
  }
  const updated = await getSession(id);
  res.json({ session: updated });
}

export async function handleDeleteSession(req: Request, res: Response): Promise<void> {
  const { id } = req.params as { id: string };
  const session = await getSession(id);
  if (!session) {
    res.status(404).json({ error: "Session not found" });
    return;
  }
  await deleteSession(id);
  res.json({ ok: true });
}

export async function handleEditSessionMessage(req: Request, res: Response): Promise<void> {
  const { id, messageId } = req.params as { id: string; messageId: string };
  const parsedMessageId = Number.parseInt(messageId, 10);
  if (!Number.isFinite(parsedMessageId) || parsedMessageId <= 0) {
    res.status(400).json({ error: "Invalid message id" });
    return;
  }

  const body = req.body as { content?: string };
  const content = String(body.content ?? "").trim();
  if (!content) {
    res.status(422).json({ error: "content is required" });
    return;
  }

  const result = await editUserMessage(id, parsedMessageId, content);
  if (!result.ok) {
    res.status(result.status).json({ error: result.error });
    return;
  }

  res.json({
    ok: true,
    message_id: parsedMessageId,
    pruned_messages: result.pruned_messages
  });
}
