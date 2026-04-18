export type VeeControlConfig = {
  baseUrl: string;
  token: string;
  timeoutMs: number;
};

export type ApprovalStatus = "pending" | "approved" | "rejected" | "executed";

export type ApprovalEntity = {
  approval_id: string;
  status: ApprovalStatus;
  request_payload?: Record<string, unknown> | null;
  summary?: string | null;
  approved_at?: string | null;
  rejected_at?: string | null;
  executed_at?: string | null;
};

export class VeeControlAdapter {
  private readonly baseUrl: string;
  private readonly token: string;
  private readonly timeoutMs: number;

  constructor(config: VeeControlConfig) {
    this.baseUrl = config.baseUrl.replace(/\/+$/, "");
    this.token = config.token.trim();
    this.timeoutMs = config.timeoutMs;
  }

  async createApproval(payload: {
    action_name: string;
    tool_name: string;
    summary?: string;
    request_payload?: Record<string, unknown>;
    meta?: Record<string, unknown>;
  }): Promise<{ approval_id: string; status: ApprovalStatus }> {
    this.assertConfigured();

    return this.requestJson<{ ok: boolean; approval_id: string; status: ApprovalStatus }>(
      "/approvals",
      {
        method: "POST",
        body: payload,
      }
    );
  }

  async getApproval(approvalId: string): Promise<ApprovalEntity> {
    this.assertConfigured();

    const response = await this.requestJson<{ ok: boolean; approval: ApprovalEntity }>(
      `/approvals/${encodeURIComponent(approvalId)}`
    );

    return response.approval;
  }

  async markApprovalExecuted(approvalId: string, meta?: Record<string, unknown>): Promise<void> {
    this.assertConfigured();

    await this.requestJson<{ ok: boolean; approval_id: string; status: ApprovalStatus }>(
      `/approvals/${encodeURIComponent(approvalId)}/executed`,
      {
        method: "POST",
        body: { status: "executed", meta: meta ?? {} },
      }
    );
  }

  private assertConfigured(): void {
    if (!this.baseUrl) {
      throw new Error("VEE_CONTROL_BASE_URL is not configured.");
    }

    if (!this.token) {
      throw new Error("VEE_CONTROL_TOKEN is not configured.");
    }
  }

  private async requestJson<T>(
    path: string,
    options?: {
      method?: "GET" | "POST";
      body?: unknown;
    }
  ): Promise<T> {
    const controller = new AbortController();
    const timeout = setTimeout(() => controller.abort(), this.timeoutMs);

    try {
      const response = await fetch(`${this.baseUrl}${path}`, {
        method: options?.method ?? "GET",
        headers: {
          "X-VEE-Internal-Token": this.token,
          Accept: "application/json",
          "Content-Type": "application/json",
        },
        body: options?.body !== undefined ? JSON.stringify(options.body) : undefined,
        signal: controller.signal,
      });

      if (!response.ok) {
        const body = await response.text();
        throw new Error(`VEE control API ${response.status}: ${body.slice(0, 250)}`);
      }

      return (await response.json()) as T;
    } catch (error) {
      if (error instanceof Error && error.name === "AbortError") {
        throw new Error(`VEE control API timeout after ${this.timeoutMs}ms.`);
      }
      throw error;
    } finally {
      clearTimeout(timeout);
    }
  }
}
