/**
 * Orch routes
 *   GET  /orch/status  — estado dos grupos + workflow n8n + últimas execuções salvas
 *   POST /orch/runs    — persiste resultado de uma execução (chamado pelo blade)
 */
import type { Request, Response } from 'express';
import { pool } from '../db.js';
import { N8N_BASE_URL, N8N_API_KEY, TEST_RUNNER_WORKFLOW_ID } from '../config.js';

// ── Definição canônica dos grupos (espelho de platform-health.js) ─────────────

export const TEST_GROUPS = [
  { id:'A', name:'Onboarding',        route:'atendimento', tenant_slug:'veetest',   daily:3,  total:4,  bugs:[] },
  { id:'B', name:'Intent',            route:'atendimento', tenant_slug:'veetest',   daily:5,  total:5,  bugs:[] },
  { id:'C', name:'Fluxo Completo',    route:'atendimento', tenant_slug:'veetest',   daily:3,  total:4,  bugs:[] },
  { id:'D', name:'Entity Resolver',   route:'agendamento', tenant_slug:'veetest',   daily:6,  total:7,  bugs:['BUG-001'] },
  { id:'E', name:'Missing Fields',    route:'agendamento', tenant_slug:'veetest',   daily:5,  total:5,  bugs:['BUG-001'] },
  { id:'F', name:'PreCheck/Blocking', route:'agendamento', tenant_slug:'veetest-c', daily:0,  total:3,  bugs:[], skip:true, skipReason:'veetest-c nao criado' },
  { id:'G', name:'Criacao Agend.',    route:'agendamento', tenant_slug:'veetest',   daily:2,  total:4,  bugs:['BUG-001'] },
  { id:'H', name:'Reagendamento',     route:'agendamento', tenant_slug:'veetest',   daily:0,  total:3,  bugs:['BUG-001'] },
  { id:'I', name:'Cancelamento',      route:'agendamento', tenant_slug:'veetest',   daily:0,  total:2,  bugs:['BUG-001'] },
  { id:'J', name:'Desambiguacao',     route:'agendamento', tenant_slug:'veetest-c', daily:0,  total:2,  bugs:[], skip:true, skipReason:'veetest-c nao criado' },
  { id:'K', name:'Save Process',      route:'agendamento', tenant_slug:'veetest',   daily:2,  total:2,  bugs:['BUG-002'] },
  { id:'L', name:'Hub/Roteamento',    route:'atendimento', tenant_slug:'veetest',   daily:2,  total:2,  bugs:[] },
  { id:'M', name:'mmbeauty',          route:'agendamento', tenant_slug:'mmbeauty',  daily:6,  total:12, bugs:['BUG-001'] },
] as const;

type GroupId = (typeof TEST_GROUPS)[number]['id'];
const VALID_GROUP_IDS = TEST_GROUPS.map((g) => g.id) as string[];

// ── n8n helper ────────────────────────────────────────────────────────────────

async function n8nGet<T>(endpoint: string): Promise<T | null> {
  if (!N8N_BASE_URL || !N8N_API_KEY) return null;
  try {
    const res = await fetch(`${N8N_BASE_URL}${endpoint}`, {
      headers: { 'X-N8N-API-KEY': N8N_API_KEY },
      signal: AbortSignal.timeout(8000),
    });
    if (!res.ok) return null;
    return res.json() as Promise<T>;
  } catch {
    return null;
  }
}

// ── GET /orch/status ──────────────────────────────────────────────────────────

export async function handleOrchStatus(_req: Request, res: Response): Promise<void> {
  // 1. Workflow status no n8n
  const wf = await n8nGet<{ id:string; name:string; active:boolean; updatedAt:string }>(
    `/workflows/${TEST_RUNNER_WORKFLOW_ID}`
  );

  // 2. Execuções recentes
  const execData = await n8nGet<{ data: Array<{ id:string; status:string; startedAt:string; stoppedAt:string }> }>(
    `/executions?workflowId=${TEST_RUNNER_WORKFLOW_ID}&limit=10&includeData=false`
  );
  const recentExecs = execData?.data ?? [];

  // 3. Últimos resultados por grupo (DB local, últimos 7 dias)
  type RunRow = { group_id:string; status:string; executed_at:string; execution_id:string|null };
  let dbRows: RunRow[] = [];
  try {
    const [rows] = await pool.execute<any[]>(`
      SELECT group_id, status, executed_at, execution_id
      FROM vee_test_runs
      WHERE executed_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
      ORDER BY executed_at DESC
      LIMIT 200
    `);
    dbRows = rows as RunRow[];
  } catch { /* tabela pode ainda nao existir */ }

  // Pega o resultado mais recente por grupo
  const lastByGroup: Record<string, RunRow> = {};
  for (const row of dbRows) {
    if (!lastByGroup[row.group_id]) lastByGroup[row.group_id] = row;
  }

  // 4. Montar resposta
  const groups = TEST_GROUPS.map((g) => {
    const last = lastByGroup[g.id];
    const fmt  = last
      ? new Date(last.executed_at).toLocaleTimeString('pt-BR', { hour:'2-digit', minute:'2-digit' })
      : null;
    return {
      ...g,
      status:      last?.status ?? 'idle',
      lastRun:     fmt,
      lastRunFull: last?.executed_at ?? null,
      executionId: last?.execution_id ?? null,
    };
  });

  res.json({
    workflow: {
      id:        wf?.id        ?? TEST_RUNNER_WORKFLOW_ID,
      name:      wf?.name      ?? 'MMCC - Test Runner',
      active:    wf?.active    ?? null,
      updatedAt: wf?.updatedAt ?? null,
    },
    groups,
    recentExecs: recentExecs.map((e) => ({
      id: e.id, status: e.status, startedAt: e.startedAt, stoppedAt: e.stoppedAt,
    })),
    fetchedAt: new Date().toISOString(),
  });
}

// ── POST /orch/runs ───────────────────────────────────────────────────────────

export async function handleSaveOrchRun(req: Request, res: Response): Promise<void> {
  const { group_id, status, execution_id, route, tenant_slug, message, detail } = req.body as {
    group_id: string; status: string;
    execution_id?: string; route?: string; tenant_slug?: string;
    message?: string; detail?: string;
  };

  if (!group_id || !status) {
    res.status(400).json({ error: 'group_id and status are required' }); return;
  }
  if (!VALID_GROUP_IDS.includes(group_id)) {
    res.status(400).json({ error: `invalid group_id: ${group_id}` }); return;
  }
  if (!['pass','fail','error'].includes(status)) {
    res.status(400).json({ error: 'status must be pass, fail, or error' }); return;
  }

  try {
    const [result] = await pool.execute<any>(
      `INSERT INTO vee_test_runs (group_id, status, execution_id, route, tenant_slug, message, detail)
       VALUES (?, ?, ?, ?, ?, ?, ?)`,
      [group_id, status, execution_id ?? null, route ?? null,
       tenant_slug ?? null, message ?? null, detail ?? null]
    );
    res.status(201).json({ id: (result as any).insertId, group_id, status });
  } catch (err) {
    res.status(500).json({ error: err instanceof Error ? err.message : String(err) });
  }
}
