# Vee MCP Server

MCP server for Vee inside `mmcriativos`, focused on operational control over n8n, Obsidian and infrastructure server.

## Current scope

- HTTP MCP endpoint at `POST /mcp` (stateless Streamable HTTP transport)
- Health endpoint at `GET /health`
- Enabled tools:
  - `vee.health`
  - `vee.list_capabilities`
  - `vee.n8n.list_workflows` (REST API)
  - `vee.n8n.get_workflow` (REST API)
  - `vee.n8n.preview_workflow_diff` (REST API + local diff)
  - `vee.n8n.list_recent_executions` (REST API)
  - `vee.n8n.get_execution` (REST API)
  - `vee.n8n.retry_execution` (REST API)
  - `vee.n8n.stop_execution` (REST API)
  - `vee.n8n.update_workflow` (REST API, guarded write)
  - `vee.n8n.patch_workflow_nodes` (REST API, guarded write)
  - `vee.n8n.rollback_workflow` (REST API, guarded write)
  - `vee.obsidian.health` (vault read-only)
  - `vee.obsidian.search` (vault read-only)
  - `vee.obsidian.read_note` (vault read-only)
  - `vee.server.status` (SSH read-only)
  - `vee.server.list_containers` (SSH read-only)
  - `vee.server.get_container_logs` (SSH read-only)
  - `vee.claude.connection_info` (MCP endpoint metadata)

## Planned in next iterations

- `vee.mmcc.list_tenants`

## Local run

1. Copy `.env.example` to `.env` and fill optional `MCP_AUTH_TOKEN`
2. Install dependencies:

```bash
npm install
```

3. Start in dev mode:

```bash
npm run dev
```

4. Build and run:

```bash
npm run build
npm run start
```

## Auth behavior

- If `MCP_AUTH_TOKEN` is empty, `/mcp` is open.
- If `MCP_AUTH_TOKEN` is set, `/mcp` requires:

```text
Authorization: Bearer <MCP_AUTH_TOKEN>
```

## N8N behavior

- `vee.n8n.*` tools use n8n REST API (not n8n MCP workflow exposure).
- `N8N_API_KEY` is required for these tools.
- This allows operation over all workflows, including sub-workflows.
- `vee.n8n.update_workflow`, `vee.n8n.patch_workflow_nodes` and `vee.n8n.rollback_workflow` follow persistent approval flow:
  - call without `approvalId` to create pending approval
  - approve in Laravel endpoint/dashboard
  - call again with `approvalId` to execute
- write execution still requires `N8N_WRITE_ENABLED=true` (workflow writes + execution control)
- `vee.n8n.stop_execution` tries `POST /executions/{id}/stop`; if unavailable in your n8n version, set `allowDeleteFallback=true` to attempt `DELETE /executions/{id}`

## Obsidian behavior

- Set `OBSIDIAN_ROOT_PATH` to your vault root (example: `D:\MM Criativos\MM-Brain`).
- Tools are read-only and only allow `.md` files.
- `vee.obsidian.search` scans markdown files and returns path/line/snippet matches.
- `vee.obsidian.read_note` reads one note with max size controls.

## Server behavior (SSH)

- Configure `SERVER_SSH_HOST`, `SERVER_SSH_USERNAME`, and either:
  - `SERVER_SSH_PASSWORD`, or
  - `SERVER_SSH_PRIVATE_KEY_PATH`
- Tools are read-only:
  - `vee.server.status`
  - `vee.server.list_containers`
  - `vee.server.get_container_logs`

## Filesystem behavior (local allowlist profiles)

- Filesystem tools (`vee.fs.*`) use local paths and require at least one configured read root.
- `FS_ALLOWLIST_ENV` selects the active profile: `production`, `development`, or `documentation`.
- Effective read roots are merged from:
  - `FS_ALLOWED_ROOTS` (legacy/base)
  - `FS_ALLOWED_ROOTS_COMMON`
  - `FS_ALLOWED_ROOTS_<PROFILE>`
  - `FS_WORKSPACE_ROOTS` (full workspace roots)
  - `FS_DEV_REPO_ROOTS` (allowed development repositories)
  - `FS_CONFIG_READ_PATHS` (configs/scripts/docs/deploy paths)
- Effective write paths are merged from:
  - `FS_WRITE_ALLOWED_PATHS` (legacy/base)
  - `FS_WRITE_ALLOWED_PATHS_COMMON`
  - `FS_WRITE_ALLOWED_PATHS_<PROFILE>`
  - `FS_OPERATION_WRITE_PATHS`
  - `FS_DOCS_WRITE_PATHS`
- `FS_WRITE_ENABLED=true` is still required to enable `vee.fs.write_file`.
- Use `vee.fs.list_allowed_paths` to validate active roots, write paths, and selected allowlist environment.
- `vee.fs.search_text` can search the full authorized workspace by using one of the allowed root paths as `path`.

## Database behavior (allowlist + structured query)

- `vee.db.query` is generic and works for any allowlisted table/view without creating a new tool.
- `vee.db.list_allowlist` returns the effective runtime allowlist (tables, named views, blocked columns, write mode, and source).
- Schema and relationship introspection tools:
  - `vee.db.inspect_schema_object`
  - `vee.db.list_table_indexes`
  - `vee.db.list_table_relations`
  - `vee.db.list_allowlist_schema`
  - `vee.db.discover_relationships`
  - `vee.db.test_join_assisted`
  - `vee.db.validate_named_views`
  - `vee.db.validate_table_behavior`
