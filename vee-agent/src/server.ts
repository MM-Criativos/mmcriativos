import "dotenv/config";
import express, { type NextFunction, type Request, type Response } from "express";
import {
  PORT,
  HOST,
  AUTH_TOKEN,
  MODEL_CHAT,
  MODEL_COWORK,
  MCP_SERVER_URL,
  VEE_CONTROL_BASE_URL
} from "./config.js";
import { ensureTables } from "./db.js";
import { handleStream } from "./routes/stream.js";
import {
  handleListSessions,
  handleGetSession,
  handleCreateSession,
  handleUpdateSession,
  handleDeleteSession,
  handleEditSessionMessage
} from "./routes/sessions.js";
import { handleLogFeed } from "./routes/log.js";
import {
  handleApproveApproval,
  handleGetApproval,
  handleListApprovals,
  handleRejectApproval
} from "./routes/approvals.js";

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

// ─── CORS ────────────────────────────────────────────────────────────────────

app.use((_req, res, next) => {
  res.setHeader("Access-Control-Allow-Origin", "*");
  res.setHeader("Access-Control-Allow-Headers", "Authorization, Content-Type");
  res.setHeader("Access-Control-Allow-Methods", "GET, POST, PATCH, DELETE, OPTIONS");
  if (_req.method === "OPTIONS") {
    res.sendStatus(204);
    return;
  }
  next();
});

// ─── DB state (set during boot) ──────────────────────────────────────────────

let dbReady = false;
let dbError: string | null = null;

// ─── Health ───────────────────────────────────────────────────────────────────

app.get("/health", (_req, res) => {
  res.json({
    name: "vee-agent",
    version: "1.0.0",
    status: dbReady ? "ok" : "degraded",
    db: dbReady ? "ok" : `error: ${dbError ?? "not connected"}`,
    now: new Date().toISOString(),
    model_chat: MODEL_CHAT,
    model_cowork: MODEL_COWORK,
    mcp_server: MCP_SERVER_URL,
    vee_control: VEE_CONTROL_BASE_URL ? "configured" : "missing",
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

app.patch("/sessions/:id/messages/:messageId", withAuth, (req, res) => {
  handleEditSessionMessage(req, res).catch((err: unknown) => {
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

// ─── Approvals proxy (safe UI integration) ────────────────────────────────────

app.get("/approvals", withAuth, (req, res) => {
  handleListApprovals(req, res).catch((err: unknown) => {
    const message = err instanceof Error ? err.message : String(err);
    res.status(500).json({ error: message });
  });
});

app.get("/approvals/:id", withAuth, (req, res) => {
  handleGetApproval(req, res).catch((err: unknown) => {
    const message = err instanceof Error ? err.message : String(err);
    res.status(500).json({ error: message });
  });
});

app.post("/approvals/:id/approve", withAuth, (req, res) => {
  handleApproveApproval(req, res).catch((err: unknown) => {
    const message = err instanceof Error ? err.message : String(err);
    res.status(500).json({ error: message });
  });
});

app.post("/approvals/:id/reject", withAuth, (req, res) => {
  handleRejectApproval(req, res).catch((err: unknown) => {
    const message = err instanceof Error ? err.message : String(err);
    res.status(500).json({ error: message });
  });
});

// ─── Boot ─────────────────────────────────────────────────────────────────────

async function boot(): Promise<void> {
  // Start server first — /health responds even if DB is not ready
  await new Promise<void>((resolve) => {
    app.listen(PORT, HOST, () => {
      console.log(`[Vee Agent] listening on http://${HOST}:${PORT}`);
      console.log(`[Vee Agent] auth: ${AUTH_TOKEN ? "Bearer token required" : "open"}`);
      console.log(`[Vee Agent] model_chat: ${MODEL_CHAT}`);
      console.log(`[Vee Agent] model_cowork: ${MODEL_COWORK}`);
      console.log(`[Vee Agent] mcp_server: ${MCP_SERVER_URL}`);
      console.log("[Vee Agent] endpoints: POST /stream, GET /sessions, GET /log/feed, GET /approvals");
      resolve();
    });
  });

  // Connect to DB (non-fatal — server stays up, /health reports degraded)
  try {
    await ensureTables();
    dbReady = true;
    console.log("[Vee Agent] DB ready");
  } catch (err: unknown) {
    dbError = err instanceof Error ? err.message : String(err);
    console.error("[Vee Agent] DB connection failed:", dbError);
    console.error("[Vee Agent] Check env vars: DB_HOST, DB_PORT, DB_USER, DB_PASSWORD, DB_DATABASE");
  }
}

boot().catch((err: unknown) => {
  console.error("[Vee Agent] fatal boot error:", err);
  process.exit(1);
});
