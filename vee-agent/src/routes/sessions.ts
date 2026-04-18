import type { Request, Response } from "express";
import {
  listSessions,
  getSession,
  createSession,
  updateSessionTitle,
  deleteSession,
  loadHistory
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
  const messages = await loadHistory(id);
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
