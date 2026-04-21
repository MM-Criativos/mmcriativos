import { promises as fs } from "node:fs";
import { Client } from "ssh2";

export type ServerSshAdapterConfig = {
  host: string;
  port: number;
  username: string;
  password: string;
  privateKeyPath: string;
  readyTimeoutMs: number;
  commandTimeoutMs: number;
  restartEnabled: boolean;
  allowedContainers: string[];
  allowedServices: string[];
  allowedLogPaths: string[];
  fsAllowedRoots: string[];
  fsMaxFileBytes: number;
  fsWriteEnabled: boolean;
  fsWriteAllowedPaths: string[];
};

export type ServerCommandResult = {
  stdout: string;
  stderr: string;
  exitCode: number | null;
  signal: string | null;
};

export type ServerContainerInfo = {
  name: string;
  status: string;
  image: string;
};

export type ServerContainerDetailedInfo = {
  name: string;
  status: string;
  image: string;
  ports: string;
  id: string;
};

export type ServerDiskEntry = {
  filesystem: string;
  size: string;
  used: string;
  avail: string;
  use_percent: string;
  mounted_on: string;
};

export type ServerMemoryInfo = {
  total_mb: number;
  used_mb: number;
  free_mb: number;
  available_mb: number;
  use_percent: number;
};

export type ServerContainerInspect = {
  name: string;
  image: string;
  status: string;
  started_at: string | null;
  restart_policy: string | null;
  env_count: number;
  mounts: Array<{ source: string; destination: string; mode: string }>;
  ports: Record<string, unknown>;
};

export type ServerHealthCheckResult = {
  ssh_ok: boolean;
  host_name: string | null;
  uptime: string | null;
  disk: ServerDiskEntry[];
  memory: ServerMemoryInfo | null;
  container_count: number;
};

export type FsEntry = {
  name: string;
  type: "file" | "dir" | "link" | "other";
  size_bytes: number | null;
  modified_at: string | null;
};

export type FsReadResult = {
  path: string;
  content: string;
  total_bytes: number;
  truncated: boolean;
};

export type FsSearchMatch = {
  file: string;
  line: number;
  text: string;
};

export type FsSearchResult = {
  path: string;
  pattern: string;
  matches: FsSearchMatch[];
  truncated: boolean;
};

export type FsWriteResult = {
  ok: boolean;
  path: string;
};

const DEFAULT_ALLOWED_SERVICES = ["docker", "nginx", "mysql", "postgresql", "redis", "n8n-worker"];
const DEFAULT_ALLOWED_LOG_PATHS = [
  "/var/log/nginx",
  "/var/log/mysql",
  "/var/log/postgresql",
  "/var/log/docker",
  "/var/log/syslog"
];

export class ServerSshAdapter {
  private readonly host: string;
  private readonly port: number;
  private readonly username: string;
  private readonly password: string;
  private readonly privateKeyPath: string;
  private readonly readyTimeoutMs: number;
  private readonly commandTimeoutMs: number;
  private readonly restartEnabled: boolean;
  private readonly allowedContainers: string[];
  private readonly allowedServices: string[];
  private readonly allowedLogPaths: string[];
  private readonly fsAllowedRoots: string[];
  private readonly fsMaxFileBytes: number;
  private readonly fsWriteEnabled: boolean;
  private readonly fsWriteAllowedPaths: string[];

  constructor(config: ServerSshAdapterConfig) {
    this.host = config.host.trim();
    this.port = Number.isInteger(config.port) && config.port > 0 ? config.port : 22;
    this.username = config.username.trim();
    this.password = config.password;
    this.privateKeyPath = config.privateKeyPath.trim();
    this.readyTimeoutMs = config.readyTimeoutMs;
    this.commandTimeoutMs = config.commandTimeoutMs;
    this.restartEnabled = config.restartEnabled;
    this.allowedContainers = config.allowedContainers.map(s => s.trim()).filter(Boolean);
    this.allowedServices =
      config.allowedServices.length > 0
        ? config.allowedServices.map(s => s.trim()).filter(Boolean)
        : DEFAULT_ALLOWED_SERVICES;
    this.allowedLogPaths =
      config.allowedLogPaths.length > 0
        ? config.allowedLogPaths.map(s => s.trim()).filter(Boolean)
        : DEFAULT_ALLOWED_LOG_PATHS;
    this.fsAllowedRoots = config.fsAllowedRoots.map(s => s.trim()).filter(Boolean);
    this.fsMaxFileBytes = config.fsMaxFileBytes > 0 ? config.fsMaxFileBytes : 262144;
    this.fsWriteEnabled = config.fsWriteEnabled;
    this.fsWriteAllowedPaths = config.fsWriteAllowedPaths.map(s => s.trim()).filter(Boolean);
  }

