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
