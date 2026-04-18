import { promises as fs } from "node:fs";
import path from "node:path";

export type ObsidianAdapterConfig = {
  rootPath: string;
  maxFileBytes: number;
  writeEnabled: boolean;
  allowedWriteFolders: string[];
};

export type ObsidianNoteTemplate = "project" | "task" | "incident" | "decision" | "analysis" | "generic";

export type ObsidianCreateNoteParams = {
  path: string;
  title: string;
  template?: ObsidianNoteTemplate;
  frontmatter?: Record<string, unknown>;
  content?: string;
};

export type ObsidianAppendToNoteParams = {
  path: string;
  content: string;
  section?: string;
  prepend?: boolean;
};

export type ObsidianUpdateNoteSectionParams = {
  path: string;
  section_name: string;
  content: string;
  create_if_missing?: boolean;
};

export type ObsidianAppendToDailyLogParams = {
  date?: string;
  category: string;
  content: string;
};

export type ObsidianCreateTaskNoteParams = {
  project: string;
  title: string;
  status?: "open" | "in-progress" | "blocked" | "done";
  priority?: "low" | "medium" | "high" | "critical";
  description?: string;
  links?: string[];
  assignee?: string;
};

export type ObsidianSearchMatch = {
  path: string;
  line: number;
  snippet: string;
};

export class ObsidianAdapter {
  private readonly rootPath: string;
  private readonly maxFileBytes: number;
  private readonly writeEnabled: boolean;
  private readonly allowedWriteFolders: string[];

  constructor(config: ObsidianAdapterConfig) {
    this.rootPath = config.rootPath.trim();
    this.maxFileBytes = config.maxFileBytes;
    this.writeEnabled = config.writeEnabled;
    this.allowedWriteFolders = config.allowedWriteFolders.map(f => f.trim().replaceAll("\\", "/")).filter(Boolean);
  }

  isConfigured(): boolean {
    return this.rootPath.length > 0;
  }

  async getHealth(): Promise<{
    configured: boolean;
    root_path: string | null;
    root_exists: boolean;
  }> {
    if (!this.isConfigured()) {
      return {
        configured: false,
        root_path: null,
        root_exists: false
      };
    }

    const rootExists = await this.pathExists(this.rootPath);
    return {
      configured: true,
      root_path: this.rootPath,
      root_exists: rootExists
    };
  }

  async searchNotes(params: {
    query: string;
    limit?: number;
    caseSensitive?: boolean;
  }): Promise<ObsidianSearchMatch[]> {
    this.assertConfigured();

    const query = params.query.trim();
    if (!query) {
      throw new Error("query is required.");
    }

    const caseSensitive = params.caseSensitive ?? false;
    const limit = Math.max(1, Math.min(params.limit ?? 20, 100));
    const normalizedQuery = caseSensitive ? query : query.toLowerCase();
    const files = await this.listMarkdownFiles();
    const matches: ObsidianSearchMatch[] = [];

    for (const filePath of files) {
      if (matches.length >= limit) {
        break;
      }

      const content = await this.readFileSafe(filePath);
      if (!content) {
        continue;
      }

      const lines = content.split(/\r?\n/);
      for (let index = 0; index < lines.length; index += 1) {
        const line = lines[index];
        const normalizedLine = caseSensitive ? line : line.toLowerCase();
        if (!normalizedLine.includes(normalizedQuery)) {
          continue;
        }

        matches.push({
          path: this.toRelativePath(filePath),
          line: index + 1,
          snippet: this.truncate(line.trim(), 240)
        });

        if (matches.length >= limit) {
          break;
        }
      }
    }

    return matches;
  }

