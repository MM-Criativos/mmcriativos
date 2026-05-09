{{-- ─── SDR CALL MODAL ─────────────────────────────────────────────────────── --}}
{{-- Inclua no final da view: @include('admin.commercial.prospeccao.call-modal') --}}

<div id="sdr-call-modal"
    class="fixed inset-0 z-[9999] hidden bg-black/70 backdrop-blur-sm flex items-center justify-center p-4"
    onclick="if(event.target===this && SdrCall.canClose())SdrCall.close()">

    <div class="bg-gray-900 border border-gray-700 rounded-2xl w-full max-w-2xl max-h-[90vh] flex flex-col shadow-2xl"
        onclick="event.stopPropagation()">

        {{-- Header --}}
        <div class="flex items-center gap-3 px-6 py-4 border-b border-gray-800">
            <div class="flex-1 min-w-0">
                <div id="sdr-modal-company" class="text-white font-semibold text-base truncate">—</div>
                <div id="sdr-modal-meta" class="text-gray-400 text-xs mt-0.5"></div>
            </div>
            <div id="sdr-timer" class="text-orange-400 font-mono text-lg font-semibold min-w-[56px] text-right">00:00</div>
            <button id="sdr-end-btn" onclick="SdrCall.end()"
                class="px-3 py-1.5 text-sm border border-red-700 text-red-400 rounded-lg hover:bg-red-950 transition">
                Encerrar
            </button>
            <button onclick="if(SdrCall.canClose())SdrCall.close()"
                class="p-1.5 text-gray-500 hover:text-gray-300 rounded-lg hover:bg-gray-800 transition text-lg leading-none">
                ✕
            </button>
        </div>

        {{-- Stage bar --}}
        <div id="sdr-stage-bar" class="flex items-center gap-1.5 px-6 py-3 border-b border-gray-800 flex-wrap"></div>

        {{-- Scrollable body --}}
        <div class="flex-1 overflow-y-auto px-6 py-4" id="sdr-modal-body"></div>
    </div>
</div>

