export type GitHubAdapterConfig = {
  token: string;
  defaultOwner?: string;
  timeoutMs: number;
};

export class GitHubApiError extends Error {
  readonly status: number;
  readonly responseBody: string;

  constructor(status: number, responseBody: string) {
    super(`GitHub API ${status}: ${responseBody.slice(0, 250)}`);
    this.name = "GitHubApiError";
    this.status = status;
    this.responseBody = responseBody;
  }
}

export class GitHubAdapter {
  private readonly token: string;
  private readonly defaultOwner?: string;
  private readonly timeoutMs: number;
  private readonly baseUrl = "https://api.github.com";

  constructor(config: GitHubAdapterConfig) {
    this.token = config.token.trim();
    this.defaultOwner = config.defaultOwner?.trim();
    this.timeoutMs = config.timeoutMs;
  }

  isConfigured(): boolean {
    return this.token.length > 0;
  }

  getDefaultOwner(): string {
    return this.defaultOwner ?? "";
  }

  private resolveOwner(owner?: string): string {
    const resolved = (owner ?? this.defaultOwner ?? "").trim();
    if (!resolved) throw new Error("owner is required (or configure GITHUB_DEFAULT_OWNER)");
    return resolved;
  }

  // ─── READ ONLY ──────────────────────────────────────────────────────────────

  async listRepos(input: { owner?: string; sort?: string; limit?: number }) {
    const owner = this.resolveOwner(input.owner);
    const params = new URLSearchParams({
      type: "owner",
      sort: input.sort ?? "updated",
      per_page: String(Math.min(input.limit ?? 30, 100)),
    });
    const data = await this.requestJson<Record<string, unknown>[]>(
      `/users/${encodeURIComponent(owner)}/repos?${params}`
    );
    return {
      total: data.length,
      repos: data.map(r => ({
        name: r["name"],
        full_name: r["full_name"],
        description: r["description"],
        private: r["private"],
        default_branch: r["default_branch"],
        updated_at: r["updated_at"],
        language: r["language"],
        open_issues_count: r["open_issues_count"],
      })),
    };
  }

  async getFile(input: { owner?: string; repo: string; path: string; branch?: string }) {
    const owner = this.resolveOwner(input.owner);
    const params = input.branch ? `?ref=${encodeURIComponent(input.branch)}` : "";
    const data = await this.requestJson<Record<string, unknown>>(
      `/repos/${encodeURIComponent(owner)}/${encodeURIComponent(input.repo)}/contents/${input.path}${params}`
    );
    if (Array.isArray(data)) throw new Error("path is a directory — use a specific file path");
    if (data["type"] !== "file") throw new Error("not a file");
    const raw = String(data["content"] ?? "").replace(/\n/g, "");
    const content = Buffer.from(raw, "base64").toString("utf-8");
    return {
      name: data["name"],
      path: data["path"],
      sha: data["sha"],
      size: data["size"],
      content,
    };
  }

  async searchCode(input: { query: string; owner?: string; repo?: string; limit?: number }) {
    const owner = input.owner ?? this.defaultOwner;
    let q = input.query;
    if (owner && input.repo) q += ` repo:${owner}/${input.repo}`;
    else if (owner) q += ` user:${owner}`;
    const params = new URLSearchParams({ q, per_page: String(Math.min(input.limit ?? 20, 50)) });
    const data = await this.requestJson<{ total_count: number; items: Record<string, unknown>[] }>(
      `/search/code?${params}`
    );
    return {
      total_count: data.total_count,
      items: (data.items ?? []).map(i => ({
        name: i["name"],
        path: i["path"],
        repository: (i["repository"] as Record<string, unknown> | undefined)?.["full_name"],
        html_url: i["html_url"],
      })),
    };
  }

  async listIssues(input: {
    owner?: string;
    repo: string;
    state?: string;
    labels?: string[];
    limit?: number;
  }) {
    const owner = this.resolveOwner(input.owner);
    const params = new URLSearchParams({
      state: input.state ?? "open",
      per_page: String(Math.min(input.limit ?? 30, 100)),
    });
    if (input.labels?.length) params.set("labels", input.labels.join(","));
    const data = await this.requestJson<Record<string, unknown>[]>(
      `/repos/${encodeURIComponent(owner)}/${encodeURIComponent(input.repo)}/issues?${params}`
    );
    return {
      total: data.length,
      issues: data
        .filter(i => !i["pull_request"])
        .map(i => ({
          number: i["number"],
          title: i["title"],
          state: i["state"],
          labels: (i["labels"] as Record<string, unknown>[] | undefined ?? []).map(l => l["name"]),
          created_at: i["created_at"],
          updated_at: i["updated_at"],
          body: typeof i["body"] === "string" ? i["body"].slice(0, 500) : null,
        })),
    };
  }