  async readNote(relativePath: string, maxChars = 20000): Promise<{
    path: string;
    content: string;
    truncated: boolean;
  }> {
    this.assertConfigured();

    const notePath = this.resolveWithinRoot(relativePath);
    const stat = await fs.stat(notePath);
    if (!stat.isFile()) {
      throw new Error("Target note path is not a file.");
    }

    const content = await fs.readFile(notePath, "utf8");
    const safeMaxChars = Math.max(500, Math.min(maxChars, 200000));
    const truncated = content.length > safeMaxChars;

    return {
      path: this.toRelativePath(notePath),
      content: truncated ? `${content.slice(0, safeMaxChars)}\n...[truncated]` : content,
      truncated
    };
  }

  // ─── Write methods ────────────────────────────────────────────────────────

  async createNote(params: ObsidianCreateNoteParams): Promise<{
    ok: boolean;
    path: string;
    title: string;
    timestamp: string;
    size_bytes: number;
  }> {
    this.assertConfigured();
    this.assertWriteEnabled();

    const notePath = this.resolveWithinRoot(params.path);
    this.validateWritePath(params.path);

    // Refuse to overwrite existing files
    const exists = await this.pathExists(notePath);
    if (exists) {
      throw new Error(`Note already exists: ${params.path}. Use append_to_note or update_note_section to modify it.`);
    }

    const template = params.template ?? "generic";
    const extraFrontmatter = params.frontmatter ?? {};
    const body = params.content ?? "";
    const fileContent = this.buildNoteFromTemplate(template, params.title, extraFrontmatter, body);

    await fs.mkdir(path.dirname(notePath), { recursive: true });
    // wx flag = fail if file exists (extra safety against race conditions)
    const handle = await fs.open(notePath, "wx");
    await handle.writeFile(fileContent, "utf8");
    await handle.close();

    const stat = await fs.stat(notePath);
    const timestamp = new Date().toISOString();

    return {
      ok: true,
      path: this.toRelativePath(notePath),
      title: params.title,
      timestamp,
      size_bytes: stat.size
    };
  }

  async appendToNote(params: ObsidianAppendToNoteParams): Promise<{
    ok: boolean;
    path: string;
    appended_lines: number;
    new_size_bytes: number;
  }> {
    this.assertConfigured();
    this.assertWriteEnabled();

    const notePath = this.resolveWithinRoot(params.path);
    this.validateWritePath(params.path);

    const stat = await fs.stat(notePath);
    if (!stat.isFile()) {
      throw new Error("Target note path is not a file.");
    }

    const existingContent = await fs.readFile(notePath, "utf8");
    const appendContent = params.content.endsWith("\n") ? params.content : `${params.content}\n`;
    const appendedLines = appendContent.split(/\r?\n/).length - 1;
    let newContent: string;

    if (params.section) {
      newContent = this.insertAfterSection(existingContent, params.section, appendContent);
    } else if (params.prepend) {
      newContent = appendContent + existingContent;
    } else {
      const separator = existingContent.endsWith("\n") ? "" : "\n";
      newContent = existingContent + separator + appendContent;
    }

    await fs.writeFile(notePath, newContent, "utf8");
    const newStat = await fs.stat(notePath);

    return {
      ok: true,
      path: this.toRelativePath(notePath),
      appended_lines: appendedLines,
      new_size_bytes: newStat.size
    };
  }