  isConfigured(): boolean {
    return this.host.length > 0 && this.username.length > 0;
  }

  async getStatus(): Promise<{
    configured: boolean;
    host: string | null;
    port: number | null;
    ssh_ok: boolean;
    host_name: string | null;
    server_time: string | null;
    uptime: string | null;
  }> {
    if (!this.isConfigured()) {
      return {
        configured: false,
        host: null,
        port: null,
        ssh_ok: false,
        host_name: null,
        server_time: null,
        uptime: null
      };
    }

    try {
      const hostName = (await this.execute("hostname")).stdout.trim() || null;
      const serverTime = (await this.execute("date -Iseconds")).stdout.trim() || null;
      const uptime = (await this.execute("uptime -p")).stdout.trim() || null;

      return {
        configured: true,
        host: this.host,
        port: this.port,
        ssh_ok: true,
        host_name: hostName,
        server_time: serverTime,
        uptime
      };
    } catch {
      return {
        configured: true,
        host: this.host,
        port: this.port,
        ssh_ok: false,
        host_name: null,
        server_time: null,
        uptime: null
      };
    }
  }

  async listContainers(limit = 200): Promise<ServerContainerInfo[]> {
    this.assertConfigured();
    const safeLimit = Math.max(1, Math.min(limit, 1000));

    const result = await this.execute(
      "docker ps --format '{{.Names}}\\t{{.Status}}\\t{{.Image}}'"
    );

    if (result.exitCode !== 0) {
      throw new Error(`docker ps failed: ${result.stderr.trim() || "unknown error"}`);
    }

    const containers = result.stdout
      .split(/\r?\n/)
      .map((line) => line.trim())
      .filter((line) => line.length > 0)
      .map((line) => {
        const [name, status, image] = line.split("\t");
        return {
          name: (name ?? "").trim(),
          status: (status ?? "").trim(),
          image: (image ?? "").trim()
        };
      })
      .filter((row) => row.name.length > 0);

    return containers.slice(0, safeLimit);
  }

  async getContainerLogs(containerName: string, tailLines = 100): Promise<ServerCommandResult> {
    this.assertConfigured();

    const safeContainer = containerName.trim();
    if (!/^[a-zA-Z0-9_.-]+$/.test(safeContainer)) {
      throw new Error("containerName contains invalid characters.");
    }

    const safeTail = Math.max(1, Math.min(tailLines, 1000));
    const command = `docker logs --tail ${safeTail} ${safeContainer} 2>&1`;
    return this.execute(command);
  }

  // ─── Expanded SSH methods (Sprint 2 — v0.2.0) ────────────────────────────

  async getDiskUsage(): Promise<ServerDiskEntry[]> {
    this.assertConfigured();
    const result = await this.execute("df -h --output=source,size,used,avail,pcent,target 2>/dev/null || df -h");
    if (result.exitCode !== 0) {
      throw new Error(`df failed: ${result.stderr.trim() || "unknown error"}`);
    }
    return parseDfOutput(result.stdout);
  }

  async getMemoryUsage(): Promise<ServerMemoryInfo> {
    this.assertConfigured();
    const result = await this.execute("free -m");
    if (result.exitCode !== 0) {
      throw new Error(`free failed: ${result.stderr.trim() || "unknown error"}`);
    }
    return parseFreeOutput(result.stdout);
  }

