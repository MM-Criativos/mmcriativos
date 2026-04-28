type N8nListResponse<T> = {
  data?: T[];
  nextCursor?: string;
};

export type N8nWorkflowSummary = {
  id: string;
  name: string;
  active: boolean;
  isArchived: boolean;
  updatedAt?: string;
  createdAt?: string;
};

export type N8nAdapterConfig = {
  baseUrl: string;
  apiKey: string;
  timeoutMs: number;
};

export type N8nExecutionSummary = {
  id: string;
  workflowId?: string;
  workflowName?: string;
  status?: string;
  mode?: string;
  startedAt?: string;
  stoppedAt?: string;
  finished?: boolean;
  retryOf?: string;
  retrySuccessId?: string;
};

type RequestOptions = {
  method?: "GET" | "POST" | "PUT" | "PATCH" | "DELETE";
  body?: unknown;
};

export class N8nApiError extends Error {
  readonly status: number;
  readonly responseBody: string;

  constructor(status: number, responseBody: string) {
    super(`N8N API ${status}: ${responseBody.slice(0, 250)}`);
    this.name = "N8nApiError";
    this.status = status;
    this.responseBody = responseBody;
  }
}

export class N8nRestAdapter {
  private readonly baseUrl: string;
  private readonly apiKey: string;
  private readonly timeoutMs: number;

  constructor(config: N8nAdapterConfig) {
    this.baseUrl = config.baseUrl.replace(/\/+$/, "");
    this.apiKey = config.apiKey.trim();
    this.timeoutMs = config.timeoutMs;
  }

  async listWorkflows(limit = 100): Promise<N8nWorkflowSummary[]> {
    this.assertConfigured();

    const workflows: N8nWorkflowSummary[] = [];
    let cursor: string | undefined;
    let safetyCounter = 0;

    do {
      const query = new URLSearchParams({ limit: String(limit) });
      if (cursor) {
        query.set("cursor", cursor);
      }

      const response = await this.requestJson<N8nListResponse<Record<string, unknown>>>(
        `/workflows?${query.toString()}`
      );

      const page = Array.isArray(response.data) ? response.data : [];
      workflows.push(
        ...page.map((row) => ({
          id: String(row.id ?? ""),
          name: String(row.name ?? ""),
          active: Boolean(row.active),
          isArchived: Boolean(row.isArchived),
          updatedAt: this.optionalString(row.updatedAt),
          createdAt: this.optionalString(row.createdAt)
        }))
      );

      cursor = response.nextCursor;
      safetyCounter += 1;
      if (safetyCounter > 50) {
        throw new Error("N8N pagination exceeded safety limit.");
      }
    } while (cursor);

    return workflows.sort((a, b) => a.name.localeCompare(b.name, "pt-BR"));
  }

  async getWorkflow(workflowId: string): Promise<Record<string, unknown>> {
    this.assertConfigured();
    const normalizedId = workflowId.trim();
    if (!normalizedId) {
      throw new Error("workflowId is required.");
    }

    return this.requestJson<Record<string, unknown>>(`/workflows/${encodeURIComponent(normalizedId)}`);
  }

  async listExecutions(params?: {
    workflowId?: string;
    status?: string;
    limit?: number;
  }): Promise<N8nExecutionSummary[]> {
    this.assertConfigured();

    const query = new URLSearchParams();
    if (params?.workflowId) {
      query.set("workflowId", params.workflowId.trim());
    }
    if (params?.status) {
      query.set("status", params.status.trim());
    }
    query.set("limit", String(params?.limit ?? 10));

    const response = await this.requestJson<N8nListResponse<Record<string, unknown>>>(
      `/executions?${query.toString()}`
    );

    const rows = Array.isArray(response.data) ? response.data : [];
    return rows.map((row) => ({
      id: String(row.id ?? ""),
      workflowId: this.optionalString(row.workflowId),
      workflowName: this.optionalString(row.workflowName),
      status: this.optionalString(row.status),
      mode: this.optionalString(row.mode),
      startedAt: this.optionalString(row.startedAt),
      stoppedAt: this.optionalString(row.stoppedAt),
      finished: typeof row.finished === "boolean" ? row.finished : undefined,
      retryOf: this.optionalString(row.retryOf),
      retrySuccessId: this.optionalString(row.retrySuccessId)
    }));
  }