  async updateNoteSection(params: ObsidianUpdateNoteSectionParams): Promise<{
    ok: boolean;
    path: string;
    section_name: string;
    replaced_lines: number;
    now_has_section: boolean;
  }> {
    this.assertConfigured();
    this.assertWriteEnabled();

    const notePath = this.resolveWithinRoot(params.path);
    this.validateWritePath(params.path);

    const stat = await fs.stat(notePath);
    if (!stat.isFile()) {
      throw new Error("Target note path is not a file.");
    }

    const existingContent = await fs.readFile(notePath, "utf8");
    const lines = existingContent.split(/\r?\n/);
    const headingPattern = new RegExp(`^##\\s+${escapeRegex(params.section_name)}\\s*$`, "i");

    let sectionStart = -1;
    for (let i = 0; i < lines.length; i += 1) {
      if (headingPattern.test(lines[i])) {
        sectionStart = i;
        break;
      }
    }

    if (sectionStart === -1) {
      if (!params.create_if_missing) {
        throw new Error(`Section "## ${params.section_name}" not found. Set create_if_missing to add it.`);
      }
      // Append new section at end
      const separator = existingContent.endsWith("\n") ? "" : "\n";
      const sectionContent = `\n## ${params.section_name}\n\n${params.content}\n`;
      await fs.writeFile(notePath, existingContent + separator + sectionContent, "utf8");
      return {
        ok: true,
        path: this.toRelativePath(notePath),
        section_name: params.section_name,
        replaced_lines: 0,
        now_has_section: true
      };
    }

    // Find end of section (next ## heading at same or higher level, or EOF)
    let sectionEnd = lines.length;
    for (let i = sectionStart + 1; i < lines.length; i += 1) {
      if (/^##\s/.test(lines[i])) {
        sectionEnd = i;
        break;
      }
    }

    const replacedLines = sectionEnd - sectionStart - 1;
    const newSectionLines = [`## ${params.section_name}`, "", params.content, ""];
    const newLines = [
      ...lines.slice(0, sectionStart),
      ...newSectionLines,
      ...lines.slice(sectionEnd)
    ];

    await fs.writeFile(notePath, newLines.join("\n"), "utf8");

    return {
      ok: true,
      path: this.toRelativePath(notePath),
      section_name: params.section_name,
      replaced_lines: replacedLines,
      now_has_section: true
    };
  }

  async appendToDailyLog(params: ObsidianAppendToDailyLogParams): Promise<{
    ok: boolean;
    log_path: string;
    entry_timestamp: string;
  }> {
    this.assertConfigured();
    this.assertWriteEnabled();

    const now = new Date();
    const dateStr = params.date ?? now.toISOString().slice(0, 10);
    const timeStr = now.toTimeString().slice(0, 5);
    const logRelativePath = `Sessoes/${dateStr}.md`;
    const logPath = this.resolveWithinRoot(logRelativePath);
    this.validateWritePath(logRelativePath);

    await fs.mkdir(path.dirname(logPath), { recursive: true });

    const exists = await this.pathExists(logPath);
    if (!exists) {
      const header = `# Log — ${dateStr}\n\n`;
      await fs.writeFile(logPath, header, "utf8");
    }

    const entry = `\n### ${timeStr} — [${params.category}]\n${params.content}\n`;
    await fs.appendFile(logPath, entry, "utf8");

    return {
      ok: true,
      log_path: logRelativePath,
      entry_timestamp: now.toISOString()
    };
  }

  async createTaskNote(params: ObsidianCreateTaskNoteParams): Promise<{
    ok: boolean;
    path: string;
    timestamp: string;
  }> {
    this.assertConfigured();
    this.assertWriteEnabled();

    const now = new Date();
    const dateStr = now.toISOString().slice(0, 10);
    const slug = slugify(params.title);
    const relativePath = `Vee/tarefas/${dateStr}-${slug}.md`;
    const notePath = this.resolveWithinRoot(relativePath);
    this.validateWritePath(relativePath);

    const exists = await this.pathExists(notePath);
    if (exists) {
      throw new Error(`Task note already exists: ${relativePath}`);
    }

    const frontmatter: Record<string, unknown> = {
      title: params.title,
      type: "task",
      project: params.project,
      status: params.status ?? "open",
      priority: params.priority ?? "medium",
      assignee: params.assignee ?? "team",
      created_at: dateStr,
      created_by: "vee"
    };

    if (params.links && params.links.length > 0) {
      frontmatter["links"] = params.links;
    }

    const body = [
      "## Descrição",
      "",
      params.description ?? "",
      "",
      "## Contexto",
      "",
      "",
      "## Critério de conclusão",
      "",
      ""
    ].join("\n");

    const fileContent = this.buildFrontmatterBlock(frontmatter) + `\n# ${params.title}\n\n` + body;

    await fs.mkdir(path.dirname(notePath), { recursive: true });
    const handle = await fs.open(notePath, "wx");
    await handle.writeFile(fileContent, "utf8");
    await handle.close();

    return {
      ok: true,
      path: this.toRelativePath(notePath),
      timestamp: now.toISOString()
    };
  }