<script>
const SdrCall = (() => {
    const CALL_START_URL = '{{ route("admin.commercial.sdr.call.start") }}';
    const CALL_END_BASE  = '{{ url("admin/commercial/sdr/call") }}';
    const CSRF           = '{{ csrf_token() }}';

    const STAGES = ['Abertura','Qualificação','Pitch','Objeção','Fechamento','Encerrado'];
    const STAGE_COLORS = {
        'Abertura':    { dot: '#1D9E75', text: 'text-teal-400' },
        'Qualificação':{ dot: '#378ADD', text: 'text-blue-400' },
        'Pitch':       { dot: '#7F77DD', text: 'text-purple-400' },
        'Objeção':     { dot: '#f97316', text: 'text-orange-400' },
        'Fechamento':  { dot: '#EF9F27', text: 'text-yellow-400' },
        'Encerrado':   { dot: '#6b7280', text: 'text-gray-400' },
    };
    const OUTCOME_UI = {
        'reuniao_agendada': { icon:'🎯', label:'Reunião agendada!', color:'text-green-400', bg:'bg-green-950 border-green-800' },
        'retorno_agendado': { icon:'📅', label:'Retorno agendado',  color:'text-blue-400',  bg:'bg-blue-950 border-blue-800' },
        'sem_interesse':    { icon:'👋', label:'Sem interesse',     color:'text-gray-400',  bg:'bg-gray-800 border-gray-700' },
    };

    let state = {
        sessionId: null, flow: null, currentNode: null,
        path: [], prospect: {}, startTime: null, timerRef: null,
    };

    async function open(prospect) {
        state.prospect   = prospect;
        state.path       = [];
        state.startTime  = Date.now();

        document.getElementById('sdr-modal-company').textContent = prospect.company;
        document.getElementById('sdr-modal-meta').textContent =
            `com ${prospect.contact || '—'} · SDR: ${prospect.sdrName}`;
        document.getElementById('sdr-call-modal').classList.remove('hidden');
        document.body.style.overflow = 'hidden';

        startTimer();
        renderBody('<div class="text-center text-gray-400 py-8">Carregando script...</div>');

        try {
            const res = await fetch(CALL_START_URL, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
                body: JSON.stringify({
                    lead_id:  prospect.leadId || null,
                    sdr_name: prospect.sdrName,
                    company:  prospect.company,
                    contact:  prospect.contact || null,
                }),
            });
            const data = await res.json();
            state.sessionId   = data.session_id;
            state.flow        = data.flow;
            state.currentNode = state.flow.startNode;
            render();
        } catch(e) {
            renderBody(`<div class="text-red-400 text-sm">Erro ao carregar script: ${e.message}</div>`);
        }
    }

    async function end() {
        if (!state.sessionId) { close(); return; }
        const node    = state.flow?.nodes?.[state.currentNode];
        const outcome = node?.outcome || 'encerrado_manualmente';
        try {
            await fetch(`${CALL_END_BASE}/${state.sessionId}/end`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
                body: JSON.stringify({ path: state.path, outcome }),
            });
        } catch(e) { console.warn('Could not log call end:', e); }
        close();
    }

    function close() {
        clearInterval(state.timerRef);
        document.getElementById('sdr-call-modal').classList.add('hidden');
        document.body.style.overflow = '';
        state = { sessionId:null, flow:null, currentNode:null, path:[], prospect:{}, startTime:null, timerRef:null };
    }

    function canClose() {
        if (!state.sessionId) return true;
        return confirm('A call ainda está em andamento. Deseja encerrar sem salvar o resultado?');
    }

    // ── Timer ──────────────────────────────────────────────────────────────
    function startTimer() {
        clearInterval(state.timerRef);
        state.timerRef = setInterval(() => {
            const s   = Math.floor((Date.now() - state.startTime) / 1000);
            const m   = String(Math.floor(s / 60)).padStart(2, '0');
            const sec = String(s % 60).padStart(2, '0');
            document.getElementById('sdr-timer').textContent = `${m}:${sec}`;
        }, 1000);
    }

    // ── Render ─────────────────────────────────────────────────────────────
    function render() {
        if (!state.flow) return;
        const node = state.flow.nodes[state.currentNode];
        if (!node) { renderBody('<div class="text-red-400">Nó não encontrado.</div>'); return; }
        renderStageBar(node.stage);
        node.stage === 'Encerrado' ? renderOutcome(node) : renderCard(node);
    }

    function renderStageBar(currentStage) {
        const bar = document.getElementById('sdr-stage-bar');
        bar.innerHTML = STAGES.map((s, i) => {
            const currIdx  = STAGES.indexOf(currentStage);
            const isDone   = i < currIdx;
            const isCurr   = i === currIdx;
            const color    = STAGE_COLORS[s] || { dot:'#6b7280', text:'text-gray-500' };
            const dotStyle = (isDone || isCurr)
                ? `background:${color.dot};${isCurr ? `outline:3px solid ${color.dot}33;outline-offset:2px` : ''}`
                : 'background:#374151';
            const textCls  = isCurr ? `${color.text} font-medium` : (isDone ? 'text-gray-500' : 'text-gray-600');
            const arrow    = i < STAGES.length - 1 ? '<span class="text-gray-700 text-xs">›</span>' : '';
            return `<span class="flex items-center gap-1 text-xs ${textCls}">
                <span class="w-2 h-2 rounded-full flex-shrink-0" style="${dotStyle}"></span>${s}
            </span>${arrow}`;
        }).join('');
    }

    function renderCard(node) {
        const script  = substitute(node.script || '');
        const tip     = node.tip || '';
        const choices = (node.choices || []).map(c => `
            <button onclick="SdrCall.pick('${esc(node.id)}','${esc(c.label)}','${esc(c.next)}')"
                class="w-full text-left px-4 py-3 bg-gray-800 hover:bg-gray-700 border border-gray-700
                       hover:border-orange-500 rounded-xl text-sm text-gray-200 hover:text-white
                       transition-all duration-150 mb-2">
                ${esc(c.label)}
            </button>`).join('');

        const pathHtml = state.path.length ? `
            <div class="mt-4 pt-4 border-t border-gray-800">
                <p class="text-xs text-gray-500 font-medium mb-2 uppercase tracking-wide">Caminho percorrido</p>
                <div class="space-y-1.5">
                    ${state.path.map((step, i) => `
                        <div class="flex items-start gap-2 text-xs text-gray-500">
                            <span class="w-4 h-4 rounded-full bg-gray-800 text-gray-400 flex items-center justify-center flex-shrink-0 text-[10px]">${i+1}</span>
                            <span>${esc(step.nodeLabel)}</span>
                            <span class="text-orange-400 font-medium">→ ${esc(step.choiceLabel)}</span>
                        </div>`).join('')}
                </div>
            </div>` : '';

        renderBody(`
            <div class="bg-gray-800 border border-gray-700 border-l-4 rounded-xl p-5 mb-4"
                style="border-left-color:#7F77DD;">
                <p class="text-xs font-medium text-purple-400 uppercase tracking-wider mb-1">${esc(node.stage)}</p>
                <h3 class="text-white font-semibold text-base mb-4">${esc(node.label)}</h3>
                ${script ? `<div class="bg-gray-900 rounded-xl px-4 py-3 text-sm text-gray-100 leading-relaxed mb-4 border border-gray-700">
                    <span class="text-gray-500 text-xs block mb-1">Você diz</span>
                    "${esc(script)}"
                </div>` : ''}
                ${tip ? `<div class="flex gap-2 bg-purple-950/50 border border-purple-900 rounded-lg px-3 py-2 mb-5">
                    <span class="text-base">💡</span>
                    <p class="text-xs text-purple-300">${esc(tip)}</p>
                </div>` : ''}
                <p class="text-xs text-gray-500 font-medium uppercase tracking-wide mb-3">O prospect disse</p>
                ${choices}
            </div>
            ${pathHtml}
        `);
    }

    function renderOutcome(node) {
        const ui = OUTCOME_UI[node.outcome] || { icon:'✓', label:'Encerrado', color:'text-gray-400', bg:'bg-gray-800 border-gray-700' };
        renderBody(`
            <div class="border rounded-2xl p-8 text-center ${ui.bg}">
                <div class="text-4xl mb-3">${ui.icon}</div>
                <h3 class="text-lg font-semibold ${ui.color} mb-1">${ui.label}</h3>
                <p class="text-sm text-gray-400 mb-6">Call encerrada. Clique em salvar para registrar no histórico.</p>
                <button onclick="SdrCall.end()"
                    class="px-6 py-2.5 bg-orange-500 hover:bg-orange-600 text-white rounded-xl font-medium text-sm transition">
                    Salvar e encerrar
                </button>
            </div>
            <div class="mt-4 pt-4 border-t border-gray-800">
                <p class="text-xs text-gray-500 font-medium mb-2 uppercase tracking-wide">Caminho percorrido</p>
                <div class="space-y-1.5">
                    ${state.path.map((step, i) => `
                        <div class="flex items-start gap-2 text-xs text-gray-500">
                            <span class="w-4 h-4 rounded-full bg-gray-800 text-gray-400 flex items-center justify-center flex-shrink-0 text-[10px]">${i+1}</span>
                            <span>${esc(step.nodeLabel)}</span>
                            <span class="text-orange-400 font-medium">→ ${esc(step.choiceLabel)}</span>
                        </div>`).join('')}
                </div>
            </div>
        `);
    }

    function renderBody(html) {
        document.getElementById('sdr-modal-body').innerHTML = html;
    }

    function pick(nodeId, choiceLabel, nextId) {
        const node = state.flow.nodes[nodeId];
        state.path.push({ nodeId, nodeLabel: node?.label || nodeId, choiceLabel });
        state.currentNode = nextId;
        render();
    }

    function substitute(text) {
        return (text || '')
            .replace(/\{company\}/g,  state.prospect.company  || '')
            .replace(/\{contact\}/g,  state.prospect.contact  || '')
            .replace(/\{sdr_name\}/g, state.prospect.sdrName  || '');
    }

    function esc(s) {
        return String(s || '')
            .replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;')
            .replace(/"/g,'&quot;').replace(/'/g,'&#39;');
    }

    return { open, end, close, canClose, pick };
})();
</script>