  async listContainersDetailed(limit = 200): Promise<ServerContainerDetailedInfo[]> {
    this.assertConfigured();
    const safeLimit = Math.max(1, Math.min(limit, 1000));

    const result = await this.execute(
      "docker ps --format '{{.Names}}\\t{{.Status}}\\t{{.Image}}\\t{{.Ports}}\\t{{.ID}}'"
    );

    if (result.exitCode !== 0) {
      throw new Error(`docker ps failed: ${result.stderr.trim() || "unknown error"}`);
    }

    const containers = result.stdout
      .split(/\r?\n/)
      .map((line) => line.trim())
      .filter((line) => line.length > 0)
      .map((line) => {
        const [name, status, image, ports, id] = line.split("\t");
        return {
          name: (name ?? "").trim(),
          status: (status ?? "").trim(),
          image: (image ?? "").trim(),
          ports: (ports ?? "").trim(),
          id: (id ?? "").trim().slice(0, 12)
        };
      })
      .filter((row) => row.name.length > 0);

    return containers.slice(0, safeLimit);
  }

  async inspectContainer(containerName: string): Promise<ServerContainerInspect> {
    this.assertConfigured();
    this.validateContainerName(containerName);

    const result = await this.execute(`docker inspect ${containerName}`);
    if (result.exitCode !== 0) {
      throw new Error(`docker inspect failed: ${result.stderr.trim() || "unknown error"}`);
    }

    const parsed = JSON.parse(result.stdout.trim()) as unknown[];
    if (!Array.isArray(parsed) || parsed.length === 0) {
      throw new Error(`Container not found: ${containerName}`);
    }

    const info = parsed[0] as Record<string, unknown>;
    const state = (info["State"] ?? {}) as Record<string, unknown>;
    const config = (info["Config"] ?? {}) as Record<string, unknown>;
    const hostConfig = (info["HostConfig"] ?? {}) as Record<string, unknown>;
    const networkSettings = (info["NetworkSettings"] ?? {}) as Record<string, unknown>;
    const mounts = (info["Mounts"] ?? []) as Array<Record<string, unknown>>;
    const restartPolicy = (hostConfig["RestartPolicy"] ?? {}) as Record<string, unknown>;
    const env = (config["Env"] ?? []) as unknown[];

    return {
      name: containerName,
      image: String(config["Image"] ?? info["Image"] ?? ""),
      status: String(state["Status"] ?? ""),
      started_at: state["StartedAt"] != null ? String(state["StartedAt"]) : null,
      restart_policy: restartPolicy["Name"] != null ? String(restartPolicy["Name"]) : null,
      env_count: Array.isArray(env) ? env.length : 0,
      mounts: mounts.slice(0, 10).map((m) => ({
        source: String(m["Source"] ?? ""),
        destination: String(m["Destination"] ?? ""),
        mode: String(m["Mode"] ?? "")
      })),
      ports: (networkSettings["Ports"] ?? {}) as Record<string, unknown>
    };
  }

  async restartContainer(containerName: string): Promise<{ ok: boolean; container: string; output: string }> {
    this.assertConfigured();

    if (!this.restartEnabled) {
      throw new Error("Container restart is disabled. Set SERVER_SSH_RESTART_ENABLED=true to enable.");
    }

    this.validateContainerName(containerName);

    if (this.allowedContainers.length > 0 && !this.allowedContainers.includes(containerName)) {
      throw new Error(
        `Container "${containerName}" is not in the restart allowlist. Allowed: ${this.allowedContainers.join(", ")}`
      );
    }

    const result = await this.execute(`docker restart ${containerName}`);
    return {
      ok: result.exitCode === 0,
      container: containerName,
      output: (result.stdout + result.stderr).trim()
    };
  }

