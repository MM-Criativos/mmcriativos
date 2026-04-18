{{-- Vee Drawer — Etapa 2 do v3 --}}
<div
    x-data="veeDrawer()"
    x-on:open-vee.window="openDrawer()"
    x-on:keydown.escape.window="closeDrawer()"
    x-cloak
>
    {{-- Backdrop --}}
    <div
        x-show="open"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 bg-black/70 z-[60]"
        @click="closeDrawer()"
        style="display:none;"
    ></div>

    {{-- Drawer Panel --}}
    <div
        x-show="open"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="translate-x-full"
        x-transition:enter-end="translate-x-0"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="translate-x-0"
        x-transition:leave-end="translate-x-full"
        class="fixed inset-0 z-[70] flex flex-col bg-[#0a0a0a] transform"
        style="display:none;"
    >
        {{-- ===== HEADER ===== --}}
        <div class="flex items-center justify-between px-5 h-16 border-b border-neutral-800 flex-shrink-0">

            {{-- Left: logo + status --}}
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg bg-[#ff8800] flex items-center justify-center">
                    <i class="fa-duotone fa-solid fa-robot text-sm text-white"></i>
                </div>
                <div class="flex items-center gap-2">
                    <span class="text-white font-semibold text-lg">Vee</span>
                    <span class="w-2 h-2 rounded-full"
                          :class="isStreaming ? 'bg-[#ff8800] animate-pulse' : 'bg-green-500'"></span>
                </div>
                <span x-show="currentSession && currentSession.title"
                      x-text="currentSession ? currentSession.title : ''"
                      class="text-neutral-500 text-sm truncate max-w-[200px]"></span>
            </div>

            {{-- Center: mode tabs --}}
            <div class="flex items-center gap-1 bg-neutral-900 rounded-lg p-1">
                <button @click="setMode('chat')"
                    :class="mode === 'chat' ? 'bg-[#ff8800] text-white' : 'text-neutral-400 hover:text-white'"
                    class="px-3 py-1.5 rounded-md text-sm font-medium transition">
                    Chat
                </button>
                <button @click="setMode('cowork')"
                    :class="mode === 'cowork' ? 'bg-[#ff8800] text-white' : 'text-neutral-400 hover:text-white'"
                    class="px-3 py-1.5 rounded-md text-sm font-medium transition">
                    Cowork
                </button>
                <button @click="setMode('log')"
                    :class="mode === 'log' ? 'bg-[#ff8800] text-white' : 'text-neutral-400 hover:text-white'"
                    class="px-3 py-1.5 rounded-md text-sm font-medium transition">
                    Log
                </button>
            </div>

            {{-- Right: history + close --}}
            <div class="flex items-center gap-2">
                <button @click="toggleHistory()"
                    :class="showHistory ? 'bg-neutral-700 text-white' : 'text-neutral-400 hover:text-white'"
                    class="flex items-center gap-2 px-3 py-1.5 rounded-lg text-sm transition">
                    <i class="fa-regular fa-clock-rotate-left"></i>
                    <span>Histórico</span>
                </button>
                <button @click="newSession()"
                    class="text-neutral-400 hover:text-[#ff8800] w-8 h-8 flex items-center justify-center rounded-lg transition"
                    title="Nova sessão">
                    <i class="fa-regular fa-plus"></i>
                </button>
                <button @click="closeDrawer()"
                    class="text-neutral-400 hover:text-white w-8 h-8 flex items-center justify-center rounded-lg transition"
                    title="Fechar">
                    <i class="fa-regular fa-xmark text-lg"></i>
                </button>
            </div>
        </div>

        {{-- ===== BODY ===== --}}
        <div class="flex flex-1 overflow-hidden relative">

            {{-- History Sidebar --}}
            <div
                x-show="showHistory"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="-translate-x-full opacity-0"
                x-transition:enter-end="translate-x-0 opacity-100"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="translate-x-0 opacity-100"
                x-transition:leave-end="-translate-x-full opacity-0"
                class="absolute left-0 top-0 bottom-0 w-72 bg-[#111] border-r border-neutral-800 z-10 flex flex-col"
                style="display:none;"
            >
                <div class="px-4 py-3 border-b border-neutral-800 flex items-center justify-between">
                    <span class="text-white font-medium text-sm">Tarefas</span>
                    <button @click="showHistory = false" class="text-neutral-500 hover:text-white transition">
                        <i class="fa-regular fa-xmark"></i>
                    </button>
                </div>
                <div class="flex-1 overflow-y-auto py-2">
                    <template x-if="sessions.length === 0">
                        <p class="text-neutral-600 text-sm text-center py-8">Nenhuma tarefa ainda</p>
                    </template>
                    <template x-for="sess in sessions" :key="sess.id">
                        <div
                            @click="loadSession(sess.id)"
                            :class="currentSession && currentSession.id === sess.id
                                ? 'bg-neutral-800 border-l-2 border-[#ff8800]'
                                : 'hover:bg-neutral-900 border-l-2 border-transparent'"
                            class="px-4 py-3 cursor-pointer transition group flex items-start justify-between gap-2">
                            <div class="flex-1 min-w-0">
                                <p x-text="sess.title" class="text-white text-sm truncate"></p>
                                <div class="flex items-center gap-2 mt-0.5">
                                    <span x-text="sess.mode"
                                          :class="sess.mode === 'cowork' ? 'text-orange-400' : 'text-neutral-500'"
                                          class="text-xs capitalize"></span>
                                    <span class="text-neutral-700 text-xs"
                                          x-text="formatDate(sess.updated_at)"></span>
                                </div>
                            </div>
                            <button @click.stop="deleteSession(sess.id)"
                                class="opacity-0 group-hover:opacity-100 text-neutral-600 hover:text-red-400 transition flex-shrink-0 mt-0.5">
                                <i class="fa-regular fa-trash text-xs"></i>
                            </button>
                        </div>
                    </template>
                </div>
            </div>

            {{-- ===== CHAT / COWORK VIEW ===== --}}
            <div x-show="mode !== 'log'" class="flex flex-col flex-1 overflow-hidden">

                {{-- Messages --}}
                <div
                    x-ref="messagesArea"
                    class="flex-1 overflow-y-auto px-4 py-6 space-y-4"
                    :class="showHistory ? 'pl-[calc(72*4px+1rem)]' : ''"
                >
                    {{-- Empty state --}}
                    <template x-if="messages.length === 0">
                        <div class="flex flex-col items-center justify-center h-full text-center py-20">
                            <div class="w-16 h-16 rounded-2xl bg-[#ff8800]/10 border border-[#ff8800]/20 flex items-center justify-center mb-4">
                                <i class="fa-duotone fa-solid fa-robot text-[#ff8800] text-2xl"></i>
                            </div>
                            <p class="text-white font-medium text-lg">Olá! Sou a Vee.</p>
                            <p class="text-neutral-500 text-sm mt-1">
                                <span x-show="mode === 'chat'">Aqui para planejar, discutir e organizar ideias.</span>
                                <span x-show="mode === 'cowork'">Pronta para executar tarefas com acesso às ferramentas.</span>
                            </p>
                        </div>
                    </template>

                    {{-- Messages list --}}
                    <template x-for="(msg, idx) in messages" :key="idx">
                        <div>
                            {{-- User message --}}
                            <template x-if="msg.role === 'user'">
                                <div class="flex justify-end">
                                    <div class="max-w-[70%] bg-[#ff8800] text-white rounded-2xl rounded-tr-sm px-4 py-3 text-sm">
                                        <p x-text="msg.content" class="whitespace-pre-wrap"></p>
                                    </div>
                                </div>
                            </template>

                            {{-- Assistant message --}}
                            <template x-if="msg.role === 'assistant'">
                                <div class="flex justify-start gap-3">
                                    <div class="w-7 h-7 rounded-lg bg-[#ff8800]/20 flex items-center justify-center flex-shrink-0 mt-1">
                                        <i class="fa-solid fa-robot text-[#ff8800] text-xs"></i>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        {{-- Tool activities --}}
                                        <template x-if="msg.toolActivities && msg.toolActivities.length > 0">
                                            <div class="space-y-1 mb-2">
                                                <template x-for="(tool, ti) in msg.toolActivities" :key="ti">
                                                    <div :class="{
                                                            'border-[#ff8800]/50 bg-[#ff8800]/5': tool.status === 'running',
                                                            'border-green-800/50 bg-green-950/20': tool.status === 'done',
                                                            'border-red-800/50 bg-red-950/20': tool.status === 'error'
                                                         }"
                                                         class="border rounded-lg px-3 py-2 text-xs font-mono">
                                                        <div class="flex items-center gap-2">
                                                            <span x-show="tool.status === 'running'"
                                                                  class="w-3 h-3 border border-[#ff8800] border-t-transparent rounded-full animate-spin flex-shrink-0"></span>
                                                            <i x-show="tool.status === 'done'"
                                                               class="fa-solid fa-check text-green-500 flex-shrink-0"></i>
                                                            <i x-show="tool.status === 'error'"
                                                               class="fa-solid fa-xmark text-red-500 flex-shrink-0"></i>
                                                            <span class="text-neutral-300" x-text="tool.name"></span>
                                                            <span x-show="tool.duration_ms"
                                                                  x-text="tool.duration_ms + 'ms'"
                                                                  class="text-neutral-600 ml-auto"></span>
                                                        </div>
                                                        <template x-if="tool.status === 'error' && tool.error">
                                                            <p x-text="tool.error" class="text-red-400 mt-1 truncate"></p>
                                                        </template>
                                                    </div>
                                                </template>
                                            </div>
                                        </template>

                                        {{-- Text content --}}
                                        <template x-if="msg.content">
                                            <div class="bg-[#1a1a1a] border border-neutral-800 rounded-2xl rounded-tl-sm px-4 py-3 text-sm text-neutral-100
                                                        prose prose-invert prose-sm max-w-none
                                                        prose-headings:text-white prose-code:text-[#ff8800]
                                                        prose-pre:bg-neutral-900 prose-pre:border prose-pre:border-neutral-700"
                                                 x-html="renderMarkdown(msg.content)">
                                            </div>
                                        </template>

                                        {{-- Streaming cursor --}}
                                        <template x-if="isStreaming && idx === messages.length - 1 && !msg.content">
                                            <div class="bg-[#1a1a1a] border border-neutral-800 rounded-2xl rounded-tl-sm px-4 py-3">
                                                <span class="inline-block w-1.5 h-4 bg-[#ff8800] animate-pulse rounded-sm"></span>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </template>

                    {{-- Streaming indicator when no assistant msg yet --}}
                    <template x-if="isStreaming && (messages.length === 0 || messages[messages.length-1].role === 'user')">
                        <div class="flex justify-start gap-3">
                            <div class="w-7 h-7 rounded-lg bg-[#ff8800]/20 flex items-center justify-center flex-shrink-0">
                                <i class="fa-solid fa-robot text-[#ff8800] text-xs"></i>
                            </div>
                            <div class="bg-[#1a1a1a] border border-neutral-800 rounded-2xl rounded-tl-sm px-4 py-3">
                                <span class="inline-block w-1.5 h-4 bg-[#ff8800] animate-pulse rounded-sm"></span>
                            </div>
                        </div>
                    </template>
                </div>

                {{-- Input area --}}
                <div class="border-t border-neutral-800 px-4 py-3 flex-shrink-0 bg-[#0d0d0d]">
                    <div class="flex items-end gap-3">
                        <textarea
                            x-model="inputText"
                            @keydown.enter.prevent="if (!$event.shiftKey && !isStreaming) sendMessage()"
                            :disabled="isStreaming"
                            placeholder="Escreva uma mensagem... (Enter para enviar, Shift+Enter para quebra de linha)"
                            rows="1"
                            class="flex-1 bg-neutral-900 border border-neutral-700 rounded-xl px-4 py-3 text-white text-sm
                                   placeholder-neutral-600 resize-none focus:outline-none focus:border-[#ff8800] transition
                                   disabled:opacity-50"
                            style="min-height: 46px; max-height: 180px;"
                            @input="$el.style.height = 'auto'; $el.style.height = Math.min($el.scrollHeight, 180) + 'px'"
                        ></textarea>
                        <button
                            @click="sendMessage()"
                            :disabled="isStreaming || !inputText.trim()"
                            class="w-11 h-11 rounded-xl bg-[#ff8800] text-white flex items-center justify-center
                                   hover:bg-orange-600 transition disabled:opacity-40 disabled:cursor-not-allowed flex-shrink-0">
                            <i x-show="!isStreaming" class="fa-solid fa-paper-plane-top text-sm"></i>
                            <span x-show="isStreaming"
                                  class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
                        </button>
                    </div>
                </div>
            </div>

            {{-- ===== LOG VIEW ===== --}}
            <div x-show="mode === 'log'" class="flex flex-col flex-1 overflow-hidden">

                {{-- Log header / filters --}}
                <div class="border-b border-neutral-800 px-4 py-2 flex items-center gap-2 flex-shrink-0 bg-[#0d0d0d]">
                    <span class="text-neutral-500 text-xs">Filtro:</span>
                    <template x-for="f in logFilters" :key="f.key">
                        <button
                            @click="logFilter = f.key"
                            :class="logFilter === f.key ? 'bg-[#ff8800] text-white' : 'bg-neutral-900 text-neutral-400 hover:text-white'"
                            class="px-2.5 py-1 rounded-md text-xs transition"
                            x-text="f.label">
                        </button>
                    </template>
                    <div class="ml-auto flex items-center gap-2">
                        <span x-show="logConnected"
                              class="flex items-center gap-1.5 text-green-400 text-xs">
                            <span class="w-1.5 h-1.5 bg-green-400 rounded-full animate-pulse"></span>
                            Ao vivo
                        </span>
                        <span x-show="!logConnected" class="text-neutral-600 text-xs">Desconectado</span>
                        <button @click="clearLogEvents()"
                                class="text-neutral-600 hover:text-neutral-400 text-xs transition">
                            Limpar
                        </button>
                    </div>
                </div>

                {{-- Log events --}}
                <div x-ref="logArea" class="flex-1 overflow-y-auto px-4 py-3 space-y-1.5 font-mono">
                    <template x-if="logEventsFilt.length === 0">
                        <div class="flex items-center justify-center py-16">
                            <p class="text-neutral-700 text-sm">Nenhum evento ainda. Aguardando atividade...</p>
                        </div>
                    </template>
                    <template x-for="(evt, i) in logEventsFilt" :key="i">
                        <div :class="{
                            'border-[#ff8800]/30 bg-[#ff8800]/5': evt.type === 'tool_start',
                            'border-green-800/40 bg-green-950/10': evt.type === 'tool_result',
                            'border-red-800/40 bg-red-950/10': evt.type === 'tool_error',
                            'border-neutral-800 bg-neutral-950': !['tool_start','tool_result','tool_error'].includes(evt.type)
                        }" class="border rounded-lg px-3 py-2 text-xs">
                            <div class="flex items-center gap-3">
                                <span class="text-neutral-600" x-text="formatTime(evt.timestamp)"></span>
                                <span :class="{
                                    'text-[#ff8800]': evt.type === 'tool_start',
                                    'text-green-400': evt.type === 'tool_result',
                                    'text-red-400': evt.type === 'tool_error',
                                    'text-neutral-400': !['tool_start','tool_result','tool_error'].includes(evt.type)
                                }" x-text="evt.type" class="font-semibold w-24 flex-shrink-0"></span>
                                <span x-show="evt.tool_name"
                                      x-text="evt.tool_name"
                                      class="text-white flex-shrink-0"></span>
                                <span x-show="evt.duration_ms"
                                      x-text="evt.duration_ms + 'ms'"
                                      class="text-neutral-600 ml-auto flex-shrink-0"></span>
                            </div>
                            <template x-if="evt.args">
                                <p x-text="truncate(evt.args, 120)"
                                   class="text-neutral-600 mt-0.5 pl-3 truncate"></p>
                            </template>
                            <template x-if="evt.result && evt.type === 'tool_result'">
                                <p x-text="truncate(evt.result, 120)"
                                   class="text-neutral-500 mt-0.5 pl-3 truncate"></p>
                            </template>
                            <template x-if="evt.error">
                                <p x-text="evt.error"
                                   class="text-red-400 mt-0.5 pl-3 truncate"></p>
                            </template>
                        </div>
                    </template>
                </div>
            </div>

        </div>{{-- end body --}}
    </div>{{-- end drawer panel --}}