- Coverage automation tools:
  - `vee.db.schedule_coverage_scan`
  - `vee.db.run_coverage_batch`
  - `vee.db.get_coverage_status`
  - `vee.db.generate_coverage_report`
- Operational documentation tools:
  - `vee.db.save_coverage_report_markdown`
  - `vee.db.update_allowlist_inventory_doc`
  - `vee.db.append_schema_observation`
- Baseline write-mode intent:
  - `safe_execute` (MM Criativos core): `projects`, `project_tasks`, `vee_project_events`, `vee_status_history`, `vee_blocks`, `vee_operational_decisions`, `vee_execution_notes`
  - `approval_required` (sensitive core): `clients`, `contacts`, `tenants`, `users`, `messages`, auth/credential tables, billing tables, sensitive integrations
  - MMCC tables should be classified through env lists (`DB_SAFE_EXECUTE_TABLES_MMCC`, `DB_APPROVAL_REQUIRED_TABLES_MMCC`)
- `vee.db.query` supports:
  - `where` filters
  - `joins` between allowlisted tables/views
  - `order_by`
  - `limit` + `offset` pagination
- Security rules:
  - table/view must be allowlisted
  - blocked columns are always denied (SELECT, WHERE, ORDER BY, JOIN ON, writes)
  - if a table has blocked columns and no explicit `columns` is provided, query is rejected
  - with joins and implicit columns, only base-table columns are selected by default
- Query runner output includes consistency/performance metadata:
  - `performance.execution_ms`
  - `pagination` (limit, offset, returned_rows, has_more_possible, next_offset)
  - `result_schema` and normalized row payload
- Runtime allowlist expansion without code changes:
  - `DB_ALLOWLIST_EXTRA_TABLE_POLICIES_JSON`
  - `DB_ALLOWLIST_EXTRA_NAMED_VIEWS_JSON`
- Runtime write-mode grouping (target/category driven):
  - `DB_SAFE_EXECUTE_TABLES_MMCRIATIVOS`
  - `DB_SAFE_EXECUTE_TABLES_MMCC`
  - `DB_APPROVAL_REQUIRED_TABLES_MMCRIATIVOS`
  - `DB_APPROVAL_REQUIRED_TABLES_MMCC`
  - optional global fallbacks: `DB_SAFE_EXECUTE_TABLES`, `DB_APPROVAL_REQUIRED_TABLES`, `DB_READ_ONLY_TABLES`
- `vee.db.list_allowlist` also returns grouped write policies:
  - `safe_execute`
  - `approval_required`
  - `read_only`
  - `mm_criativos_baseline`
  - `mmcc_configured`
- Generic write behavior (`vee.db.write`):
  - supports `INSERT`, `UPDATE`, `UPSERT` only
  - rejects destructive operations by design (`DELETE`, `DROP`, `TRUNCATE` unsupported)
  - accepts payload aliases: `data`, `values`, `set`, `payload.data`, `payload.values`, `payload.set`
  - requires reason in format `context: detailed justification`
  - valid `reason` examples:
    - `phase1_validation: validating insert flow in vee_execution_notes`
    - `ops_fix: normalize stale timeline context fields`
    - `audit_sync: update trace correlation metadata for incident session`
  - captures before/after snapshots when possible (UPDATE and UPSERT with identity keys; INSERT by `id` or identity fallback)
  - supports required/auto fields via env:
    - `DB_WRITE_REQUIRED_FIELDS_JSON`
    - `DB_WRITE_AUTO_FILL_FIELDS_JSON`
- Audit/cobertura throughput:
  - Default read rate limit: `DB_READONLY_RATE_LIMIT`
  - Coverage/audit read rate limit override: `DB_READONLY_AUDIT_RATE_LIMIT` (or fallback `DB_COVERAGE_RATE_LIMIT`)

## Claude connector behavior

- `vee.claude.connection_info` returns endpoint/auth requirements for Claude setup.
- Set `CLAUDE_MCP_PUBLIC_URL` to force a public endpoint in responses.
- If empty, the tool falls back to local inferred endpoint (`http://localhost:<PORT>/mcp`).

## Audit behavior

- When `VEE_AUDIT_URL` is configured, every tool call is sent to Laravel audit endpoint.
- Use `VEE_AUDIT_TOKEN` to authenticate with `X-VEE-Internal-Token`.
- Payload includes `call_id`, tool name, status, duration, compact request/response, and error message.

## Approval behavior

- Set `VEE_CONTROL_BASE_URL` to Laravel internal MCP base (example: `http://mmcriativos.test/api/internal/vee/mcp`).
- Set `VEE_CONTROL_TOKEN` (same token used by Laravel `vee.internal` middleware).
- Approval endpoints used by Vee MCP:
  - `POST /approvals` (create pending)
  - `GET /approvals/{approvalId}` (check status/payload)
  - `POST /approvals/{approvalId}/executed` (mark execution complete)

## Phase 1 validation

- Validation checklist and final runtime snapshot (allowlist + roots): `docs/phase1-validation.md`
- Raw execution evidence file: `tmp/phase1-validation-results.json`
- Real environment validation (production endpoint + runtime FS correction): `docs/phase1-real-validation.md`
- Real environment evidence file: `tmp/phase1-real-validation-results.json`
