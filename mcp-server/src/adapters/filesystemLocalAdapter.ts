import { promises as fs } from "node:fs";
import path from "node:path";

export type FilesystemLocalAdapterConfig = {
  allowedRoots: string[];
  maxFileBytes: number;
  writeEnabled: boolean;
  writeAllowedPaths: string[];
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

export class FilesystemLocalAdapter {
  private readonly allowedRoots: string[];
  private readonly maxFileBytes: number;
  private readonly writeEnabled: boolean;
  private readonly writeAllowedPaths: string[];

  constructor(config: FilesystemLocalAdapterConfig) {
    this.allowedRoots = config.allowedRoots
      .map(root => normalizeAbsolutePath(root))
      .filter(Boolean);
    this.maxFileBytes = config.maxFileBytes > 0 ? config.maxFileBytes : 262144;
    this.writeEnabled = config.writeEnabled;
    this.writeAllowedPaths = config.writeAllowedPaths
      .map(root => normalizeAbsolutePath(root))
      .filter(Boolean);
  }

  listAllowedPaths(): { roots: string[]; write_enabled: boolean; write_paths: string[] } {
    return {
      roots: this.allowedRoots,
      write_enabled: this.writeEnabled,
      write_paths: this.writeAllowedPaths
    };
  }

  async listDirectory(
    dirPath: string,
    maxEntries = 200
  ): Promise<{ path: string; entries: FsEntry[]; total: number }> {
    const safeDir = this.validatePath(dirPath, "read");
    const stat = await fs.stat(safeDir);
    if (!stat.isDirectory()) {
      throw new Error(`Path is not a directory: ${safeDir}`);
    }

    const safeMax = Math.max(1, Math.min(maxEntries, 1000));
    const dirents = await fs.readdir(safeDir, { withFileTypes: true });
    const selected = dirents.sort((a, b) => a.name.localeCompare(b.name)).slice(0, safeMax);

    const entries = await Promise.all(
      selected.map(async dirent => {
        const fullPath = path.join(safeDir, dirent.name);
        const info = await fs.lstat(fullPath);
        return {
          name: dirent.name,
          type: dirent.isFile()
            ? "file"
            : dirent.isDirectory()
              ? "dir"
              : dirent.isSymbolicLink()
                ? "link"
                : "other",
          size_bytes: info.size,
          modified_at: info.mtime.toISOString()
        } satisfies FsEntry;
      })
    );

    return { path: safeDir, entries, total: entries.length };
  }

  async readFile(filePath: string, maxBytes?: number): Promise<FsReadResult> {
    const safeFile = this.validatePath(filePath, "read");
    const stat = await fs.stat(safeFile);
    if (!stat.isFile()) {
      throw new Error(`Path is not a regular file: ${safeFile}`);
    }

    const limit = Math.max(1, Math.min(maxBytes ?? this.maxFileBytes, this.maxFileBytes));
    const bytesToRead = Math.min(stat.size, limit);
    const handle = await fs.open(safeFile, "r");
    let buffer = Buffer.alloc(0);

    try {
      if (bytesToRead > 0) {
        buffer = Buffer.alloc(bytesToRead);
        const { bytesRead } = await handle.read(buffer, 0, bytesToRead, 0);
        if (bytesRead < bytesToRead) {
          buffer = buffer.subarray(0, bytesRead);
        }
      }
    } finally {
      await handle.close();
    }

    return {
      path: safeFile,
      content: buffer.toString("utf8"),
      total_bytes: stat.size,
      truncated: stat.size > limit
    };
  }

  async searchText(
    pattern: string,
    searchPath: string,
    options?: { maxMatches?: number; caseInsensitive?: boolean; include?: string }
  ): Promise<FsSearchResult> {
    const safePath = this.validatePath(searchPath, "read");
    const needle = this.validatePattern(pattern);
    const maxMatches = Math.max(1, Math.min(options?.maxMatches ?? 100, 500));
    const caseInsensitive = options?.caseInsensitive ?? false;
    const includePattern = options?.include ? this.compileIncludePattern(options.include) : null;

    const matches: FsSearchMatch[] = [];
    const files = await this.collectFiles(safePath);

    for (const filePath of files) {
      if (matches.length >= maxMatches) {
        break;
      }

      const relativePath = normalizeToPosix(path.relative(safePath, filePath));
      const includeValue = relativePath.length > 0 && relativePath !== "." ? relativePath : path.basename(filePath);
      if (includePattern && !includePattern.test(includeValue) && !includePattern.test(path.basename(filePath))) {
        continue;
      }

      const stat = await fs.stat(filePath);
      if (!stat.isFile() || stat.size === 0) {
        continue;
      }

      const bytesToRead = Math.min(stat.size, this.maxFileBytes);
      const handle = await fs.open(filePath, "r");
      let buffer = Buffer.alloc(0);

      try {
        if (bytesToRead > 0) {
          buffer = Buffer.alloc(bytesToRead);
          const { bytesRead } = await handle.read(buffer, 0, bytesToRead, 0);
          if (bytesRead < bytesToRead) {
            buffer = buffer.subarray(0, bytesRead);
          }
        }
      } finally {
        await handle.close();
      }

      if (isProbablyBinary(buffer)) {
        continue;
      }

      const text = buffer.toString("utf8");
      const lines = text.split(/\r?\n/);
      const preparedNeedle = caseInsensitive ? needle.toLowerCase() : needle;

      for (let lineIndex = 0; lineIndex < lines.length; lineIndex += 1) {
        if (matches.length >= maxMatches) {
          break;
        }
        const line = lines[lineIndex] ?? "";
        const haystack = caseInsensitive ? line.toLowerCase() : line;
        if (!haystack.includes(preparedNeedle)) {
          continue;
        }

        matches.push({
          file: normalizeToPosix(filePath),
          line: lineIndex + 1,
          text: truncateLine(line)
        });
      }
    }

    return {
      path: safePath,
      pattern: needle,
      matches,
      truncated: matches.length >= maxMatches
    };
  }

  async writeFile(filePath: string, content: string): Promise<FsWriteResult> {
    const safeFile = this.validatePath(filePath, "write");
    await fs.mkdir(path.dirname(safeFile), { recursive: true });
    await fs.writeFile(safeFile, content, "utf8");
    return { ok: true, path: safeFile };
  }

  private validatePath(rawPath: string, operation: "read" | "write"): string {
    const input = rawPath.trim();
    if (!path.isAbsolute(input)) {
      throw new Error(`Path must be absolute: ${rawPath}`);
    }
    if (input.includes("\u0000")) {
      throw new Error("Path contains invalid null bytes.");
    }

    const resolvedPath = normalizeAbsolutePath(input);
    if (!resolvedPath) {
      throw new Error(`Invalid path: ${rawPath}`);
    }

    if (this.allowedRoots.length === 0) {
      throw new Error(
        "FS_ALLOWED_ROOTS is not configured. Set it to a comma-separated list of allowed absolute paths."
      );
    }

    const readAllowed = this.allowedRoots.some(root => isWithinRoot(resolvedPath, root));
    if (!readAllowed) {
      throw new Error(
        `Path "${resolvedPath}" is not under any allowed root. Allowed: ${this.allowedRoots.join(", ")}`
      );
    }

    if (operation === "write") {
      if (!this.writeEnabled) {
        throw new Error("Filesystem writes are disabled. Set FS_WRITE_ENABLED=true to enable.");
      }
      if (this.writeAllowedPaths.length > 0) {
        const writeAllowed = this.writeAllowedPaths.some(root => isWithinRoot(resolvedPath, root));
        if (!writeAllowed) {
          throw new Error(
            `Write to "${resolvedPath}" is not allowed. Allowed write paths: ${this.writeAllowedPaths.join(", ")}`
          );
        }
      }
    }

    return resolvedPath;
  }

  private validatePattern(pattern: string): string {
    const normalized = pattern.trim();
    if (!normalized) {
      throw new Error("Search pattern cannot be empty.");
    }
    if (normalized.length > 200) {
      throw new Error("Search pattern exceeds 200 characters.");
    }
    if (/[\u0000-\u001f]/.test(normalized)) {
      throw new Error("Search pattern contains control characters.");
    }
    return normalized;
  }

  private compileIncludePattern(globPattern: string): RegExp {
    const normalized = globPattern.trim();
    if (!normalized) {
      throw new Error("include cannot be empty when provided.");
    }
    if (!/^[a-zA-Z0-9*?._\-/]+$/.test(normalized)) {
      throw new Error(
        "include contains invalid characters. Use only letters, digits, slash, dot, hyphen, underscore, * and ?."
      );
    }

    const regexSource = normalized
      .split("")
      .map(char => {
        if (char === "*") {
          return ".*";
        }
        if (char === "?") {
          return ".";
        }
        return escapeRegex(char);
      })
      .join("");

    return new RegExp(`^${regexSource}$`);
  }

  private async collectFiles(startPath: string): Promise<string[]> {
    const stat = await fs.stat(startPath);
    if (stat.isFile()) {
      return [startPath];
    }
    if (!stat.isDirectory()) {
      return [];
    }

    const files: string[] = [];
    const queue: string[] = [startPath];

    while (queue.length > 0) {
      const current = queue.shift();
      if (!current) {
        continue;
      }

      const entries = await fs.readdir(current, { withFileTypes: true });
      entries.sort((a, b) => a.name.localeCompare(b.name));

      for (const entry of entries) {
        if (entry.isSymbolicLink()) {
          continue;
        }
        const fullPath = path.join(current, entry.name);
        if (entry.isDirectory()) {
          queue.push(fullPath);
          continue;
        }
        if (entry.isFile()) {
          files.push(fullPath);
        }
      }
    }

    return files;
  }
}

function normalizeAbsolutePath(input: string): string {
  const trimmed = input.trim();
  if (!trimmed) {
    return "";
  }
  const resolved = path.resolve(trimmed);
  return normalizeToPosix(resolved);
}

function normalizeToPosix(input: string): string {
  return input.replace(/\\/g, "/");
}

function isWithinRoot(targetPath: string, rootPath: string): boolean {
  if (targetPath === rootPath) {
    return true;
  }
  const rootWithSep = rootPath.endsWith("/") ? rootPath : `${rootPath}/`;
  return targetPath.startsWith(rootWithSep);
}

function truncateLine(value: string): string {
  return value.length > 500 ? `${value.slice(0, 500)}...[truncated]` : value;
}

function isProbablyBinary(buffer: Buffer): boolean {
  const sampleSize = Math.min(buffer.length, 1024);
  for (let index = 0; index < sampleSize; index += 1) {
    if (buffer[index] === 0) {
      return true;
    }
  }
  return false;
}

function escapeRegex(char: string): string {
  return char.replace(/[.*+?^${}()|[\]\\]/g, "\\$&");
}