  async getIssue(input: { owner?: string; repo: string; issue_number: number }) {
    const owner = this.resolveOwner(input.owner);
    const data = await this.requestJson<Record<string, unknown>>(
      `/repos/${encodeURIComponent(owner)}/${encodeURIComponent(input.repo)}/issues/${input.issue_number}`
    );
    return {
      number: data["number"],
      title: data["title"],
      state: data["state"],
      labels: (data["labels"] as Record<string, unknown>[] | undefined ?? []).map(l => l["name"]),
      body: data["body"],
      comments: data["comments"],
      created_at: data["created_at"],
      updated_at: data["updated_at"],
    };
  }

  async listPrs(input: { owner?: string; repo: string; state?: string; limit?: number }) {
    const owner = this.resolveOwner(input.owner);
    const params = new URLSearchParams({
      state: input.state ?? "open",
      per_page: String(Math.min(input.limit ?? 20, 50)),
    });
    const data = await this.requestJson<Record<string, unknown>[]>(
      `/repos/${encodeURIComponent(owner)}/${encodeURIComponent(input.repo)}/pulls?${params}`
    );
    return {
      total: data.length,
      prs: data.map(pr => ({
        number: pr["number"],
        title: pr["title"],
        state: pr["state"],
        base: (pr["base"] as Record<string, unknown> | undefined)?.["ref"],
        head: (pr["head"] as Record<string, unknown> | undefined)?.["ref"],
        created_at: pr["created_at"],
        draft: pr["draft"],
      })),
    };
  }

  async getPr(input: { owner?: string; repo: string; pull_number: number }) {
    const owner = this.resolveOwner(input.owner);
    const data = await this.requestJson<Record<string, unknown>>(
      `/repos/${encodeURIComponent(owner)}/${encodeURIComponent(input.repo)}/pulls/${input.pull_number}`
    );
    return {
      number: data["number"],
      title: data["title"],
      state: data["state"],
      base: (data["base"] as Record<string, unknown> | undefined)?.["ref"],
      head: (data["head"] as Record<string, unknown> | undefined)?.["ref"],
      body: data["body"],
      draft: data["draft"],
      mergeable: data["mergeable"],
      changed_files: data["changed_files"],
      additions: data["additions"],
      deletions: data["deletions"],
      created_at: data["created_at"],
    };
  }

  async listCommits(input: { owner?: string; repo: string; branch?: string; limit?: number }) {
    const owner = this.resolveOwner(input.owner);
    const params = new URLSearchParams({ per_page: String(Math.min(input.limit ?? 20, 50)) });
    if (input.branch) params.set("sha", input.branch);
    const data = await this.requestJson<Record<string, unknown>[]>(
      `/repos/${encodeURIComponent(owner)}/${encodeURIComponent(input.repo)}/commits?${params}`
    );
    return {
      total: data.length,
      commits: data.map(c => {
        const commit = (c["commit"] as Record<string, unknown> | undefined) ?? {};
        const author = (commit["author"] as Record<string, unknown> | undefined) ?? {};
        const sha = String(c["sha"] ?? "");
        return {
          sha: sha.slice(0, 7),
          full_sha: sha,
          message: String(commit["message"] ?? "").split("\n")[0],
          author: author["name"],
          date: author["date"],
        };
      }),
    };
  }

  // ─── SAFE EXECUTE ───────────────────────────────────────────────────────────

  async createIssue(input: {
    owner?: string;
    repo: string;
    title: string;
    body?: string;
    labels?: string[];
  }) {
    const owner = this.resolveOwner(input.owner);
    const data = await this.requestJson<Record<string, unknown>>(
      `/repos/${encodeURIComponent(owner)}/${encodeURIComponent(input.repo)}/issues`,
      { method: "POST", body: { title: input.title, body: input.body, labels: input.labels } }
    );
    return {
      number: data["number"],
      title: data["title"],
      html_url: data["html_url"],
      created_at: data["created_at"],
    };
  }

