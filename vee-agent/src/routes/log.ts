import type { Request, Response } from "express";
import { eventBus, type LogEvent } from "../eventBus.js";

// SSE endpoint — clients subscribe to get all log events in real time
export function handleLogFeed(req: Request, res: Response): void {
  res.setHeader("Content-Type", "text/event-stream");
  res.setHeader("Cache-Control", "no-cache");
  res.setHeader("Connection", "keep-alive");
  res.setHeader("X-Accel-Buffering", "no");
  res.flushHeaders();

  const sessionFilter = typeof req.query["session_id"] === "string"
    ? req.query["session_id"]
    : null;

  // Send a ping immediately so the client knows the connection is alive
  res.write(`data: ${JSON.stringify({ type: "connected", timestamp: new Date().toISOString() })}\n\n`);

  const listener = (event: LogEvent) => {
    if (sessionFilter && event.session_id !== sessionFilter) return;
    res.write(`data: ${JSON.stringify(event)}\n\n`);
  };

  eventBus.onLog(listener);

  // Heartbeat every 30s to keep connection alive through proxies
  const heartbeat = setInterval(() => {
    res.write(`: heartbeat\n\n`);
  }, 30_000);

  req.on("close", () => {
    clearInterval(heartbeat);
    eventBus.offLog(listener);
  });
}