  async tailLog(logPath: string, lines = 100): Promise<{ path: string; lines: string[]; line_count: number }> {
    this.assertConfigured();

    const normalizedPath = logPath.trim().replaceAll("\\", "/");
    if (!normalizedPath || normalizedPath.includes("..")) {
      throw new Error("Invalid log path.");
    }

    const allowed = this.allowedLogPaths.some((prefix) =>
      normalizedPath.startsWith(prefix.endsWith("/") ? prefix : `${prefix}/`) || normalizedPath === prefix
    );
    if (!allowed) {
      throw new Error(
        `Log path "${logPath}" is not allowed. Allowed prefixes: ${this.allowedLogPaths.join(", ")}`
      );
    }

    // Sanitize path for shell: only allow /a-z0-9._-
    if (!/^\/[a-zA-Z0-9._\-/]+$/.test(normalizedPath)) {
      throw new Error("Log path contains invalid characters.");
    }

    const safeLines = Math.max(1, Math.min(lines, 1000));
    const result = await this.execute(`tail -n ${safeLines} ${normalizedPath} 2>&1`);
    const outputLines = result.stdout.split(/\r?\n/);
    return {
      path: normalizedPath,
      lines: outputLines,
      line_count: outputLines.length
    };
  }

  async getServiceStatus(serviceName: string): Promise<{ service: string; output: string; active: boolean }> {
    this.assertConfigured();

    const safe = serviceName.trim();
    if (!/^[a-zA-Z0-9_.-]+$/.test(safe)) {
      throw new Error("serviceName contains invalid characters.");
    }

    if (!this.allowedServices.includes(safe)) {
      throw new Error(
        `Service "${safe}" is not in the allowed list. Allowed: ${this.allowedServices.join(", ")}`
      );
    }

    const result = await this.execute(`systemctl status ${safe} 2>&1 || true`);
    const output = result.stdout.trim();
    const active = output.includes("active (running)");
    return { service: safe, output, active };
  }

  async getHealthCheck(): Promise<ServerHealthCheckResult> {
    this.assertConfigured();

    try {
      const [hostnameResult, uptimeResult, dfResult, freeResult, psResult] = await Promise.all([
        this.execute("hostname").catch(() => ({ stdout: "", stderr: "", exitCode: 1, signal: null })),
        this.execute("uptime -p").catch(() => ({ stdout: "", stderr: "", exitCode: 1, signal: null })),
        this.execute("df -h --output=source,size,used,avail,pcent,target 2>/dev/null || df -h").catch(() => ({
          stdout: "",
          stderr: "",
          exitCode: 1,
          signal: null
        })),
        this.execute("free -m").catch(() => ({ stdout: "", stderr: "", exitCode: 1, signal: null })),
        this.execute("docker ps --format '{{.Names}}' | wc -l").catch(() => ({
          stdout: "0",
          stderr: "",
          exitCode: 0,
          signal: null
        }))
      ]);

      return {
        ssh_ok: true,
        host_name: hostnameResult.stdout.trim() || null,
        uptime: uptimeResult.stdout.trim() || null,
        disk: dfResult.exitCode === 0 ? parseDfOutput(dfResult.stdout) : [],
        memory: freeResult.exitCode === 0 ? parseFreeOutput(freeResult.stdout) : null,
        container_count: Number.parseInt(psResult.stdout.trim(), 10) || 0
      };
    } catch {
      return {
        ssh_ok: false,
        host_name: null,
        uptime: null,
        disk: [],
        memory: null,
        container_count: 0
      };
    }
  }

  listAllowedPaths(): { containers: string[]; services: string[]; log_paths: string[] } {
    return {
      containers: this.allowedContainers.length > 0 ? this.allowedContainers : ["(all containers allowed)"],
      services: this.allowedServices,
      log_paths: this.allowedLogPaths
    };
  }

  listAllowedFsPaths(): { roots: string[]; write_enabled: boolean; write_paths: string[] } {
    return {
      roots: this.fsAllowedRoots,
      write_enabled: this.fsWriteEnabled,
      write_paths: this.fsWriteAllowedPaths
    };
  }