  async addIssueComment(input: {
    owner?: string;
    repo: string;
    issue_number: number;
    body: string;
  }) {
    const owner = this.resolveOwner(input.owner);
    const data = await this.requestJson<Record<string, unknown>>(
      `/repos/${encodeURIComponent(owner)}/${encodeURIComponent(input.repo)}/issues/${input.issue_number}/comments`,
      { method: "POST", body: { body: input.body } }
    );
    return {
      id: data["id"],
      html_url: data["html_url"],
      created_at: data["created_at"],
    };
  }

  // ─── APPROVAL REQUIRED ──────────────────────────────────────────────────────

  async pushFile(input: {
    owner?: string;
    repo: string;
    path: string;
    content: string;
    message: string;
    branch?: string;
    sha?: string;
  }) {
    const owner = this.resolveOwner(input.owner);
    let sha = input.sha;

    // Auto-fetch SHA when updating an existing file
    if (!sha) {
      try {
        const params = input.branch ? `?ref=${encodeURIComponent(input.branch)}` : "";
        const existing = await this.requestJson<Record<string, unknown>>(
          `/repos/${encodeURIComponent(owner)}/${encodeURIComponent(input.repo)}/contents/${input.path}${params}`
        );
        if (!Array.isArray(existing) && existing["sha"]) {
          sha = String(existing["sha"]);
        }
      } catch (e) {
        // 404 = new file, no SHA needed
        if (!(e instanceof GitHubApiError) || e.status !== 404) throw e;
      }
    }

    const body: Record<string, unknown> = {
      message: input.message,
      content: Buffer.from(input.content).toString("base64"),
    };
    if (sha) body["sha"] = sha;
    if (input.branch) body["branch"] = input.branch;

    const data = await this.requestJson<Record<string, unknown>>(
      `/repos/${encodeURIComponent(owner)}/${encodeURIComponent(input.repo)}/contents/${input.path}`,
      { method: "PUT", body }
    );

    const contentData = (data["content"] as Record<string, unknown> | undefined) ?? {};
    const commitData = (data["commit"] as Record<string, unknown> | undefined) ?? {};
    return {
      path: contentData["path"],
      sha: contentData["sha"],
      commit_sha: String(commitData["sha"] ?? "").slice(0, 7),
    };
  }

  async createPr(input: {
    owner?: string;
    repo: string;
    title: string;
    body?: string;
    head: string;
    base: string;
    draft?: boolean;
  }) {
    const owner = this.resolveOwner(input.owner);
    const data = await this.requestJson<Record<string, unknown>>(
      `/repos/${encodeURIComponent(owner)}/${encodeURIComponent(input.repo)}/pulls`,
      {
        method: "POST",
        body: {
          title: input.title,
          body: input.body,
          head: input.head,
          base: input.base,
          draft: input.draft ?? false,
        },
      }
    );
    return {
      number: data["number"],
      title: data["title"],
      html_url: data["html_url"],
      created_at: data["created_at"],
    };
  }

  // ─── INTERNAL ───────────────────────────────────────────────────────────────

  private async requestJson<T>(path: string, options?: { method?: string; body?: unknown }): Promise<T> {
    const controller = new AbortController();
    const timeout = setTimeout(() => controller.abort(), this.timeoutMs);

    try {
      const method = options?.method ?? "GET";
      const response = await fetch(`${this.baseUrl}${path}`, {
        method,
        headers: {
          Authorization: `Bearer ${this.token}`,
          Accept: "application/vnd.github.v3+json",
          "Content-Type": "application/json",
          "User-Agent": "vee-mcp-server/0.4",
          "X-GitHub-Api-Version": "2022-11-28",
        },
        body: options?.body !== undefined ? JSON.stringify(options.body) : undefined,
        signal: controller.signal,
      });

      if (!response.ok) {
        const body = await response.text();
        throw new GitHubApiError(response.status, body);
      }

      const text = await response.text();
      if (!text) return {} as T;

      try {
        return JSON.parse(text) as T;
      } catch {
        throw new Error("GitHub API returned a non-JSON payload.");
      }
    } catch (error) {
      if (error instanceof Error && error.name === "AbortError") {
        throw new Error(`GitHub API timeout after ${this.timeoutMs}ms.`);
      }
      throw error;
    } finally {
      clearTimeout(timeout);
    }
  }
}