  async getExecution(executionId: string, includeData = false): Promise<Record<string, unknown>> {
    this.assertConfigured();
    const normalizedId = this.normalizeId(executionId, "executionId");

    const query = new URLSearchParams();
    if (includeData) {
      query.set("includeData", "true");
    }

    const suffix = query.size > 0 ? `?${query.toString()}` : "";
    return this.requestJson<Record<string, unknown>>(
      `/executions/${encodeURIComponent(normalizedId)}${suffix}`
    );
  }

  async retryExecution(executionId: string, loadWorkflow = false): Promise<Record<string, unknown>> {
    this.assertConfigured();
    const normalizedId = this.normalizeId(executionId, "executionId");

    return this.requestJson<Record<string, unknown>>(
      `/executions/${encodeURIComponent(normalizedId)}/retry`,
      {
        method: "POST",
        body: {
          loadWorkflow: Boolean(loadWorkflow)
        }
      }
    );
  }

  async stopExecution(executionId: string, options?: { allowDeleteFallback?: boolean }): Promise<Record<string, unknown>> {
    this.assertConfigured();
    const normalizedId = this.normalizeId(executionId, "executionId");

    try {
      return await this.requestJson<Record<string, unknown>>(
        `/executions/${encodeURIComponent(normalizedId)}/stop`,
        {
          method: "POST"
        }
      );
    } catch (error) {
      if (error instanceof N8nApiError && (error.status === 404 || error.status === 405)) {
        if (!options?.allowDeleteFallback) {
          throw new Error(
            "Execution stop endpoint is not available in this n8n version. Set allowDeleteFallback=true to try DELETE /executions/{id}."
          );
        }

        return this.requestJson<Record<string, unknown>>(
          `/executions/${encodeURIComponent(normalizedId)}`,
          {
            method: "DELETE"
          }
        );
      }

      throw error;
    }
  }

  async updateWorkflow(workflowId: string, workflowPayload: Record<string, unknown>): Promise<Record<string, unknown>> {
    this.assertConfigured();

    const normalizedId = this.normalizeId(workflowId, "workflowId");

    if (!workflowPayload || typeof workflowPayload !== "object") {
      throw new Error("workflow payload must be an object.");
    }

    // n8n PUT /workflows/{id} only accepts these fields — all others are read-only and rejected.
    // Build the payload explicitly to avoid TypeScript issues.
    const writablePayload: Record<string, unknown> = {
      name: workflowPayload["name"],
      nodes: workflowPayload["nodes"],
      connections: workflowPayload["connections"],
      settings: workflowPayload["settings"] ?? {},
      staticData: workflowPayload["staticData"] ?? null
    };

    return this.requestJson<Record<string, unknown>>(`/workflows/${encodeURIComponent(normalizedId)}`, {
      method: "PUT",
      body: writablePayload
    });
  }

  private assertConfigured(): void {
    if (!this.baseUrl) {
      throw new Error("N8N_BASE_URL is not configured.");
    }
    if (!this.apiKey) {
      throw new Error("N8N_API_KEY is not configured.");
    }
  }

  private async requestJson<T>(path: string, options?: RequestOptions): Promise<T> {
    const controller = new AbortController();
    const timeout = setTimeout(() => controller.abort(), this.timeoutMs);

    try {
      const method = options?.method ?? "GET";
      const response = await fetch(`${this.baseUrl}${path}`, {
        method,
        headers: {
          "X-N8N-API-KEY": this.apiKey,
          Accept: "application/json",
          "Content-Type": "application/json"
        },
        body: options?.body !== undefined ? JSON.stringify(options.body) : undefined,
        signal: controller.signal
      });

      if (!response.ok) {
        const body = await response.text();
        throw new N8nApiError(response.status, body);
      }

      const text = await response.text();
      if (!text) {
        return {} as T;
      }

      try {
        return JSON.parse(text) as T;
      } catch {
        throw new Error("N8N API returned a non-JSON payload.");
      }
    } catch (error) {
      if (error instanceof Error && error.name === "AbortError") {
        throw new Error(`N8N API timeout after ${this.timeoutMs}ms.`);
      }
      throw error;
    } finally {
      clearTimeout(timeout);
    }
  }

  private optionalString(value: unknown): string | undefined {
    return typeof value === "string" && value.length > 0 ? value : undefined;
  }

  private normalizeId(value: string, fieldName: string): string {
    const normalized = value.trim();
    if (!normalized) {
      throw new Error(`${fieldName} is required.`);
    }

    return normalized;
  }
}