  async listDirectory(
    dirPath: string,
    maxEntries = 200
  ): Promise<{ path: string; entries: FsEntry[]; total: number }> {
    this.assertConfigured();
    const safe = this.validateFsPath(dirPath, "read");
    const safeMax = Math.max(1, Math.min(maxEntries, 1000));

    // Use find for structured, parseable output; fallback handled by 2>/dev/null
    const cmd =
      `find ${shellQuote(safe)} -maxdepth 1 -mindepth 1 ` +
      `-printf '%f\\t%y\\t%s\\t%TY-%Tm-%TdT%TH:%TM:%.0TS\\n' 2>/dev/null | sort | head -n ${safeMax + 1}`;

    const result = await this.execute(cmd);
    const lines = result.stdout
      .split(/\r?\n/)
      .map(l => l.trim())
      .filter(Boolean);

    const entries: FsEntry[] = lines.slice(0, safeMax).map(line => {
      const [name, typeChar, sizeStr, mtime] = line.split("\t");
      const typeMap: Record<string, FsEntry["type"]> = {
        f: "file",
        d: "dir",
        l: "link"
      };
      return {
        name: name ?? "",
        type: typeMap[typeChar ?? ""] ?? "other",
        size_bytes: sizeStr != null && sizeStr !== "" ? Number.parseInt(sizeStr, 10) || null : null,
        modified_at: mtime?.trim() || null
      };
    });

    return { path: safe, entries, total: entries.length };
  }

  async readFile(filePath: string, maxBytes?: number): Promise<FsReadResult> {
    this.assertConfigured();
    const safe = this.validateFsPath(filePath, "read");
    const limit = Math.min(maxBytes ?? this.fsMaxFileBytes, this.fsMaxFileBytes);

    // Get size and content in one round-trip using shell
    const cmd =
      `SIZE=$(wc -c < ${shellQuote(safe)} 2>/dev/null || echo -1); ` +
      `echo "$SIZE"; ` +
      `head -c ${limit} ${shellQuote(safe)} 2>&1`;

    const result = await this.execute(cmd);
    if (result.exitCode !== 0 && result.stdout.trim() === "") {
      throw new Error(`Cannot read file: ${result.stderr.trim() || "unknown error"}`);
    }

    const newlineIdx = result.stdout.indexOf("\n");
    const sizeLine = newlineIdx >= 0 ? result.stdout.slice(0, newlineIdx).trim() : "";
    const content = newlineIdx >= 0 ? result.stdout.slice(newlineIdx + 1) : result.stdout;

    const totalBytes = Number.parseInt(sizeLine, 10);
    const validSize = !Number.isNaN(totalBytes) && totalBytes >= 0 ? totalBytes : content.length;

    return {
      path: safe,
      content,
      total_bytes: validSize,
      truncated: validSize > limit
    };
  }

  async searchText(
    pattern: string,
    searchPath: string,
    options?: { maxMatches?: number; caseInsensitive?: boolean; include?: string }
  ): Promise<FsSearchResult> {
    this.assertConfigured();
    const safe = this.validateFsPath(searchPath, "read");
    const safePattern = this.validateGrepPattern(pattern);
    const maxMatches = Math.max(1, Math.min(options?.maxMatches ?? 100, 500));

    const flags = ["-rn", `--max-count=5`];
    if (options?.caseInsensitive) {
      flags.push("-i");
    }
    if (options?.include) {
      const safeInclude = options.include.replace(/[^a-zA-Z0-9.*_\-]/g, "");
      if (safeInclude) {
        flags.push(`--include=${shellQuote(safeInclude)}`);
      }
    }

    const cmd =
      `grep ${flags.join(" ")} ${shellQuote(safePattern)} ${shellQuote(safe)} 2>/dev/null | head -n ${maxMatches}`;

    const result = await this.execute(cmd);
    // exit code 1 = no matches (not an error), exit code 2 = real error
    if (result.exitCode === 2) {
      throw new Error(`grep error: ${result.stderr.trim()}`);
    }

    const lines = result.stdout
      .split(/\r?\n/)
      .map(l => l.trim())
      .filter(Boolean);

    const matches: FsSearchMatch[] = lines.map(line => {
      // Format: /path/to/file:linenum:text
      const firstColon = line.indexOf(":");
      const secondColon = firstColon >= 0 ? line.indexOf(":", firstColon + 1) : -1;
      if (firstColon >= 0 && secondColon >= 0) {
        const file = line.slice(0, firstColon);
        const lineNum = Number.parseInt(line.slice(firstColon + 1, secondColon), 10);
        const text = line.slice(secondColon + 1);
        return {
          file,
          line: Number.isNaN(lineNum) ? 0 : lineNum,
          text
        };
      }
      return { file: "", line: 0, text: line };
    });

    return {
      path: safe,
      pattern,
      matches: matches.slice(0, maxMatches),
      truncated: lines.length >= maxMatches
    };
  }

