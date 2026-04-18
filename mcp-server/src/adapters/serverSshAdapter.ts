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