  private assertConfigured(): void {
    if (!this.isConfigured()) {
      throw new Error("OBSIDIAN_ROOT_PATH is not configured.");
    }
  }

  private assertWriteEnabled(): void {
    if (!this.writeEnabled) {
      throw new Error("Obsidian write is disabled. Set OBSIDIAN_WRITE_ENABLED=true to enable.");
    }
  }

  private validateWritePath(relativePath: string): void {
    if (this.allowedWriteFolders.length === 0) {
      return; // Empty = full vault allowed
    }
    const normalized = relativePath.trim().replaceAll("\\", "/");
    const allowed = this.allowedWriteFolders.some(folder => {
      const prefix = folder.endsWith("/") ? folder : `${folder}/`;
      return normalized.startsWith(prefix);
    });
    if (!allowed) {
      throw new Error(
        `Write access denied for path "${relativePath}". Allowed folders: ${this.allowedWriteFolders.join(", ")}`
      );
    }
  }

  private buildFrontmatterBlock(obj: Record<string, unknown>): string {
    const lines = Object.entries(obj).map(([key, value]) => {
      if (Array.isArray(value)) {
        const items = value.map(v => `  - ${String(v)}`).join("\n");
        return `${key}:\n${items}`;
      }
      if (typeof value === "string") {
        return `${key}: "${value.replaceAll('"', '\\"')}"`;
      }
      return `${key}: ${String(value)}`;
    });
    return `---\n${lines.join("\n")}\n---\n`;
  }

  private buildNoteFromTemplate(
    template: ObsidianNoteTemplate,
    title: string,
    extraFrontmatter: Record<string, unknown>,
    body: string
  ): string {
    const dateStr = new Date().toISOString().slice(0, 10);
    const baseFrontmatter: Record<string, unknown> = {
      title,
      type: template,
      created_at: dateStr,
      created_by: "vee",
      ...extraFrontmatter
    };

    const templateSections: Record<ObsidianNoteTemplate, string[]> = {
      generic: [],
      project: ["## Objetivo", "", "", "## Status", "", "", "## Notas", "", ""],
      task: ["## Descrição", "", "", "## Contexto", "", "", "## Critério de conclusão", "", ""],
      incident: ["## O que aconteceu", "", "", "## Impacto", "", "", "## Passos de resolução", "", ""],
      decision: ["## Contexto", "", "", "## Decisão", "", "", "## Consequências", "", ""],
      analysis: ["## Objetivo", "", "", "## Descobertas", "", "", "## Conclusão", "", ""]
    };

    // Template-specific default frontmatter
    if (template === "task" && !extraFrontmatter["status"]) baseFrontmatter["status"] = "open";
    if (template === "task" && !extraFrontmatter["priority"]) baseFrontmatter["priority"] = "medium";
    if (template === "incident" && !extraFrontmatter["severity"]) baseFrontmatter["severity"] = "medium";
    if (template === "incident" && !extraFrontmatter["status"]) baseFrontmatter["status"] = "open";
    if (template === "decision" && !extraFrontmatter["status"]) baseFrontmatter["status"] = "proposed";
    if (template === "project" && !extraFrontmatter["status"]) baseFrontmatter["status"] = "draft";

    const sections = templateSections[template];
    const templateBody = sections.length > 0 ? sections.join("\n") + "\n" : "";
    const finalBody = body ? `${body}\n\n${templateBody}`.trimEnd() + "\n" : templateBody;

    return `${this.buildFrontmatterBlock(baseFrontmatter)}\n# ${title}\n\n${finalBody}`;
  }