</div>

<style>
    [x-cloak] { display: none !important; }

    /* Prose overrides inside vee-drawer */
    .vee-msg-content h1,
    .vee-msg-content h2,
    .vee-msg-content h3 { color: #fff; margin-top: 0.75rem; margin-bottom: 0.25rem; }
    .vee-msg-content p { margin-bottom: 0.5rem; }
    .vee-msg-content code { color: #ff8800; background: #1f1f1f; padding: 0.1rem 0.3rem; border-radius: 4px; }
    .vee-msg-content pre { background: #111; border: 1px solid #333; border-radius: 8px; padding: 0.75rem; overflow-x: auto; }
    .vee-msg-content pre code { color: #e2e8f0; background: transparent; padding: 0; }
    .vee-msg-content ul, .vee-msg-content ol { padding-left: 1.25rem; margin-bottom: 0.5rem; }
    .vee-msg-content li { margin-bottom: 0.125rem; }
    .vee-msg-content a { color: #ff8800; }
    .vee-msg-content blockquote { border-left: 3px solid #ff8800; padding-left: 0.75rem; color: #aaa; }
    .vee-msg-content table { width: 100%; border-collapse: collapse; margin-bottom: 0.5rem; }
    .vee-msg-content th, .vee-msg-content td { border: 1px solid #333; padding: 0.4rem 0.75rem; font-size: 0.8rem; }
    .vee-msg-content th { background: #1a1a1a; color: #ff8800; }
</style>

<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('veeDrawer', () => ({
        open: false,
        showHistory: false,
        mode: 'chat',

        currentSession: null,
        messages: [],

        isStreaming: false,
        streamController: null,

        sessions: [],

        logEvents: [],
        logEventSource: null,
        logConnected: false,
        logFilter: 'all',
        logFilters: [
            { key: 'all',   label: 'Todos'  },
            { key: 'tool',  label: 'Tools'  },
            { key: 'n8n',   label: 'n8n'    },
            { key: 'ssh',   label: 'SSH'    },
            { key: 'db',    label: 'DB'     },
        ],

        inputText: '',

        agentUrl: window.VEE_AGENT_URL  || '',
        agentToken: window.VEE_AGENT_TOKEN || '',

        // ---- computed ----
        get logEventsFilt() {
            if (this.logFilter === 'all') return this.logEvents;
            return this.logEvents.filter(e => {
                if (this.logFilter === 'tool') return e.tool_name;
                if (this.logFilter === 'n8n')  return e.tool_name && e.tool_name.startsWith('n8n');
                if (this.logFilter === 'ssh')  return e.tool_name && (e.tool_name.startsWith('ssh') || e.tool_name.startsWith('disk') || e.tool_name.startsWith('memory') || e.tool_name.startsWith('container') || e.tool_name.startsWith('tail') || e.tool_name.startsWith('service') || e.tool_name.startsWith('restart') || e.tool_name.startsWith('inspect'));
                if (this.logFilter === 'db')   return e.tool_name && e.tool_name.startsWith('db');
                return true;
            });
        },

        // ---- lifecycle ----
        openDrawer() {
            this.open = true;
            document.body.classList.add('overflow-hidden');
            this.loadSessions();
            if (this.mode === 'log') this.startLogFeed();
        },

        closeDrawer() {
            if (this.isStreaming && this.streamController) {
                this.streamController.abort();
                this.isStreaming = false;
            }
            this.open = false;
            document.body.classList.remove('overflow-hidden');
        },

        setMode(m) {
            if (m === this.mode) return;
            if (m === 'log') {
                this.startLogFeed();
            } else if (this.mode === 'log') {
                this.stopLogFeed();
            }
            this.mode = m;
        },

        toggleHistory() {
            this.showHistory = !this.showHistory;
            if (this.showHistory) this.loadSessions();
        },

        newSession() {
            this.currentSession = null;
            this.messages = [];
        },

        // ---- sessions ----
        async loadSessions() {
            try {
                const res = await fetch(`${this.agentUrl}/sessions?limit=50`, {
                    headers: this.authHeaders(),
                });
                if (!res.ok) return;
                const data = await res.json();
                this.sessions = data.sessions || [];
            } catch {}
        },

        async loadSession(id) {
            try {
                const res = await fetch(`${this.agentUrl}/sessions/${id}`, {
                    headers: this.authHeaders(),
                });
                if (!res.ok) return;
                const data = await res.json();
                this.currentSession = data.session;
                this.messages = this.normalizeMessages(data.messages || []);
                this.showHistory = false;
                this.$nextTick(() => this.scrollToBottom());
            } catch {}
        },

        async deleteSession(id) {
            try {
                await fetch(`${this.agentUrl}/sessions/${id}`, {
                    method: 'DELETE',
                    headers: this.authHeaders(),
                });
                this.sessions = this.sessions.filter(s => s.id !== id);
                if (this.currentSession && this.currentSession.id === id) {
                    this.currentSession = null;
                    this.messages = [];
                }
            } catch {}
        },

        normalizeMessages(raw) {
            const msgs = [];
            for (const m of raw) {
                if (m.role === 'user') {
                    msgs.push({ role: 'user', content: m.content || '', toolActivities: [] });
                } else if (m.role === 'assistant') {
                    msgs.push({ role: 'assistant', content: m.content || '', toolActivities: [] });
                }
                // tool roles are skipped — they'll be shown as toolActivities inline if needed
            }
            return msgs;
        },

        // ---- streaming ----
        async sendMessage() {
            const text = this.inputText.trim();
            if (!text || this.isStreaming) return;

            this.inputText = '';
            this.messages.push({ role: 'user', content: text, toolActivities: [] });
            await this.$nextTick();
            this.scrollToBottom();

            await this.streamSSE(text);
        },

        async streamSSE(message) {
            this.isStreaming = true;
            this.streamController = new AbortController();

            // Placeholder for assistant response
            const assistantIdx = this.messages.length;
            this.messages.push({ role: 'assistant', content: '', toolActivities: [] });

            try {
                const res = await fetch(`${this.agentUrl}/stream`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        ...this.authHeaders(),
                    },
                    body: JSON.stringify({
                        message,
                        session_id: this.currentSession?.id ?? undefined,
                        mode: this.mode,
                    }),
                    signal: this.streamController.signal,
                });

                if (!res.ok) {
                    this.messages[assistantIdx].content = `Erro: ${res.status} ${res.statusText}`;
                    this.isStreaming = false;
                    return;
                }

                const reader = res.body.getReader();
                const decoder = new TextDecoder();
                let buffer = '';

                while (true) {
                    const { done, value } = await reader.read();
                    if (done) break;

                    buffer += decoder.decode(value, { stream: true });
                    const lines = buffer.split('\n');
                    buffer = lines.pop();

                    for (const line of lines) {
                        if (!line.startsWith('data: ')) continue;
                        const raw = line.slice(6).trim();
                        if (!raw || raw === '[DONE]') continue;

                        let evt;
                        try { evt = JSON.parse(raw); } catch { continue; }

                        switch (evt.type) {
                            case 'session_created':
                                this.currentSession = { id: evt.session_id, title: 'Nova tarefa', mode: evt.mode };
                                break;

                            case 'text':
                                this.messages[assistantIdx].content += evt.content;
                                this.$nextTick(() => this.scrollToBottom());
                                break;

                            case 'tool_start':
                                this.messages[assistantIdx].toolActivities.push({
                                    call_id: evt.call_id,
                                    name: evt.name,
                                    args: evt.args,
                                    status: 'running',
                                    result: null,
                                    error: null,
                                    duration_ms: null,
                                });
                                this.$nextTick(() => this.scrollToBottom());
                                break;

                            case 'tool_result': {
                                const tool = this.messages[assistantIdx].toolActivities
                                    .find(t => t.call_id === evt.call_id);
                                if (tool) {
                                    tool.status = 'done';
                                    tool.result = evt.result;
                                    tool.duration_ms = evt.duration_ms;
                                }
                                break;
                            }

                            case 'tool_error': {
                                const tool = this.messages[assistantIdx].toolActivities
                                    .find(t => t.call_id === evt.call_id);
                                if (tool) {
                                    tool.status = 'error';
                                    tool.error = evt.error;
                                    tool.duration_ms = evt.duration_ms;
                                }
                                break;
                            }

                            case 'session_titled':
                                if (this.currentSession) {
                                    this.currentSession.title = evt.title;
                                    const s = this.sessions.find(s => s.id === evt.session_id);
                                    if (s) s.title = evt.title;
                                    else this.loadSessions();
                                }
                                break;

                            case 'warning':
                                console.warn('[Vee]', evt.message);
                                break;

                            case 'done':
                                this.isStreaming = false;
                                break;

                            case 'error':
                                this.messages[assistantIdx].content += `\n\n**Erro:** ${evt.message}`;
                                this.isStreaming = false;
                                break;
                        }
                    }
                }
            } catch (err) {
                if (err.name !== 'AbortError') {
                    this.messages[assistantIdx].content = 'Erro de conexão com a Vee Agent API.';
                }
            } finally {
                this.isStreaming = false;
                this.streamController = null;
            }
        },

        // ---- log feed ----
        startLogFeed() {
            if (this.logEventSource) return;
            const url = `${this.agentUrl}/log/feed`;
            // EventSource não suporta custom headers; para APIs sem auth ou com token na query
            // Se o token for necessário, usar query param ou fetch stream
            this.logEventSource = new EventSource(
                this.agentToken ? `${url}?token=${encodeURIComponent(this.agentToken)}` : url
            );
            this.logEventSource.onopen = () => { this.logConnected = true; };
            this.logEventSource.onerror = () => { this.logConnected = false; };
            this.logEventSource.onmessage = (e) => {
                try {
                    const evt = JSON.parse(e.data);
                    if (evt.type === 'connected') return;
                    this.logEvents.unshift(evt);
                    if (this.logEvents.length > 200) this.logEvents.pop();
                } catch {}
            };
        },

        stopLogFeed() {
            if (this.logEventSource) {
                this.logEventSource.close();
                this.logEventSource = null;
                this.logConnected = false;
            }
        },

        clearLogEvents() {
            this.logEvents = [];
        },

        // ---- helpers ----
        authHeaders() {
            if (!this.agentToken) return {};
            return { 'Authorization': `Bearer ${this.agentToken}` };
        },

        renderMarkdown(text) {
            if (!text) return '';
            if (window.marked) {
                return window.marked.parse(text, { breaks: true, gfm: true });
            }
            return text.replace(/\n/g, '<br>');
        },

        scrollToBottom() {
            const el = this.$refs.messagesArea;
            if (el) el.scrollTop = el.scrollHeight;
        },

        formatDate(iso) {
            if (!iso) return '';
            const d = new Date(iso);
            const now = new Date();
            const diff = now - d;
            if (diff < 60000) return 'agora';
            if (diff < 3600000) return Math.floor(diff/60000) + 'min';
            if (diff < 86400000) return Math.floor(diff/3600000) + 'h';
            return d.toLocaleDateString('pt-BR', { day: '2-digit', month: '2-digit' });
        },

        formatTime(iso) {
            if (!iso) return '';
            return new Date(iso).toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
        },

        truncate(str, len) {
            if (!str) return '';
            const s = typeof str === 'object' ? JSON.stringify(str) : String(str);
            return s.length > len ? s.slice(0, len) + '…' : s;
        },
    }));
});
</script>