  async writeFile(filePath: string, content: string): Promise<FsWriteResult> {
    this.assertConfigured();
    const safe = this.validateFsPath(filePath, "write");

    // Encode content as base64 and decode on the remote side to avoid shell injection
    const b64 = Buffer.from(content, "utf8").toString("base64");
    const cmd = `python3 -c "import base64,sys; open(sys.argv[1],'wb').write(base64.b64decode(sys.argv[2]))" ${shellQuote(safe)} ${shellQuote(b64)}`;

    const result = await this.execute(cmd);
    if (result.exitCode !== 0) {
      throw new Error(`Write failed: ${result.stderr.trim() || result.stdout.trim() || "unknown error"}`);
    }

    return { ok: true, path: safe };
  }

  private assertConfigured(): void {
    if (!this.isConfigured()) {
      throw new Error("SERVER_SSH_HOST and SERVER_SSH_USERNAME are required.");
    }
  }

  private validateContainerName(name: string): void {
    const safe = name.trim();
    if (!/^[a-zA-Z0-9_.-]+$/.test(safe)) {
      throw new Error("containerName contains invalid characters.");
    }
  }

  private validateFsPath(rawPath: string, operation: "read" | "write"): string {
    const normalized = rawPath.trim().replace(/\\/g, "/");

    if (!normalized.startsWith("/")) {
      throw new Error(`Path must be absolute: ${rawPath}`);
    }
    if (normalized.includes("..")) {
      throw new Error("Path traversal (..) is not allowed.");
    }
    if (!/^\/[a-zA-Z0-9._\-/]+$/.test(normalized)) {
      throw new Error("Path contains invalid characters. Only alphanumeric, dots, hyphens, underscores, and slashes are allowed.");
    }

    if (this.fsAllowedRoots.length === 0) {
      throw new Error(
        "FS_ALLOWED_ROOTS is not configured. Set it to a comma-separated list of allowed root paths."
      );
    }

    const readAllowed = this.fsAllowedRoots.some(
      root =>
        normalized.startsWith(root.endsWith("/") ? root : `${root}/`) || normalized === root
    );
    if (!readAllowed) {
      throw new Error(
        `Path "${normalized}" is not under any allowed root. Allowed: ${this.fsAllowedRoots.join(", ")}`
      );
    }

    if (operation === "write") {
      if (!this.fsWriteEnabled) {
        throw new Error("Filesystem writes are disabled. Set FS_WRITE_ENABLED=true to enable.");
      }
      if (this.fsWriteAllowedPaths.length > 0) {
        const writeAllowed = this.fsWriteAllowedPaths.some(
          p => normalized.startsWith(p.endsWith("/") ? p : `${p}/`) || normalized === p
        );
        if (!writeAllowed) {
          throw new Error(
            `Write to "${normalized}" is not allowed. Allowed write paths: ${this.fsWriteAllowedPaths.join(", ")}`
          );
        }
      }
    }

    return normalized;
  }