  private insertAfterSection(content: string, sectionName: string, appendContent: string): string {
    const lines = content.split(/\r?\n/);
    const pattern = new RegExp(`^##\\s+${escapeRegex(sectionName)}\\s*$`, "i");

    let sectionStart = -1;
    for (let i = 0; i < lines.length; i += 1) {
      if (pattern.test(lines[i])) {
        sectionStart = i;
        break;
      }
    }

    if (sectionStart === -1) {
      throw new Error(`Section "## ${sectionName}" not found in note.`);
    }

    // Find end of section
    let insertAt = lines.length;
    for (let i = sectionStart + 1; i < lines.length; i += 1) {
      if (/^##\s/.test(lines[i])) {
        insertAt = i;
        break;
      }
    }

    const appendLines = appendContent.split(/\r?\n/);
    const result = [
      ...lines.slice(0, insertAt),
      ...appendLines,
      ...lines.slice(insertAt)
    ];

    return result.join("\n");
  }

  private resolveWithinRoot(relativePath: string): string {
    const normalized = relativePath.trim().replaceAll("\\", "/");
    if (!normalized) {
      throw new Error("path is required.");
    }
    if (normalized.includes("..")) {
      throw new Error("path cannot contain '..'.");
    }
    if (!normalized.toLowerCase().endsWith(".md")) {
      throw new Error("Only markdown notes (.md) are allowed.");
    }

    const resolved = path.resolve(this.rootPath, normalized);
    const rootResolved = path.resolve(this.rootPath);
    if (!resolved.startsWith(rootResolved)) {
      throw new Error("path escapes OBSIDIAN_ROOT_PATH.");
    }

    return resolved;
  }

  private async listMarkdownFiles(): Promise<string[]> {
    const rootResolved = path.resolve(this.rootPath);
    const results: string[] = [];
    const queue: string[] = [rootResolved];

    while (queue.length > 0) {
      const current = queue.pop();
      if (!current) {
        continue;
      }

      const entries = await fs.readdir(current, { withFileTypes: true });
      for (const entry of entries) {
        const fullPath = path.join(current, entry.name);

        if (entry.isDirectory()) {
          if (entry.name.startsWith(".")) {
            continue;
          }
          queue.push(fullPath);
          continue;
        }

        if (!entry.isFile()) {
          continue;
        }

        if (!entry.name.toLowerCase().endsWith(".md")) {
          continue;
        }

        results.push(fullPath);
      }
    }

    return results.sort((a, b) => a.localeCompare(b, "pt-BR"));
  }

  private async readFileSafe(filePath: string): Promise<string | null> {
    try {
      const stat = await fs.stat(filePath);
      if (!stat.isFile()) {
        return null;
      }
      if (stat.size > this.maxFileBytes) {
        return null;
      }
      return await fs.readFile(filePath, "utf8");
    } catch {
      return null;
    }
  }

  private toRelativePath(absolutePath: string): string {
    const rootResolved = path.resolve(this.rootPath);
    return path.relative(rootResolved, absolutePath).replaceAll("\\", "/");
  }

  private truncate(value: string, maxChars: number): string {
    if (value.length <= maxChars) {
      return value;
    }
    return `${value.slice(0, maxChars)}...`;
  }

  private async pathExists(targetPath: string): Promise<boolean> {
    try {
      await fs.access(targetPath);
      return true;
    } catch {
      return false;
    }
  }
}

function escapeRegex(str: string): string {
  return str.replaceAll(/[$()*+.?[\\\]^{|}]/g, "\\$&");
}

function slugify(str: string): string {
  return str
    .toLowerCase()
    .normalize("NFD")
    .replaceAll(/[\u0300-\u036f]/g, "")
    .replaceAll(/[^a-z0-9]+/g, "-")
    .replaceAll(/^-+|-+$/g, "")
    .slice(0, 60);
}
