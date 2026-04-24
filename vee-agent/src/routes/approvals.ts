import type { Request, Response } from "express";
import { VEE_CONTROL_BASE_URL, VEE_CONTROL_TOKEN, VEE_CONTROL_TIMEOUT_MS } from "../config.js";

type ApprovalStatus = "pending" | "approved" | "rejected" | "executed";

function buildBaseUrl(): string {
  const base = VEE_CONTROL_BASE_URL.trim();
  if (!base) {
    throw new Error("VEE_CONTROL_BASE_URL is not configured.");
  }
  return base.endsWith("/") ? base.slice(0, -1) : base;
}

function buildHeaders(): Record<string, string> {
  const token = VEE_CONTROL_TOKEN.trim();
  const headers: Record<string, string> = {
    Accept: "application/json",
    "Content-Type": "application/json"
  };
  if (token) {
    headers["X-VEE-Internal-Token"] = token;
  }
  return headers;
}

async function requestControl<T>(path: string, options?: { method?: "GET" | "POST"; body?: Record<string, unknown> }): Promise<T> {
  const base = buildBaseUrl();
  const controller = new AbortController();
  const timer = setTimeout(() => controller.abort(), Math.max(1000, VEE_CONTROL_TIMEOUT_MS));

  try {
    const response = await fetch(`${base}${path}`, {
      method: options?.method ?? "GET",
      headers: buildHeaders(),
      body: options?.body ? JSON.stringify(options.body) : undefined,
      signal: controller.signal
    });

    const text = await response.text();
    const json = text ? (JSON.parse(text) as Record<string, unknown>) : {};

    if (!response.ok) {
      const message =
        typeof json["message"] === "string"
          ? json["message"]
          : `Control API request failed (${response.status})`;
      throw new Error(message);
    }

    return json as T;
  } finally {
    clearTimeout(timer);
  }
}

function sanitizeStatus(value: unknown): ApprovalStatus | undefined {
  if (typeof value !== "string") {
    return undefined;
  }
  const normalized = value.trim().toLowerCase();
  if (normalized === "pending" || normalized === "approved" || normalized === "rejected" || normalized === "executed") {
    return normalized;
  }
  return undefined;
}

function sanitizeLimit(value: unknown): number {
  const n = Number(value);
  if (!Number.isFinite(n)) {
    return 50;
  }
  return Math.max(1, Math.min(200, Math.trunc(n)));
}

export async function handleListApprovals(req: Request, res: Response): Promise<void> {
  const status = sanitizeStatus(req.query.status);
  const limit = sanitizeLimit(req.query.limit);
  const actionName = typeof req.query.action_name === "string" ? req.query.action_name.trim() : "";
  const toolName = typeof req.query.tool_name === "string" ? req.query.tool_name.trim() : "";

  const query = new URLSearchParams();
  query.set("limit", String(limit));
  if (status) query.set("status", status);
  if (actionName) query.set("action_name", actionName);
  if (toolName) query.set("tool_name", toolName);

  const data = await requestControl<{ ok: boolean; total?: number; approvals?: unknown[] }>(
    `/approvals?${query.toString()}`
  );

  res.json({
    ok: true,
    total: Number(data.total ?? 0),
    approvals: Array.isArray(data.approvals) ? data.approvals : []
  });
}

export async function handleGetApproval(req: Request, res: Response): Promise<void> {
  const approvalId = String(req.params.id ?? "").trim();
  if (!approvalId) {
    res.status(400).json({ error: "approval id is required" });
    return;
  }

  const data = await requestControl<{ ok: boolean; approval?: unknown }>(`/approvals/${encodeURIComponent(approvalId)}`);
  res.json({
    ok: true,
    approval: data.approval ?? null
  });
}

export async function handleApproveApproval(req: Request, res: Response): Promise<void> {
  const approvalId = String(req.params.id ?? "").trim();
  if (!approvalId) {
    res.status(400).json({ error: "approval id is required" });
    return;
  }

  const approvedBy =
    typeof req.body?.approved_by === "string" && req.body.approved_by.trim()
      ? req.body.approved_by.trim()
      : "vee-drawer";

  const data = await requestControl<{ ok: boolean; approval_id?: string; status?: string }>(
    `/approvals/${encodeURIComponent(approvalId)}/approve`,
    {
      method: "POST",
      body: {
        approved_by: approvedBy
      }
    }
  );

  res.json({
    ok: true,
    approval_id: data.approval_id ?? approvalId,
    status: data.status ?? "approved"
  });
}

export async function handleRejectApproval(req: Request, res: Response): Promise<void> {
  const approvalId = String(req.params.id ?? "").trim();
  if (!approvalId) {
    res.status(400).json({ error: "approval id is required" });
    return;
  }

  const rejectedBy =
    typeof req.body?.rejected_by === "string" && req.body.rejected_by.trim()
      ? req.body.rejected_by.trim()
      : "vee-drawer";
  const reason = typeof req.body?.reason === "string" ? req.body.reason.trim() : "";

  const data = await requestControl<{ ok: boolean; approval_id?: string; status?: string }>(
    `/approvals/${encodeURIComponent(approvalId)}/reject`,
    {
      method: "POST",
      body: {
        rejected_by: rejectedBy,
        reason: reason || null
      }
    }
  );

  res.json({
    ok: true,
    approval_id: data.approval_id ?? approvalId,
    status: data.status ?? "rejected"
  });
}