  private validateGrepPattern(pattern: string): string {
    if (!pattern || pattern.trim().length === 0) {
      throw new Error("Search pattern cannot be empty.");
    }
    if (pattern.length > 200) {
      throw new Error("Search pattern exceeds 200 characters.");
    }
    // Allow common regex/search chars; reject shell injection candidates
    if (/[`$!;|&<>\\]/.test(pattern)) {
      throw new Error(
        "Search pattern contains disallowed characters (`$!;|&<>\\). Use only letters, digits, spaces, and common punctuation."
      );
    }
    return pattern.trim();
  }

  private async execute(command: string): Promise<ServerCommandResult> {
    const connection = new Client();
    const privateKey = await this.loadPrivateKey();
    const password = this.password.trim();

    if (!privateKey && !password) {
      throw new Error("Server SSH auth is not configured. Set SERVER_SSH_PASSWORD or SERVER_SSH_PRIVATE_KEY_PATH.");
    }

    return new Promise<ServerCommandResult>((resolve, reject) => {
      let settled = false;
      let timeoutRef: ReturnType<typeof setTimeout> | null = null;

      const cleanup = () => {
        if (timeoutRef) {
          clearTimeout(timeoutRef);
          timeoutRef = null;
        }
        connection.removeAllListeners();
      };

      const safeResolve = (value: ServerCommandResult) => {
        if (settled) {
          return;
        }
        settled = true;
        cleanup();
        resolve(value);
      };

      const safeReject = (error: unknown) => {
        if (settled) {
          return;
        }
        settled = true;
        cleanup();
        reject(error);
      };

      connection.on("ready", () => {
        connection.exec(command, (error, stream) => {
          if (error) {
            safeReject(error);
            connection.end();
            return;
          }

          let stdout = "";
          let stderr = "";
          let exitCode: number | null = null;
          let signal: string | null = null;

          timeoutRef = setTimeout(() => {
            stream.close();
            connection.end();
            safeReject(new Error(`Server command timeout after ${this.commandTimeoutMs}ms.`));
          }, this.commandTimeoutMs);

          stream.on("data", (data: Buffer) => {
            stdout += data.toString("utf8");
          });

          stream.stderr.on("data", (data: Buffer) => {
            stderr += data.toString("utf8");
          });

          stream.on("close", (code: number | null, signalName: string | null) => {
            exitCode = code;
            signal = signalName;
            connection.end();
            safeResolve({
              stdout,
              stderr,
              exitCode,
              signal
            });
          });
        });
      });

      connection.on("error", (error) => {
        safeReject(error);
      });

      connection.connect({
        host: this.host,
        port: this.port,
        username: this.username,
        password: password || undefined,
        privateKey: privateKey || undefined,
        readyTimeout: this.readyTimeoutMs
      });
    });
  }

  private async loadPrivateKey(): Promise<string | null> {
    if (!this.privateKeyPath) {
      return null;
    }

    try {
      return await fs.readFile(this.privateKeyPath, "utf8");
    } catch (error) {
      throw new Error(
        `Failed to read SERVER_SSH_PRIVATE_KEY_PATH (${this.privateKeyPath}): ${
          error instanceof Error ? error.message : String(error)
        }`
      );
    }
  }
}

// ─── Shell helpers ───────────────────────────────────────────────────────────

/** Wraps a string in single quotes for POSIX shell, escaping any embedded single quotes. */
function shellQuote(value: string): string {
  return `'${value.replace(/'/g, "'\\''")}'`;
}

// ─── Parsers ─────────────────────────────────────────────────────────────────

function parseDfOutput(output: string): ServerDiskEntry[] {
  const lines = output.trim().split(/\r?\n/);
  const entries: ServerDiskEntry[] = [];

  for (const line of lines) {
    if (!line.trim() || /^Filesystem/i.test(line) || /^Source/i.test(line)) {
      continue;
    }
    const cols = line.trim().split(/\s+/);
    if (cols.length >= 6) {
      entries.push({
        filesystem: cols[0],
        size: cols[1],
        used: cols[2],
        avail: cols[3],
        use_percent: cols[4],
        mounted_on: cols[5]
      });
    }
  }

  return entries;
}

function parseFreeOutput(output: string): ServerMemoryInfo {
  const lines = output.trim().split(/\r?\n/);
  for (const line of lines) {
    if (/^Mem:/i.test(line)) {
      const cols = line.trim().split(/\s+/);
      const total = Number.parseInt(cols[1], 10) || 0;
      const used = Number.parseInt(cols[2], 10) || 0;
      const free = Number.parseInt(cols[3], 10) || 0;
      const available = cols[6] != null ? Number.parseInt(cols[6], 10) : free;
      return {
        total_mb: total,
        used_mb: used,
        free_mb: free,
        available_mb: available,
        use_percent: total > 0 ? Math.round((used / total) * 100) : 0
      };
    }
  }
  return { total_mb: 0, used_mb: 0, free_mb: 0, available_mb: 0, use_percent: 0 };
}
