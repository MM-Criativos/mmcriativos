@php
    $title    = 'Guia SDR';
    $subTitle = 'Script de primeiro contato — MM Cloud';
@endphp

@extends('layouts.app')

@push('styles')
<style>
/* ── Layout ────────────────────────────────────────────────────────────── */
.sdr-wrap  { display:flex; min-height:calc(100vh - 130px); border-radius:12px; overflow:hidden; border:1px solid; }
.sdr-wrap  { border-color:#e5e7eb; background:#fff; }
.dark .sdr-wrap { border-color:#1f2937; background:#0a0a0a; }

/* ── Sidebar esquerda ──────────────────────────────────────────────────── */
.sdr-side  { width:210px; flex-shrink:0; display:flex; flex-direction:column; border-right:1px solid; }
.sdr-side  { border-color:#e5e7eb; background:#f9fafb; }
.dark .sdr-side { border-color:#1f2937; background:#0d0d0d; }

.sdr-side-hd { padding:14px 16px 10px; border-bottom:1px solid; }
.sdr-side-hd { border-color:#e5e7eb; }
.dark .sdr-side-hd { border-color:#1f2937; }

.sdr-section-title { font-size:9.5px; font-weight:700; letter-spacing:.08em; text-transform:uppercase; padding:10px 16px 4px; color:#9ca3af; }

.sdr-btn { display:flex; align-items:center; gap:9px; width:100%; padding:8px 16px; font-size:.8rem; font-weight:500; border-left:3px solid transparent; cursor:pointer; transition:all .15s; color:#374151; }
.dark .sdr-btn { color:#d1d5db; }
.sdr-btn:hover { background:rgba(0,0,0,.04); }
.dark .sdr-btn:hover { background:rgba(255,255,255,.04); }
.sdr-btn.active { border-left-color:#f97316; color:#f97316; font-weight:600; background:rgba(249,115,22,.07); }

.sdr-dot { width:8px; height:8px; border-radius:50%; flex-shrink:0; }
.sdr-count { margin-left:auto; font-size:10px; font-family:monospace; opacity:.5; }

.sdr-side-ft { margin-top:auto; padding:12px; border-top:1px solid; }
.sdr-side-ft { border-color:#e5e7eb; }
.dark .sdr-side-ft { border-color:#1f2937; }

/* ── Área principal ────────────────────────────────────────────────────── */
.sdr-main { flex:1; overflow-y:auto; padding:24px 28px; }

/* ── Badges de etapa (classes fixas, não purgadas) ─────────────────────── */
.badge-sdr { font-size:.68rem; font-weight:700; letter-spacing:.06em; text-transform:uppercase; padding:3px 9px; border-radius:20px; display:inline-flex; align-items:center; }

.badge-Abertura     { background:#d1fae5; color:#065f46; }
.badge-Qualificacao { background:#dbeafe; color:#1e40af; }
.badge-Pitch        { background:#ede9fe; color:#4c1d95; }
.badge-Objecao      { background:#ffedd5; color:#9a3412; }
.badge-Fechamento   { background:#fef9c3; color:#713f12; }
.badge-Encerrado    { background:#f3f4f6; color:#374151; }

.dark .badge-Abertura     { background:rgba(6,95,70,.35);   color:#6ee7b7; }
.dark .badge-Qualificacao { background:rgba(30,64,175,.3);  color:#93c5fd; }
.dark .badge-Pitch        { background:rgba(76,29,149,.3);  color:#c4b5fd; }
.dark .badge-Objecao      { background:rgba(154,52,18,.3);  color:#fdba74; }
.dark .badge-Fechamento   { background:rgba(113,63,18,.3);  color:#fde68a; }
.dark .badge-Encerrado    { background:rgba(55,65,81,.4);   color:#9ca3af; }

/* ── Cards de nó ──────────────────────────────────────────────────────── */
.sdr-card { border:1px solid; border-radius:12px; overflow:hidden; margin-bottom:12px; transition:box-shadow .15s; }
.sdr-card { border-color:#e5e7eb; background:#fff; }
.dark .sdr-card { border-color:#1f2937; background:#0d0d0d; }
.sdr-card:hover { box-shadow:0 3px 12px rgba(0,0,0,.08); }

.sdr-card-hd { display:flex; align-items:center; gap:10px; padding:11px 16px; cursor:pointer; }
.sdr-card-hd { background:#f9fafb; border-bottom:1px solid transparent; }
.dark .sdr-card-hd { background:#111827; }
.sdr-card-hd.open { border-bottom-color:#e5e7eb; }
.dark .sdr-card-hd.open { border-bottom-color:#1f2937; }

.sdr-card-body { padding:16px; }

/* ── Inputs ───────────────────────────────────────────────────────────── */
.sdr-lbl { font-size:.69rem; font-weight:700; letter-spacing:.05em; text-transform:uppercase; margin-top:14px; margin-bottom:3px; color:#6b7280; }
.dark .sdr-lbl { color:#9ca3af; }

.sdr-in { width:100%; font-size:.8125rem; border-radius:7px; padding:7px 10px; border:1px solid; outline:none; transition:border-color .15s; }
.sdr-in { border-color:#d1d5db; background:#f9fafb; color:#111827; }
.dark .sdr-in { border-color:#374151; background:#0f172a; color:#f1f5f9; }
.sdr-in:focus { border-color:#f97316; }

.sdr-ta { width:100%; font-size:.8125rem; border-radius:7px; padding:8px 10px; border:1px solid; outline:none; resize:vertical; line-height:1.6; transition:border-color .15s; }
.sdr-ta { border-color:#d1d5db; background:#f9fafb; color:#111827; }
.dark .sdr-ta { border-color:#374151; background:#0f172a; color:#f1f5f9; }
.sdr-ta:focus { border-color:#f97316; }

/* ── Caixa de dica ────────────────────────────────────────────────────── */
.tip-box { border-radius:9px; padding:8px 12px; display:flex; gap:8px; align-items:flex-start; font-size:.8rem; border:1px solid; margin-top:4px; }
.tip-box { background:#faf5ff; border-color:#e9d5ff; color:#6b21a8; }
.dark .tip-box { background:rgba(88,28,135,.18); border-color:rgba(139,92,246,.25); color:#c4b5fd; }
.tip-box input { background:transparent; border:none; outline:none; width:100%; font-size:.8rem; color:inherit; }

/* ── Escolhas ─────────────────────────────────────────────────────────── */
.choice-row { display:flex; gap:8px; align-items:center; margin-top:6px; }
.btn-add-choice { width:100%; padding:7px; font-size:.78rem; border:1.5px dashed; border-radius:8px; margin-top:8px; cursor:pointer; transition:background .15s; }
.btn-add-choice { border-color:#fdba74; color:#f97316; }
.btn-add-choice:hover { background:#fff7ed; }
.dark .btn-add-choice { border-color:#92400e; color:#fb923c; }
.dark .btn-add-choice:hover { background:rgba(249,115,22,.08); }

.btn-rm { width:26px; height:26px; display:flex; align-items:center; justify-content:center; border-radius:7px; font-size:.85rem; color:#f87171; cursor:pointer; flex-shrink:0; transition:background .15s; }
.btn-rm:hover { background:#fef2f2; }
.dark .btn-rm:hover { background:rgba(239,68,68,.12); }

/* ── Rodapé do card ───────────────────────────────────────────────────── */
.card-ft { display:flex; align-items:center; justify-content:space-between; margin-top:14px; padding-top:12px; border-top:1px solid; }
.card-ft { border-color:#f3f4f6; }
.dark .card-ft { border-color:#1f2937; }
</style>
@endpush

@section('content')
<div x-data="sdrEditor()" x-init="init()" class="sdr-wrap">

    {{-- ═══ SIDEBAR ═══════════════════════════════════════════════════════════ --}}
    <div class="sdr-side">

        <div class="sdr-side-hd">
            <div class="text-[10px] font-bold text-orange-500 uppercase tracking-widest">MM Cloud</div>
            <div class="text-sm font-semibold text-neutral-800 dark:text-white mt-0.5 leading-tight">Guia SDR<br><span class="text-xs font-normal text-neutral-400">Primeiro Contato</span></div>
        </div>

        <div class="sdr-section-title">FLUXO DA LIGAÇÃO</div>
        <template x-for="s in stages" :key="s.name">
            <button class="sdr-btn" :class="activeStage === s.name ? 'active' : ''" @click="activeStage = s.name">
                <span class="sdr-dot" :style="`background:${s.color}`"></span>
                <span x-text="s.name"></span>
                <span class="sdr-count" x-text="nodesByStage(s.name).length"></span>
            </button>
        </template>

        <div class="sdr-side-ft">
            <div class="text-[10px] text-center text-neutral-400 mb-2" id="save-status"></div>
            <button @click="addNodeOnStage()"
                class="w-full mb-1.5 py-1.5 text-xs border border-neutral-300 dark:border-neutral-700 rounded-lg text-neutral-500 dark:text-neutral-400 hover:bg-neutral-100 dark:hover:bg-neutral-800 transition">
                + Novo nó
            </button>
            <button @click="saveScript()" class="w-full py-2 text-sm rounded-lg font-semibold btn-mmcriativos">
                Salvar script
            </button>
            <div class="text-[10px] text-center text-neutral-400 mt-2" x-text="'Script ativo · ' + scriptVersion"></div>
        </div>
    </div>

    {{-- ═══ CONTEÚDO PRINCIPAL ═════════════════════════════════════════════════ --}}
    <div class="sdr-main">

        {{-- Cabeçalho da etapa --}}
        <div class="flex items-center gap-3 mb-1">
            <span class="badge-sdr" :class="badgeClass(activeStage)" x-text="activeStage"></span>
            <h2 class="text-base font-semibold text-neutral-800 dark:text-white" x-text="stageMeta[activeStage]?.title"></h2>
        </div>
        <p class="text-sm text-neutral-400 mb-6" x-text="stageMeta[activeStage]?.subtitle"></p>

        {{-- Nó inicial (seletor rápido) --}}
        <div class="flex items-center gap-2 mb-5 p-3 bg-neutral-50 dark:bg-neutral-900 rounded-xl border border-neutral-200 dark:border-neutral-800">
            <span class="text-xs text-neutral-500 dark:text-neutral-400 font-medium whitespace-nowrap">Nó inicial:</span>
            <select x-model="flow.startNode" @change="setDirty()"
                class="sdr-in flex-1" style="margin-top:0; padding:5px 8px;">
                <template x-for="n in allNodes()" :key="n.id">
                    <option :value="n.id" x-text="n.id + ' — ' + n.label"></option>
                </template>
            </select>
        </div>

        {{-- Empty state --}}
        <template x-if="nodesByStage(activeStage).length === 0">
            <div class="text-center py-12 text-neutral-400">
                <div class="text-3xl mb-3">📭</div>
                <p class="text-sm mb-3">Nenhum nó nesta etapa ainda.</p>
                <button @click="addNodeOnStage()" class="text-sm text-orange-500 hover:underline">
                    + Criar nó em "<span x-text="activeStage"></span>"
                </button>
            </div>
        </template>

        {{-- Cards dos nós --}}
        <template x-for="node in nodesByStage(activeStage)" :key="node.id">
            <div class="sdr-card">

                {{-- Header --}}
                <div class="sdr-card-hd" :class="openNodes[node.id] ? 'open' : ''" @click="toggleNode(node.id)">
                    <div class="flex-1 min-w-0">
                        <div class="text-sm font-semibold text-neutral-800 dark:text-white truncate" x-text="node.label"></div>
                        <div class="text-[11px] font-mono text-neutral-400 mt-0.5" x-text="node.id"></div>
                    </div>
                    <div class="flex items-center gap-2 flex-shrink-0">
                        <span x-show="flow.startNode === node.id"
                            class="text-[10px] px-2 py-0.5 rounded-full font-semibold bg-orange-100 text-orange-600 dark:bg-orange-900/30 dark:text-orange-400">
                            início
                        </span>
                        <span class="text-neutral-400 text-xs transition-transform duration-150"
                            :class="openNodes[node.id] ? 'rotate-180' : ''">▼</span>
                    </div>
                </div>

                {{-- Body --}}
                <div x-show="openNodes[node.id]" x-collapse>
                    <div class="sdr-card-body">

                        <div class="sdr-lbl">Label do nó</div>
                        <input type="text" x-model="node.label" @input="setDirty()" class="sdr-in" placeholder="Nome curto do nó...">

                        <div class="sdr-lbl">
                            📢 Script — o que o SDR diz
                            <span class="normal-case font-normal opacity-60 ml-1">{company} · {contact} · {sdr_name}</span>
                        </div>
                        <textarea rows="4" x-model="node.script" @input="setDirty()" class="sdr-ta"
                            placeholder="Frase que o SDR vai falar..."></textarea>

                        <div class="sdr-lbl">💡 Dica para o SDR</div>
                        <div class="tip-box">
                            <span class="flex-shrink-0">💡</span>
                            <input type="text" x-model="node.tip" @input="setDirty()"
                                placeholder="Orientação visível só para o SDR...">
                        </div>

                        {{-- Resultado (só Encerrado) --}}
                        <template x-if="node.stage === 'Encerrado'">
                            <div>
                                <div class="sdr-lbl">Resultado da call</div>
                                <select x-model="node.outcome" @change="setDirty()" class="sdr-in">
                                    <option value="reuniao_agendada">🎯 Reunião agendada</option>
                                    <option value="retorno_agendado">📅 Retorno agendado</option>
                                    <option value="sem_interesse">👋 Sem interesse</option>
                                    <option value="encerrado_manualmente">⏹ Encerrado manualmente</option>
                                </select>
                            </div>
                        </template>

                        {{-- Escolhas (só não-Encerrado) --}}
                        <template x-if="node.stage !== 'Encerrado'">
                            <div>
                                <div class="sdr-lbl">🗣 O prospect disse...</div>
                                <template x-for="(c, ci) in (node.choices || [])" :key="ci">
                                    <div class="choice-row">
                                        <input type="text" x-model="c.label" @input="setDirty()"
                                            placeholder="Resposta do prospect..."
                                            class="sdr-in flex-1" style="margin-top:0">
                                        <span class="text-neutral-400 text-sm flex-shrink-0">→</span>
                                        <select x-model="c.next" @change="setDirty()"
                                            class="sdr-in flex-1" style="margin-top:0">
                                            <template x-for="n in allNodes()" :key="n.id">
                                                <option :value="n.id" x-text="n.id + ' — ' + n.label.substring(0,30)"></option>
                                            </template>
                                        </select>
                                        <button @click="removeChoice(node, ci)" class="btn-rm">✕</button>
                                    </div>
                                </template>
                                <button @click="addChoice(node)" class="btn-add-choice">+ Adicionar resposta</button>
                            </div>
                        </template>

                        {{-- Rodapé do card --}}
                        <div class="card-ft">
                            <template x-if="flow.startNode !== node.id">
                                <button @click="flow.startNode = node.id; setDirty()"
                                    class="text-xs text-neutral-400 hover:text-orange-500 transition">
                                    Definir como nó inicial
                                </button>
                            </template>
                            <template x-if="flow.startNode === node.id">
                                <span class="text-xs text-orange-400 font-medium">✓ Nó inicial</span>
                            </template>
                            <button @click="deleteNode(node.id)"
                                class="text-xs text-red-400 hover:text-red-300 border border-red-100 dark:border-red-900 hover:bg-red-50 dark:hover:bg-red-950 px-3 py-1.5 rounded-lg transition">
                                Remover nó
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </template>

        {{-- Botão rápido --}}
        <button @click="addNodeOnStage()"
            class="w-full py-3 text-sm border-2 border-dashed border-neutral-200 dark:border-neutral-800 rounded-xl text-neutral-400 hover:border-orange-300 hover:text-orange-500 dark:hover:border-orange-800 transition mt-2">
            + Adicionar nó em "<span x-text="activeStage"></span>"
        </button>
        <div class="h-12"></div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const SAVE_URL     = '{{ route("admin.commercial.sdr.editor.save") }}';
const CSRF         = '{{ csrf_token() }}';
const LOADED_FLOW  = @json($script ? $script->flow : $defaultFlow);

function sdrEditor() {
    return {
        flow:          null,
        openNodes:     {},
        dirty:         false,
        activeStage:   'Abertura',
        scriptVersion: '{{ $script ? "v".($script->version ?? $script->id) : "padrão" }}',

        stages: [
            { name:'Abertura',     color:'#1D9E75' },
            { name:'Qualificação', color:'#378ADD' },
            { name:'Pitch',        color:'#7F77DD' },
            { name:'Objeção',      color:'#f97316' },
            { name:'Fechamento',   color:'#EF9F27' },
            { name:'Encerrado',    color:'#6b7280' },
        ],

        stageMeta: {
            'Abertura':     { title:'Abertura da Ligação',      subtitle:'Primeiros 30 segundos — o objetivo é abrir, não vender.' },
            'Qualificação': { title:'Qualificação do Lead',     subtitle:'Identificar o cenário atual de atendimento e onde está a dor.' },
            'Pitch':        { title:'Apresentação da Solução',  subtitle:'Mostrar o valor da MM Cloud de forma objetiva e criar curiosidade.' },
            'Objeção':      { title:'Gestão de Objeções',       subtitle:'Cenários comuns de resistência e como conduzir cada um.' },
            'Fechamento':   { title:'Fechamento',               subtitle:'Converter o interesse em reunião agendada. Sempre duas opções de horário.' },
            'Encerrado':    { title:'Encerramento',             subtitle:'Registrar o resultado e acionar o pós-call adequado.' },
        },

        init() {
            this.flow = (LOADED_FLOW && LOADED_FLOW.nodes) ? LOADED_FLOW : { startNode:'', nodes:{} };
            this.$nextTick(() => { if (window.lucide) lucide.createIcons(); });
        },

        allNodes()           { return Object.values(this.flow.nodes || {}); },
        nodesByStage(s)      { return this.allNodes().filter(n => n.stage === s); },
        toggleNode(id)       { this.openNodes[id] = !this.openNodes[id]; },

        // Badge class sem depender de purge do Tailwind
        badgeClass(stage) {
            const map = {
                'Abertura':    'badge-Abertura',
                'Qualificação':'badge-Qualificacao',
                'Pitch':       'badge-Pitch',
                'Objeção':     'badge-Objecao',
                'Fechamento':  'badge-Fechamento',
                'Encerrado':   'badge-Encerrado',
            };
            return map[stage] || 'badge-Encerrado';
        },

        setDirty() {
            this.dirty = true;
            const el = document.getElementById('save-status');
            el.textContent = '● Não salvo';
            el.style.color = '#eab308';
        },

        addNodeOnStage() {
            const id = 'node_' + Date.now();
            this.flow.nodes[id] = { id, stage: this.activeStage, label: 'Novo nó', script: '', tip: '', choices: [] };
            this.openNodes[id] = true;
            this.setDirty();
        },

        deleteNode(id) {
            if (!confirm('Remover este nó?')) return;
            delete this.flow.nodes[id];
            this.setDirty();
        },

        addChoice(node) {
            if (!node.choices) node.choices = [];
            node.choices.push({ label: '', next: Object.keys(this.flow.nodes)[0] || '' });
            this.setDirty();
        },

        removeChoice(node, idx) { node.choices.splice(idx, 1); this.setDirty(); },

        async saveScript() {
            const el = document.getElementById('save-status');
            el.textContent = 'Salvando...'; el.style.color = '#f59e0b';
            try {
                const res  = await fetch(SAVE_URL, {
                    method:  'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
                    body:    JSON.stringify({ name: 'Script Principal', version: 'v1', flow: this.flow }),
                });
                const json = await res.json();
                if (json.ok) {
                    el.textContent = '✓ Salvo'; el.style.color = '#22c55e';
                    this.dirty = false;
                    setTimeout(() => { el.textContent = ''; }, 3000);
                } else {
                    el.textContent = 'Erro ao salvar'; el.style.color = '#ef4444';
                }
            } catch(e) {
                el.textContent = 'Erro: ' + e.message; el.style.color = '#ef4444';
            }
        },
    };
}
</script>
@endpush
