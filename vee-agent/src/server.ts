import "dotenv/config";
import express, { type NextFunction, type Request, type Response } from "express";
import { PORT, HOST, AUTH_TOKEN, MODEL_CHAT, MODEL_COWORK, MCP_SERVER_URL } from "./config.js";
import { ensureTables } from "./db.js";
import { handleStream } from "./routes/stream.js";
import {
  handleListSessions,
  handleGetSession,
  handleCreateSession,
  handleUpdateSession,
  handleDeleteSession
} from "./routes/sessions.js";
import { handleLogFeed } from "./routes/log.js";

const app = express();
app.use(express.json({ limit: "10mb" }));

// ─── Auth middleware ──────────────────────────────────────────────────────────

function withAuth(req: Request, res: Response, next: NextFunction): void {
  if (!AUTH_TOKEN) {
    next();
    return;
  }
  const authorization = req.header("authorization") ?? "";
  if (authorization !== `Bearer ${AUTH_TOKEN}`) {
    res.status(401).json({ error: "unauthorized" });
    return;
  }
  next();
}

// ─── CORS (allow all for now — tighten per env later) ────────────────────────

app.use((_req, res, next) => {
  res.setHeader("Access-Control-Allow-Origin", "*");
  res.setHeader("Access-Control-Allow-Headers", "Authorization, Content-Type");
  res.setHeader("Access-Control-Allow-Methods", "GET, POST, PATCH, DELETE, OPTIONS");
  next();
});

app.options("*", (_req, res) => {
  res.sendStatus(204);
});

// ─── Health ───────────────────────────────────────────────────────────────────

app.get("/health", (_req, res) => {
  res.json({
    name: "vee-agent",
    version: "1.0.0",
    status: "ok",
    now: new Date().toISOString(),
    model_chat: MODEL_CHAT,
    model_cowork: MODEL_COWORK,
    mcp_server: MCP_SERVER_URL,
    auth: AUTH_TOKEN ? "protected" : "open"
  });
});

// ─── Stream ───────────────────────────────────────────────────────────────────

app.post("/stream", withAuth, (req, res) => {
  handleStream(req, res).catch((err: unknown) => {
    const message = err instanceof Error ? err.message : String(err);
    if (!res.headersSent) {
      res.status(500).json({ error: message });
    }
  });
});

// ─── Sessions (tarefas) ───────────────────────────────────────────────────────

app.get("/sessions", withAuth, (req, res) => {
  handleListSessions(req, res).catch((err: unknown) => {
    const message = err instanceof Error ? err.message : String(err);
    res.status(500).json({ error: message });
  });
});

app.post("/sessions", withAuth, (req, res) => {
  handleCreateSession(req, res).catch((err: unknown) => {
    const message = err instanceof Error ? err.message : String(err);
    res.status(500).json({ error: message });
  });
});

app.get("/sessions/:id", withAuth, (req, res) => {
  handleGetSession(req, res).catch((err: unknown) => {
    const message = err instanceof Error ? err.message : String(err);
    res.status(500).json({ error: message });
  });
});

app.patch("/sessions/:id", withAuth, (req, res) => {
  handleUpdateSession(req, res).catch((err: unknown) => {
    const message = err instanceof Error ? err.message : String(err);
    res.status(500).json({ error: message });
  });
});

app.delete("/sessions/:id", withAuth, (req, res) => {
  handleDeleteSession(req, res).catch((err: unknown) => {
    const message = err instanceof Error ? err.message : String(err);
    res.status(500).json({ error: message });
  });
});

// ─── Log feed (SSE) ───────────────────────────────────────────────────────────

app.get("/log/feed", withAuth, handleLogFeed);

// ─── Boot ─────────────────────────────────────────────────────────────────────

async function boot(): Promise<void> {
  await ensureTables();
  console.log("[Vee Agent] DB tables ensured");

  app.listen(PORT, HOST, () => {
    console.log(`[Vee Agent] listening on http://${HOST}:${PORT}`);
    console.log(`[Vee Agent] auth: ${AUTH_TOKEN ? "Bearer token required" : "open"}`);
    console.log(`[Vee Agent] model_chat: ${MODEL_CHAT}`);
    console.log(`[Vee Agent] model_cowork: ${MODEL_COWORK}`);
    console.log(`[Vee Agent] mcp_server: ${MCP_SERVER_URL}`);
    console.log("[Vee Agent] endpoints: POST /stream, GET /sessions, GET /log/feed");
  });
}

boot().catch((err: unknown) => {
  console.error("[Vee Agent] fatal boot error:", err);
  process.exit(1);
});
