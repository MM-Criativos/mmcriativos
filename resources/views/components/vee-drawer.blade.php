{{-- Vee Drawer v3 --}}
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
        class="fixed inset-0 z-[70] flex overflow-hidden transform"
        style="display:none; background:#0f0f0f; color:#ffffff; font-family:'Inter',sans-serif;"
    >

        {{-- ===== ICON SIDEBAR (56px) ===== --}}
        <div class="vee-sidebar">
            {{-- Logo --}}
            <div class="vee-sidebar-logo-wrap">
                <div class="vee-logo-icon">
                    <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M4 6l8 12 8-12"/>
                    </svg>
                </div>
            </div>

            {{-- Nav tabs --}}
            <nav class="vee-nav">
                <button @click="tab='plan'" title="Planejamento"
                    :class="tab==='plan' ? 'vee-nav-btn--active' : 'vee-nav-btn--inactive'"
                    class="vee-nav-btn">
                    <i data-lucide="message-square"></i>
                </button>
                <button @click="tab='orch'" title="Orquestramento"
                    :class="tab==='orch' ? 'vee-nav-btn--active' : 'vee-nav-btn--inactive'"
                    class="vee-nav-btn">
                    <i data-lucide="git-branch"></i>
                </button>
                <button @click="tab='cowork'" title="Cowork"
                    :class="tab==='cowork' ? 'vee-nav-btn--active' : 'vee-nav-btn--inactive'"
                    class="vee-nav-btn">
                    <i data-lucide="calendar-check"></i>
                </button>
                <button @click="switchToLogs()" title="Logs"
                    :class="tab==='logs' ? 'vee-nav-btn--active' : 'vee-nav-btn--inactive'"
                    class="vee-nav-btn">
                    <i data-lucide="file-text"></i>
                </button>
            </nav>

            {{-- Bottom: call + close --}}
            <div class="vee-sidebar-bottom">
                <button @click="toggleCall()" title="Ligar para a Vee"
                    :class="callOpen ? 'vee-call-btn--active' : 'vee-call-btn--inactive'"
                    class="vee-call-btn">
                    <i x-show="!callOpen" data-lucide="phone" style="color:#ff8800;"></i>
                    <i x-show="callOpen" data-lucide="phone-off" style="color:#fff;"></i>
                </button>
                <button @click="closeDrawer()" title="Fechar"
                    class="vee-nav-btn vee-nav-btn--inactive" style="margin-top:4px;">
                    <i data-lucide="x"></i>
                </button>
            </div>
        </div>

        {{-- ===== CALL PANEL (200px, docked) ===== --}}
        <div
            x-show="callOpen"
            x-transition:enter="transition ease-out duration-[250ms]"
            x-transition:enter-start="opacity-0 scale-[.92]"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-[.92]"
            class="vee-call-panel"
            style="display:none;"
        >
            {{-- Header --}}
            <div class="vee-call-header">
                <div style="display:flex;align-items:center;gap:8px;">
                    <span class="vee-call-dot-pulse"></span>
                    <span style="font-size:12px;font-weight:600;color:#ff8800;">Em chamada</span>
                </div>
                <button @click="toggleCall()"
                    style="background:rgba(239,68,68,.12);border:1px solid rgba(239,68,68,.27);border-radius:5px;cursor:pointer;padding:3px 8px;color:#ef4444;font-size:11px;font-weight:600;font-family:'Inter',sans-serif;">
                    Encerrar
                </button>
            </div>

            {{-- Rings --}}
            <div class="vee-rings-wrap">
                <div :class="`vee-ring vee-ring-1 vee-ring--${callMode}`"></div>
                <div :class="`vee-ring vee-ring-2 vee-ring--${callMode}`"></div>
                <div :class="`vee-ring vee-ring-3 vee-ring--${callMode}`"></div>
                <div :class="`vee-ring vee-ring-4 vee-ring--${callMode}`"></div>
                <div class="vee-avatar-center">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M4 6l8 12 8-12"/>
                    </svg>
                </div>
            </div>

            {{-- Status --}}
            <div style="text-align:center;padding:0 12px 14px;">
                <div style="font-size:13px;font-weight:600;color:#fff;">Vee</div>
                <div x-text="callStatusText"
                     :style="`font-size:11px;font-weight:500;margin-top:2px;letter-spacing:.3px;color:${callStatusColor};`"></div>
            </div>

            {{-- Transcript --}}
            <div x-ref="callTranscript"
                style="flex:1;overflow-y:auto;padding:0 10px 10px;display:flex;flex-direction:column;gap:6px;border-top:1px solid #2e2e2e;padding-top:10px;">
                <template x-for="(m, i) in callTranscript" :key="i">
                    <div :style="m.who==='user'
                            ? 'background:rgba(255,136,0,.12);border:1px solid rgba(255,136,0,.35);'
                            : 'background:#222;border:1px solid #2e2e2e;'"
                         style="padding:6px 9px;border-radius:6px;font-size:12px;line-height:1.45;color:#fff;">
                        <div :style="m.who==='user' ? 'color:#ff8800;' : 'color:#555;'"
                             style="font-size:10px;margin-bottom:2px;font-weight:600;"
                             x-text="m.who==='user'?'Você':'Vee'"></div>
                        <span x-text="m.text"></span>
                    </div>
                </template>
            </div>

            {{-- Mute --}}
            <div style="padding:10px 12px;border-top:1px solid #2e2e2e;display:flex;justify-content:center;">
                <button @click="callMuted=!callMuted"
                    :style="callMuted ? 'background:rgba(239,68,68,.12);border:1px solid rgba(239,68,68,.67);' : 'background:#222;border:1px solid #2e2e2e;'"
                    style="width:40px;height:40px;border-radius:50%;cursor:pointer;display:flex;align-items:center;justify-content:center;transition:all .2s;position:relative;"
                    :title="callMuted ? 'Ativar microfone' : 'Mutar microfone'">
                    <i data-lucide="mic"
                       :style="callMuted ? 'color:#ef4444;stroke:#ef4444;' : 'color:#999;stroke:#999;'"></i>
                    <svg x-show="callMuted" style="position:absolute;inset:0;width:100%;height:100%;pointer-events:none;" viewBox="0 0 40 40">
                        <line x1="10" y1="10" x2="30" y2="30" stroke="#ef4444" stroke-width="2" stroke-linecap="round"/>
                    </svg>
                </button>
            </div>
        </div>

        {{-- ===== MAIN AREA ===== --}}
        <div style="flex:1;display:flex;flex-direction:column;overflow:hidden;min-width:0;">

            {{-- Top Bar (48px) --}}
            <div style="height:48px;background:#1a1a1a;border-bottom:1px solid #2e2e2e;display:flex;align-items:center;justify-content:space-between;padding:0 20px;flex-shrink:0;">
                <span x-text="tabLabels[tab]" style="font-size:14px;font-weight:600;color:#fff;"></span>

                {{-- Orch view toggle --}}
                <div x-show="tab==='orch'" style="display:flex;align-items:center;gap:6px;">
                    <span style="font-size:12px;color:#999;">Visualização:</span>
                    <template x-for="[v, l] in [['A','Feed'],['B','Pipeline'],['C','Grafo']]" :key="v">
                        <button @click="orchView=v"
                            :style="orchView===v ? 'background:#ff8800;border-color:#ff8800;color:#fff;' : 'background:transparent;border-color:#2e2e2e;color:#999;'"
                            style="padding:4px 10px;font-size:12px;font-weight:500;cursor:pointer;border-radius:5px;border:1px solid;transition:all .15s;font-family:'Inter',sans-serif;"
                            x-text="l"></button>
                    </template>
                </div>

                {{-- Env status --}}
                <div style="display:flex;align-items:center;gap:6px;">
                    <span style="width:7px;height:7px;border-radius:50%;background:#ff8800;display:inline-block;"></span>
                    <span style="font-size:12px;color:#999;">4 ambientes ativos</span>
                </div>
            </div>

            {{-- Content --}}
            <div style="flex:1;overflow:hidden;position:relative;">

                {{-- ===== TAB: PLANEJAMENTO ===== --}}
                <div x-show="tab==='plan'" class="flex h-full" style="display:none;">

                    {{-- Conversations Sidebar (220px) --}}
                    <div style="width:220px;flex-shrink:0;border-right:1px solid #2e2e2e;display:flex;flex-direction:column;background:#1a1a1a;">
                        <div style="padding:14px 16px;border-bottom:1px solid #2e2e2e;display:flex;justify-content:space-between;align-items:center;">
                            <span style="font-size:13px;font-weight:600;color:#fff;">Conversas</span>
                            <button @click="newSession()"
                                style="background:#ff8800;border:none;border-radius:5px;cursor:pointer;width:24px;height:24px;display:flex;align-items:center;justify-content:center;">
                                <i data-lucide="plus" style="stroke:#fff;"></i>
                            </button>
                        </div>
                        <div style="flex:1;overflow-y:auto;">
                            <template x-if="sessions.length === 0">
                                <p style="color:#555;font-size:12px;text-align:center;padding:32px 16px;">Nenhuma conversa ainda</p>
                            </template>
                            <template x-for="sess in sessions" :key="sess.id">
                                <div @click="loadSession(sess.id)"
                                    :style="currentSession && currentSession.id === sess.id
                                        ? 'background:rgba(255,136,0,.12);border-left:3px solid #ff8800;'
                                        : 'background:transparent;border-left:3px solid transparent;'"
                                    style="padding:12px 16px;cursor:pointer;border-bottom:1px solid #2e2e2e;transition:background .15s;">
                                    <div style="display:flex;justify-content:space-between;margin-bottom:2px;">
                                        <span x-text="sess.title"
                                              style="font-size:13px;font-weight:600;color:#fff;flex:1;margin-right:8px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"></span>
                                        <span x-text="formatDate(sess.updated_at)"
                                              style="font-size:11px;color:#555;flex-shrink:0;"></span>
                                    </div>
                                    <div style="font-size:12px;color:#999;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;margin-bottom:6px;"
                                         x-text="sess.preview || '...'"></div>
                                    <div style="display:flex;gap:10px;">
                                        <button @click.stop="toggleStar(sess.id)"
                                            :style="starredSessions.includes(sess.id) ? 'color:#f59e0b;' : 'color:#555;'"
                                            style="background:none;border:none;cursor:pointer;padding:0;">
                                            <svg width="12" height="12" viewBox="0 0 24 24" :fill="starredSessions.includes(sess.id) ? '#f59e0b' : 'none'" :stroke="starredSessions.includes(sess.id) ? '#f59e0b' : '#555'" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>
                                        </button>
                                        <button @click.stop="deleteSession(sess.id)"
                                            style="background:none;border:none;cursor:pointer;padding:0;color:#555;">
                                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="#555" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"></path></svg>
                                        </button>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>

                    {{-- Chat area --}}
                    <div style="flex:1;display:flex;flex-direction:column;overflow:hidden;min-width:0;">
                        {{-- Chat header --}}
                        <div style="padding:12px 20px;border-bottom:1px solid #2e2e2e;background:#1a1a1a;display:flex;justify-content:space-between;align-items:center;flex-shrink:0;">
                            <div>
                                <div style="font-size:14px;font-weight:600;color:#fff;"
                                     x-text="currentSession ? currentSession.title : 'Nova conversa'"></div>
                                <div style="font-size:12px;color:#999;"
                                     x-text="messages.length + ' mensagem' + (messages.length !== 1 ? 's' : '')"></div>
                            </div>
                            <div style="display:flex;gap:6px;">
                                <span style="font-size:11px;padding:3px 8px;background:#2a2a2a;border:1px solid #2e2e2e;border-radius:4px;color:#999;">local</span>
                                <span style="font-size:11px;padding:3px 8px;background:#2a2a2a;border:1px solid #2e2e2e;border-radius:4px;color:#999;">vault</span>
                            </div>
                        </div>

                        {{-- Messages --}}
                        <div x-ref="messagesArea" style="flex:1;overflow-y:auto;padding:20px;display:flex;flex-direction:column;gap:14px;">

                            {{-- Empty state --}}
                            <template x-if="messages.length === 0">
                                <div style="display:flex;flex-direction:column;align-items:center;justify-content:center;height:100%;text-align:center;padding:80px 0;">
                                    <div style="width:56px;height:56px;border-radius:16px;background:rgba(255,136,0,.1);border:1px solid rgba(255,136,0,.2);display:flex;align-items:center;justify-content:center;margin-bottom:16px;">
                                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#ff8800" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M4 6l8 12 8-12"/>
                                        </svg>
                                    </div>
                                    <p style="color:#fff;font-weight:500;font-size:17px;">Olá! Sou a Vee.</p>
                                    <p style="color:#999;font-size:13px;margin-top:4px;">Aqui para planejar, discutir e coordenar agentes.</p>
                                </div>
                            </template>

                            {{-- Messages list --}}
                            <template x-for="(msg, idx) in messages" :key="idx">
                                <div>
                                    {{-- User message --}}
                                    <template x-if="msg.role === 'user'">
                                        <div style="display:flex;justify-content:flex-end;">
                                            <div style="max-width:70%;background:#ff8800;color:#fff;border-radius:8px;padding:10px 14px;font-size:14px;line-height:1.55;">
                                                <p x-text="msg.content" style="white-space:pre-wrap;margin:0;"></p>
                                            </div>
                                        </div>
                                    </template>

                                    {{-- Assistant message --}}
                                    <template x-if="msg.role === 'assistant'">
                                        <div style="display:flex;justify-content:flex-start;gap:10px;align-items:flex-end;">
                                            <div style="width:30px;height:30px;border-radius:50%;background:rgba(255,136,0,.12);border:1px solid rgba(255,136,0,.35);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#ff8800" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                                    <path d="M4 6l8 12 8-12"/>
                                                </svg>
                                            </div>
                                            <div style="flex:1;min-width:0;">
                                                {{-- Tool activities --}}
                                                <template x-if="msg.toolActivities && msg.toolActivities.length > 0">
                                                    <div style="display:flex;flex-direction:column;gap:4px;margin-bottom:8px;">
                                                        <template x-for="(tool, ti) in msg.toolActivities" :key="ti">
                                                            <div :style="tool.status==='running' ? 'border-color:rgba(255,136,0,.5);background:rgba(255,136,0,.05);' : tool.status==='done' ? 'border-color:rgba(34,197,94,.4);background:rgba(34,197,94,.05);' : 'border-color:rgba(239,68,68,.4);background:rgba(239,68,68,.05);'"
                                                                 style="border:1px solid;border-radius:6px;padding:6px 10px;font-size:11px;font-family:'JetBrains Mono',monospace;">
                                                                <div style="display:flex;align-items:center;gap:8px;">
                                                                    <span x-show="tool.status === 'running'"
                                                                          style="width:10px;height:10px;border:1.5px solid #ff8800;border-top-color:transparent;border-radius:50%;animation:spin .8s linear infinite;flex-shrink:0;display:inline-block;"></span>
                                                                    <svg x-show="tool.status === 'done'" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="#22c55e" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;"><polyline points="20 6 9 17 4 12"></polyline></svg>
                                                                    <svg x-show="tool.status === 'error'" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="#ef4444" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                                                                    <span style="color:#e2e8f0;" x-text="tool.name"></span>
                                                                    <span x-show="tool.duration_ms"
                                                                          x-text="tool.duration_ms + 'ms'"
                                                                          style="color:#555;margin-left:auto;"></span>
                                                                </div>
                                                                <template x-if="tool.status === 'error' && tool.error">
                                                                    <p x-text="tool.error" style="color:#ef4444;margin:4px 0 0;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"></p>
                                                                </template>
                                                            </div>
                                                        </template>
                                                    </div>
                                                </template>

                                                {{-- Text content --}}
                                                <template x-if="msg.content">
                                                    <div class="vee-msg-content"
                                                         style="background:#1a1a1a;border:1px solid #2e2e2e;border-radius:8px;padding:10px 14px;font-size:14px;color:#e2e8f0;line-height:1.55;"
                                                         x-html="renderMarkdown(msg.content)">
                                                    </div>
                                                </template>

                                                {{-- Streaming cursor --}}
                                                <template x-if="isStreaming && idx === messages.length - 1 && !msg.content">
                                                    <div style="background:#1a1a1a;border:1px solid #2e2e2e;border-radius:8px;padding:10px 14px;">
                                                        <span style="display:inline-block;width:6px;height:16px;background:#ff8800;animation:pulse 1s ease infinite;border-radius:2px;"></span>
                                                    </div>
                                                </template>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </template>

                            {{-- Streaming placeholder --}}
                            <template x-if="isStreaming && (messages.length === 0 || messages[messages.length-1].role === 'user')">
                                <div style="display:flex;justify-content:flex-start;gap:10px;align-items:flex-end;">
                                    <div style="width:30px;height:30px;border-radius:50%;background:rgba(255,136,0,.12);border:1px solid rgba(255,136,0,.35);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#ff8800" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M4 6l8 12 8-12"/>
                                        </svg>
                                    </div>
                                    <div style="background:#1a1a1a;border:1px solid #2e2e2e;border-radius:8px;padding:10px 14px;">
                                        <span style="display:inline-block;width:6px;height:16px;background:#ff8800;animation:pulse 1s ease infinite;border-radius:2px;"></span>
                                    </div>
                                </div>
                            </template>
                        </div>

                        {{-- Input --}}
                        <div style="border-top:1px solid #2e2e2e;padding:12px 20px;flex-shrink:0;background:#0d0d0d;">
                            <div style="border:1px solid #2e2e2e;border-radius:8px;overflow:hidden;background:#222;">
                                <textarea
                                    x-model="inputText"
                                    @keydown.enter.prevent="if (!$event.shiftKey && !isStreaming) sendMessage()"
                                    :disabled="isStreaming"
                                    placeholder="Escreva para a Vee..."
                                    rows="1"
                                    style="width:100%;padding:12px 14px;background:transparent;border:none;outline:none;color:#fff;font-size:14px;resize:none;height:68px;font-family:'Inter',sans-serif;"
                                    @input="$el.style.height='auto'; $el.style.height=Math.min($el.scrollHeight,180)+'px'"
                                ></textarea>
                                <div style="display:flex;justify-content:space-between;padding:8px 12px;border-top:1px solid #2e2e2e;">
                                    <div style="display:flex;gap:10px;">
                                        <button style="background:none;border:none;cursor:pointer;padding:4px;border-radius:4px;">
                                            <i data-lucide="mic" style="stroke:#999;width:16px;height:16px;"></i>
                                        </button>
                                        <button style="background:none;border:none;cursor:pointer;padding:4px;border-radius:4px;">
                                            <i data-lucide="paperclip" style="stroke:#999;width:16px;height:16px;"></i>
                                        </button>
                                    </div>
                                    <button @click="sendMessage()"
                                        :disabled="isStreaming || !inputText.trim()"
                                        style="display:inline-flex;align-items:center;gap:6px;padding:7px 14px;border-radius:6px;font-size:13px;font-weight:500;cursor:pointer;background:#ff8800;color:#fff;border:none;transition:all .15s;font-family:'Inter',sans-serif;"
                                        :style="(isStreaming || !inputText.trim()) ? 'opacity:.4;cursor:not-allowed;' : ''">
                                        <i x-show="!isStreaming" data-lucide="send" style="width:12px;height:12px;stroke:#fff;"></i>
                                        <span x-show="isStreaming" style="width:12px;height:12px;border:2px solid #fff;border-top-color:transparent;border-radius:50%;animation:spin .8s linear infinite;display:inline-block;"></span>
                                        Enviar
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ===== TAB: ORQUESTRAMENTO ===== --}}
                <div x-show="tab==='orch'" class="flex h-full" style="display:none;">

                    {{-- Agents sidebar (200px) --}}
                    <div style="width:200px;flex-shrink:0;border-right:1px solid #2e2e2e;background:#1a1a1a;display:flex;flex-direction:column;">
                        <div style="padding:14px 16px;border-bottom:1px solid #2e2e2e;font-size:13px;font-weight:600;color:#fff;">Agentes</div>
                        <div style="flex:1;overflow-y:auto;">
                            <template x-for="agent in orchAgents" :key="agent.id">
                                <div @click="activeAgentId=agent.id"
                                    :style="activeAgentId===agent.id ? 'background:rgba(255,136,0,.12);border-left:3px solid #ff8800;' : 'background:transparent;border-left:3px solid transparent;'"
                                    style="padding:12px 16px;cursor:pointer;border-bottom:1px solid #2e2e2e;transition:background .15s;">
                                    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:2px;">
                                        <span x-text="agent.name" style="font-size:13px;font-weight:600;color:#fff;"></span>
                                        <span :style="`background:${agentStatusColor(agent.status)};`"
                                              style="width:7px;height:7px;border-radius:50%;display:inline-block;"></span>
                                    </div>
                                    <div x-text="agent.env" style="font-size:11px;color:#555;margin-bottom:2px;"></div>
                                    <div x-text="agent.task" :style="`color:${agentStatusColor(agent.status)};`" style="font-size:12px;"></div>
                                </div>
                            </template>
                        </div>
                    </div>

                    {{-- Feed view --}}
                    <div x-show="orchView==='A'" style="flex:1;display:flex;flex-direction:column;overflow:hidden;display:none;">
                        <div style="padding:14px 20px;border-bottom:1px solid #2e2e2e;background:#1a1a1a;display:flex;gap:8px;align-items:center;flex-shrink:0;">
                            <span style="font-size:14px;font-weight:600;color:#fff;">Feed de atividade</span>
                            <div style="flex:1;"></div>
                            <span class="vee-badge vee-badge--accent">2 ativos</span>
                            <span class="vee-badge vee-badge--success">2 concluídos</span>
                        </div>
                        <div style="flex:1;overflow-y:auto;padding:16px;display:flex;flex-direction:column;gap:8px;">
                            <template x-for="item in orchFeed" :key="item.id">
                                <div :style="`border-left:3px solid ${item.status==='done'?'#22c55e':item.status==='working'?'#ff8800':'#2e2e2e'};border-color:${item.status==='working'?'rgba(255,136,0,.35)':'#2e2e2e'};`"
                                     style="padding:12px 16px;background:#1a1a1a;border:1px solid;border-radius:8px;">
                                    <div style="display:flex;justify-content:space-between;margin-bottom:6px;">
                                        <span style="font-size:12px;font-weight:600;color:#ff8800;" x-text="item.from + ' → ' + item.to"></span>
                                        <span style="font-size:11px;color:#555;font-family:'JetBrains Mono',monospace;" x-text="item.time"></span>
                                    </div>
                                    <div style="font-size:13px;color:#fff;line-height:1.5;" x-text="item.msg"></div>
                                    <template x-if="item.status==='working'">
                                        <div style="margin-top:10px;height:5px;background:#2a2a2a;border-radius:4px;overflow:hidden;">
                                            <div :style="`width:${item.pct}%;`" style="height:100%;background:#ff8800;border-radius:4px;transition:width .4s;"></div>
                                        </div>
                                    </template>
                                    <template x-if="item.status==='done'">
                                        <div style="margin-top:6px;font-size:11px;color:#22c55e;">✓ concluído</div>
                                    </template>
                                </div>
                            </template>
                        </div>
                        <div style="padding:10px 16px;border-top:1px solid #2e2e2e;background:#1a1a1a;">
                            <div style="border:1px solid #2e2e2e;border-radius:8px;overflow:hidden;background:#222;">
                                <textarea placeholder="Instruir Vee ou agentes..."
                                    style="width:100%;padding:10px 14px;background:transparent;border:none;outline:none;color:#fff;font-size:14px;resize:none;height:52px;font-family:'Inter',sans-serif;"></textarea>
                                <div style="display:flex;justify-content:flex-end;padding:6px 10px;border-top:1px solid #2e2e2e;">
                                    <button style="display:inline-flex;align-items:center;gap:6px;padding:7px 14px;border-radius:6px;font-size:13px;font-weight:500;cursor:pointer;background:#ff8800;color:#fff;border:none;font-family:'Inter',sans-serif;">
                                        <i data-lucide="send" style="width:12px;height:12px;stroke:#fff;"></i> Enviar
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Pipeline view --}}
                    <div x-show="orchView==='B'" style="flex:1;overflow-y:auto;padding:24px;display:none;">
                        <div style="font-size:13px;color:#999;font-weight:500;margin-bottom:20px;">Pipeline: projeto ativo</div>
                        <div style="display:flex;align-items:center;gap:0;overflow-x:auto;padding-bottom:16px;">
                            <template x-for="(step, i) in orchPipeline" :key="step.agent">
                                <div style="display:flex;align-items:center;flex-shrink:0;">
                                    <div :style="`border-color:${step.status==='done'?'#22c55e':step.status==='working'?'#ff8800':'#2e2e2e'};`"
                                         style="min-width:168px;padding:16px;background:#1a1a1a;border:1px solid;border-radius:8px;position:relative;">
                                        <div :style="`color:${step.status==='done'?'#22c55e':step.status==='working'?'#ff8800':'#999'};`"
                                             style="font-size:12px;font-weight:700;margin-bottom:4px;" x-text="step.agent"></div>
                                        <div style="font-size:13px;color:#fff;" x-text="step.label"></div>
                                        <template x-if="step.out">
                                            <div style="font-size:11px;color:#3b82f6;margin-top:6px;font-family:'JetBrains Mono',monospace;" x-text="'→ '+step.out"></div>
                                        </template>
                                        <template x-if="step.status==='working'">
                                            <div style="margin-top:10px;height:5px;background:#2a2a2a;border-radius:4px;overflow:hidden;">
                                                <div :style="`width:${step.pct}%;`" style="height:100%;background:#ff8800;border-radius:4px;"></div>
                                            </div>
                                        </template>
                                        <div :style="`color:${step.status==='done'?'#22c55e':step.status==='working'?'#ff8800':'#555'};`"
                                             style="position:absolute;top:10px;right:12px;font-size:12px;"
                                             x-text="step.status==='done'?'✓':step.status==='working'?'●':'○'"></div>
                                    </div>
                                    <template x-if="i < orchPipeline.length - 1">
                                        <div style="font-size:18px;color:#555;padding:0 6px;">→</div>
                                    </template>
                                </div>
                            </template>
                        </div>
                    </div>

                    {{-- Graph view --}}
                    <div x-show="orchView==='C'" style="flex:1;overflow-y:auto;padding:20px;display:none;">
                        <div style="font-size:13px;color:#999;font-weight:500;margin-bottom:14px;">Topologia de agentes — projeto ativo</div>
                        <div style="position:relative;height:340px;background:#1a1a1a;border:1px solid #2e2e2e;border-radius:8px;">
                            <svg style="position:absolute;inset:0;width:100%;height:100%;">
                                <template x-for="([a,b], i) in orchGraphEdges" :key="i">
                                    <line :x1="`${orchGraphNodes[a].x}%`" :y1="`${orchGraphNodes[a].y}%`"
                                          :x2="`${orchGraphNodes[b].x}%`" :y2="`${orchGraphNodes[b].y}%`"
                                          stroke="#2e2e2e" stroke-width="1.5" stroke-dasharray="5 3"/>
                                </template>
                            </svg>
                            <template x-for="node in orchGraphNodes" :key="node.label">
                                <div :style="`left:${node.x}%;top:${node.y}%;border-color:${node.main?'#ff8800':agentStatusColor(node.status||'idle')};background:${node.main?'rgba(255,136,0,.12)':'#222'};color:${node.main?'#ff8800':'#fff'};font-weight:${node.main?'700':'600'};`"
                                     style="position:absolute;transform:translate(-50%,-50%);padding:8px 12px;border:1.5px solid;border-radius:8px;text-align:center;min-width:72px;cursor:pointer;font-size:12px;white-space:pre-line;">
                                    <span x-text="node.label"></span>
                                    <template x-if="node.status">
                                        <div :style="`color:${agentStatusColor(node.status)};`"
                                             style="font-size:10px;margin-top:2px;"
                                             x-text="node.status==='working'?'● ativo':node.status==='done'?'✓ done':'○ idle'"></div>
                                    </template>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>

                {{-- ===== TAB: COWORK ===== --}}
                <div x-show="tab==='cowork'" class="flex h-full" style="display:none;">

                    {{-- Tasks (flex: 1) --}}
                    <div style="flex:1;display:flex;flex-direction:column;border-right:1px solid #2e2e2e;overflow:hidden;min-width:0;">
                        <div style="padding:14px 20px;border-bottom:1px solid #2e2e2e;background:#1a1a1a;display:flex;justify-content:space-between;align-items:center;flex-shrink:0;">
                            <span style="font-size:13px;font-weight:600;color:#fff;">Tasks do Projeto</span>
                            <div style="display:flex;gap:6px;">
                                <span class="vee-badge vee-badge--success">2 concluídas</span>
                                <span class="vee-badge vee-badge--accent">3 rodando</span>
                                <span class="vee-badge vee-badge--muted">3 pendentes</span>
                            </div>
                        </div>
                        <div style="flex:1;overflow-y:auto;padding:14px;display:flex;flex-direction:column;gap:8px;">
                            <template x-for="task in coworkTasks" :key="task.id">
                                <div :style="`border-left:3px solid ${task.status==='done'?'#22c55e':task.status==='running'?'#ff8800':'#2e2e2e'};border-color:${task.status==='running'?'rgba(255,136,0,.35)':'#2e2e2e'};`"
                                     style="padding:12px 16px;background:#1a1a1a;border:1px solid;border-radius:8px;">
                                    <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:6px;">
                                        <div>
                                            <div style="font-size:13px;font-weight:600;color:#fff;display:flex;align-items:center;gap:8px;">
                                                <span :style="`color:${task.status==='done'?'#22c55e':task.status==='running'?'#ff8800':'#555'};`"
                                                      x-text="task.status==='done'?'✓':task.status==='running'?'●':'○'"
                                                      style="font-size:11px;"></span>
                                                <span x-text="task.title"></span>
                                            </div>
                                            <div style="display:flex;gap:12px;margin-top:4px;">
                                                <span x-text="task.agent" style="font-size:11px;color:#ff8800;font-weight:600;"></span>
                                                <span x-text="'env:'+task.env" style="font-size:11px;color:#555;"></span>
                                                <span x-text="'due '+task.due" style="font-size:11px;color:#555;"></span>
                                            </div>
                                        </div>
                                        <span :style="`background:${task.status==='done'?'rgba(34,197,94,.12)':task.status==='running'?'rgba(255,136,0,.12)':'rgba(85,85,85,.12)'};color:${task.status==='done'?'#22c55e':task.status==='running'?'#ff8800':'#555'};border:1px solid ${task.status==='done'?'rgba(34,197,94,.27)':task.status==='running'?'rgba(255,136,0,.27)':'rgba(85,85,85,.27)'};`"
                                              style="padding:2px 8px;border-radius:4px;font-size:11px;font-weight:600;white-space:nowrap;"
                                              x-text="task.status==='done'?'concluído':task.status==='running'?'rodando':'pendente'"></span>
                                    </div>
                                    <template x-if="task.status !== 'pending'">
                                        <div style="display:flex;align-items:center;gap:10px;margin-top:4px;">
                                            <div style="flex:1;height:5px;background:#2a2a2a;border-radius:4px;overflow:hidden;">
                                                <div :style="`width:${task.pct}%;background:${task.status==='done'?'#22c55e':'#ff8800'};`"
                                                     style="height:100%;border-radius:4px;transition:width .4s;"></div>
                                            </div>
                                            <span x-text="task.pct+'%'" style="font-size:11px;color:#999;min-width:28px;"></span>
                                        </div>
                                    </template>
                                </div>
                            </template>
                        </div>
                    </div>

                    {{-- Crons sidebar (260px) --}}
                    <div style="width:260px;flex-shrink:0;display:flex;flex-direction:column;background:#1a1a1a;">
                        <div style="padding:14px 16px;border-bottom:1px solid #2e2e2e;font-size:13px;font-weight:600;color:#fff;">Agendamentos</div>
                        <div style="flex:1;overflow-y:auto;padding:12px;display:flex;flex-direction:column;gap:8px;">
                            <template x-for="cron in coworkCrons" :key="cron.id">
                                <div :style="!cron.active ? 'opacity:.5;' : ''"
                                     style="padding:12px 14px;background:#222;border:1px solid #2e2e2e;border-radius:8px;position:relative;">
                                    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:4px;">
                                        <span x-text="cron.name" style="font-size:13px;font-weight:600;color:#fff;"></span>
                                        <span :style="`background:${cron.active?'#ff8800':'#555'};`"
                                              style="width:7px;height:7px;border-radius:50%;display:inline-block;"></span>
                                    </div>
                                    <div x-text="cron.schedule" style="font-family:'JetBrains Mono',monospace;font-size:11px;color:#3b82f6;margin-bottom:4px;"></div>
                                    <div style="font-size:12px;color:#999;" x-text="'próximo: '+cron.next"></div>
                                    <div style="font-size:11px;color:#ff8800;margin-top:2px;font-weight:600;" x-text="'→ '+cron.agent"></div>
                                </div>
                            </template>
                            <button style="padding:10px;background:none;border:1px dashed #2e2e2e;border-radius:8px;color:#555;font-size:12px;cursor:pointer;display:flex;align-items:center;gap:6px;justify-content:center;font-family:'Inter',sans-serif;">
                                <i data-lucide="plus" style="width:12px;height:12px;stroke:#555;"></i> Novo agendamento
                            </button>
                        </div>
                    </div>
                </div>

                {{-- ===== TAB: LOGS ===== --}}
                <div x-show="tab==='logs'" style="display:flex;flex-direction:column;height:100%;display:none;">

                    {{-- Toolbar --}}
                    <div style="padding:10px 20px;border-bottom:1px solid #2e2e2e;background:#1a1a1a;display:flex;gap:8px;align-items:center;flex-shrink:0;">
                        <i data-lucide="filter" style="stroke:#555;width:13px;height:13px;flex-shrink:0;"></i>
                        <template x-for="f in logFilters" :key="f.key">
                            <button @click="logFilter=f.key"
                                :style="logFilter===f.key ? 'background:rgba(255,136,0,.12);border-color:#ff8800;color:#ff8800;' : 'background:transparent;border-color:#2e2e2e;color:#999;'"
                                style="padding:4px 10px;font-size:12px;font-weight:500;cursor:pointer;border-radius:5px;border:1px solid;transition:all .15s;font-family:'Inter',sans-serif;"
                                x-text="f.label"></button>
                        </template>
                        <div style="flex:1;"></div>
                        <span x-show="logConnected" style="display:flex;align-items:center;gap:6px;font-size:11px;color:#22c55e;">
                            <span style="width:6px;height:6px;background:#22c55e;border-radius:50%;animation:pulse 1.2s ease infinite;display:inline-block;"></span>
                            Ao vivo
                        </span>
                        <span x-show="!logConnected" style="font-size:11px;color:#555;">Desconectado</span>
                        <span x-text="logEventsFilt.length+' eventos'" style="font-size:11px;color:#555;font-family:'JetBrains Mono',monospace;"></span>
                        <button @click="clearLogEvents()" style="font-size:11px;color:#555;background:none;border:none;cursor:pointer;font-family:'Inter',sans-serif;">Limpar</button>
                    </div>

                    {{-- Timeline --}}
                    <div x-ref="logArea" style="flex:1;overflow-y:auto;padding:16px 20px;">
                        <template x-if="logEventsFilt.length === 0">
                            <div style="display:flex;align-items:center;justify-content:center;padding:64px 0;">
                                <p style="color:#555;font-size:13px;">Nenhum evento ainda. Aguardando atividade...</p>
                            </div>
                        </template>
                        <div style="position:relative;padding-left:20px;border-left:2px solid #2e2e2e;">
                            <template x-for="(log, i) in logEventsFilt" :key="i">
                                <div style="position:relative;margin-bottom:12px;">
                                    <div :style="`background:${logLevelColor(log.level||log.type)};`"
                                         style="position:absolute;left:-25px;top:10px;width:8px;height:8px;border-radius:50%;border:2px solid #0f0f0f;"></div>
                                    <div :style="`border-color:${(log.level==='ERROR'||log.type==='tool_error')?'rgba(239,68,68,.27)':(log.level==='WARN')?'rgba(245,158,11,.2)':'#2e2e2e'};`"
                                         style="padding:10px 14px;background:#1a1a1a;border:1px solid;border-radius:8px;">
                                        <div style="display:flex;gap:12px;align-items:center;margin-bottom:4px;flex-wrap:wrap;">
                                            <span x-text="formatTime(log.timestamp||log.time)" style="font-family:'JetBrains Mono',monospace;font-size:11px;color:#555;"></span>
                                            <span :style="`color:${logLevelColor(log.level||log.type)};`"
                                                  x-text="log.level||log.type" style="font-size:11px;font-weight:700;"></span>
                                            <span x-show="log.agent||log.tool_name"
                                                  x-text="log.agent||log.tool_name" style="font-size:11px;font-weight:600;color:#ff8800;"></span>
                                            <span x-show="log.env" x-text="'env:'+log.env" style="font-size:11px;color:#555;"></span>
                                            <span x-show="log.duration_ms" x-text="log.duration_ms+'ms'" style="font-size:11px;color:#555;margin-left:auto;"></span>
                                        </div>
                                        <div x-text="log.msg||log.message" style="font-size:13px;color:#fff;"></div>
                                        <template x-if="log.error">
                                            <p x-text="log.error" style="color:#ef4444;font-size:11px;margin:4px 0 0;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;font-family:'JetBrains Mono',monospace;"></p>
                                        </template>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>

            </div>{{-- end content --}}
        </div>{{-- end main --}}
    </div>{{-- end drawer panel --}}
</div>

<style>
    [x-cloak] { display: none !important; }

    @keyframes pulse    { 0%,100%{opacity:1}   50%{opacity:.3} }
    @keyframes spin     { to{transform:rotate(360deg)} }
    @keyframes vee-call-fade { from{opacity:0;transform:scale(.92)} to{opacity:1;transform:scale(1)} }
    @keyframes vee-transcript-in { from{opacity:0;transform:translateY(6px)} to{opacity:1;transform:translateY(0)} }

    /* Ring animations — listening */
    @keyframes ring1-listen { 0%,100%{transform:scale(1);opacity:.4}  50%{transform:scale(1.08);opacity:.25} }
    @keyframes ring2-listen { 0%,100%{transform:scale(1);opacity:.2}  50%{transform:scale(1.1); opacity:.1}  }
    @keyframes ring3-listen { 0%,100%{transform:scale(1);opacity:.1}  50%{transform:scale(1.12);opacity:.04} }
    @keyframes ring4-listen { 0%,100%{transform:scale(1);opacity:.05} 50%{transform:scale(1.14);opacity:.02} }
    /* Ring animations — speaking */
    @keyframes ring1-speak  { 0%,100%{transform:scale(1);opacity:.8}  50%{transform:scale(1.35);opacity:.55} }
    @keyframes ring2-speak  { 0%,100%{transform:scale(1);opacity:.55} 50%{transform:scale(1.48);opacity:.3}  }
    @keyframes ring3-speak  { 0%,100%{transform:scale(1);opacity:.3}  50%{transform:scale(1.6); opacity:.12} }
    @keyframes ring4-speak  { 0%,100%{transform:scale(1);opacity:.15} 50%{transform:scale(1.75);opacity:.05} }
    /* Ring animations — thinking */
    @keyframes ring1-think  { 0%,100%{transform:scale(1);opacity:.7}  50%{transform:scale(1.18);opacity:.5}  }
    @keyframes ring2-think  { 0%,100%{transform:scale(1);opacity:.45} 50%{transform:scale(1.22);opacity:.25} }
    @keyframes ring3-think  { 0%,100%{transform:scale(1);opacity:.25} 50%{transform:scale(1.28);opacity:.1}  }
    @keyframes ring4-think  { 0%,100%{transform:scale(1);opacity:.12} 50%{transform:scale(1.35);opacity:.04} }

    /* Sidebar nav icons */
    .vee-sidebar {
        --vee-icon-size: 18px;
        --vee-icon-stroke: 2;
        width: 56px; flex-shrink: 0;
        background: #1a1a1a; border-right: 1px solid #2e2e2e;
        display: flex; flex-direction: column; align-items: center;
    }
    .vee-sidebar-logo-wrap {
        width: 100%; padding: 14px 0;
        display: flex; justify-content: center;
        border-bottom: 1px solid #2e2e2e;
    }
    .vee-logo-icon {
        width: 32px; height: 32px; border-radius: 8px; background: #ff8800;
        display: flex; align-items: center; justify-content: center;
    }
    .vee-nav {
        flex: 1; padding: 10px 0;
        display: flex; flex-direction: column; gap: 4px;
        width: 100%; align-items: center;
    }
    .vee-nav-btn {
        width: 40px; height: 40px; border-radius: 8px;
        display: flex; align-items: center; justify-content: center;
        border: none; cursor: pointer; transition: all .15s; background: transparent;
    }
    .vee-nav-btn--active  { background: #ff8800 !important; color: #fff !important; }
    .vee-nav-btn--inactive { background: transparent !important; color: #999 !important; }
    .vee-nav-btn--inactive:hover { color: #fff !important; }
    .vee-sidebar-bottom {
        padding: 10px 0 14px; border-top: 1px solid #2e2e2e;
        display: flex; flex-direction: column; align-items: center; gap: 0; width: 100%;
    }
    .vee-call-btn {
        width: 40px; height: 40px; border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        border: 1px solid; cursor: pointer; transition: all .2s;
    }
    .vee-call-btn--active  { background: #ff8800 !important; border-color: rgba(255,136,0,.35) !important; box-shadow: 0 0 14px rgba(255,136,0,.55) !important; }
    .vee-call-btn--inactive { background: rgba(255,136,0,.12) !important; border-color: rgba(255,136,0,.35) !important; }

    .vee-sidebar [data-lucide] {
        width: var(--vee-icon-size);
        height: var(--vee-icon-size);
        stroke-width: var(--vee-icon-stroke);
        flex-shrink: 0;
    }
    .vee-nav-btn--active [data-lucide]          { stroke: #fff; }
    .vee-nav-btn--inactive [data-lucide]        { stroke: #999; }
    .vee-nav-btn--inactive:hover [data-lucide]  { stroke: #fff; }

    /* Call panel */
    .vee-call-panel {
        width: 200px; flex-shrink: 0;
        border-right: 1px solid #2e2e2e; background: #1a1a1a;
        display: flex; flex-direction: column;
    }
    .vee-call-header {
        padding: 12px 14px; border-bottom: 1px solid #2e2e2e;
        display: flex; justify-content: space-between; align-items: center;
    }
    .vee-call-dot-pulse {
        width: 7px; height: 7px; border-radius: 50%; background: #ff8800;
        display: inline-block; animation: pulse 1.2s ease infinite;
    }
    .vee-rings-wrap {
        display: flex; justify-content: center; align-items: center;
        padding: 24px 0 16px; position: relative; height: 148px;
    }
    .vee-ring {
        position: absolute; border-radius: 50%; border: 1.5px solid #ff8800;
    }
    .vee-ring-1 { width: 56px;  height: 56px;  }
    .vee-ring-2 { width: 76px;  height: 76px;  }
    .vee-ring-3 { width: 98px;  height: 98px;  }
    .vee-ring-4 { width: 122px; height: 122px; }
    /* listening */
    .vee-ring-1.vee-ring--listening { animation: ring1-listen 2.0s ease-in-out 0.00s infinite; }
    .vee-ring-2.vee-ring--listening { animation: ring2-listen 2.0s ease-in-out 0.12s infinite; }
    .vee-ring-3.vee-ring--listening { animation: ring3-listen 2.0s ease-in-out 0.26s infinite; }
    .vee-ring-4.vee-ring--listening { animation: ring4-listen 2.0s ease-in-out 0.40s infinite; }
    /* speaking */
    .vee-ring-1.vee-ring--speaking  { animation: ring1-speak 1.0s ease-in-out 0.00s infinite; }
    .vee-ring-2.vee-ring--speaking  { animation: ring2-speak 1.35s ease-in-out 0.12s infinite; }
    .vee-ring-3.vee-ring--speaking  { animation: ring3-speak 1.65s ease-in-out 0.26s infinite; }
    .vee-ring-4.vee-ring--speaking  { animation: ring4-speak 2.0s ease-in-out 0.40s infinite; }
    /* thinking */
    .vee-ring-1.vee-ring--thinking  { animation: ring1-think 1.0s ease-in-out 0.00s infinite; }
    .vee-ring-2.vee-ring--thinking  { animation: ring2-think 1.35s ease-in-out 0.12s infinite; }
    .vee-ring-3.vee-ring--thinking  { animation: ring3-think 1.65s ease-in-out 0.26s infinite; }
    .vee-ring-4.vee-ring--thinking  { animation: ring4-think 2.0s ease-in-out 0.40s infinite; }
    .vee-avatar-center {
        width: 44px; height: 44px; border-radius: 50%; position: relative; z-index: 2;
        background: linear-gradient(135deg, #ff8800, #cc6600);
        display: flex; align-items: center; justify-content: center;
        box-shadow: 0 0 20px rgba(255,136,0,.45);
    }

    /* Badges */
    .vee-badge {
        display: inline-flex; align-items: center; gap: 4px;
        padding: 2px 8px; border-radius: 4px; font-size: 11px; font-weight: 600;
    }
    .vee-badge--accent  { background: rgba(255,136,0,.12);  color: #ff8800; border: 1px solid rgba(255,136,0,.27);  }
    .vee-badge--success { background: rgba(34,197,94,.12);  color: #22c55e; border: 1px solid rgba(34,197,94,.27);  }
    .vee-badge--muted   { background: rgba(85,85,85,.12);   color: #555;    border: 1px solid rgba(85,85,85,.27);   }

    /* Msg content prose */
    .vee-msg-content h1, .vee-msg-content h2, .vee-msg-content h3 { color:#fff; margin-top:.75rem; margin-bottom:.25rem; }
    .vee-msg-content p  { margin-bottom:.5rem; }
    .vee-msg-content code { color:#ff8800; background:#1f1f1f; padding:.1rem .3rem; border-radius:4px; }
    .vee-msg-content pre { background:#111; border:1px solid #333; border-radius:8px; padding:.75rem; overflow-x:auto; }
    .vee-msg-content pre code { color:#e2e8f0; background:transparent; padding:0; }
    .vee-msg-content ul, .vee-msg-content ol { padding-left:1.25rem; margin-bottom:.5rem; }
    .vee-msg-content li  { margin-bottom:.125rem; }
    .vee-msg-content a   { color:#ff8800; }
    .vee-msg-content blockquote { border-left:3px solid #ff8800; padding-left:.75rem; color:#aaa; }
    .vee-msg-content table { width:100%; border-collapse:collapse; margin-bottom:.5rem; }
    .vee-msg-content th, .vee-msg-content td { border:1px solid #333; padding:.4rem .75rem; font-size:.8rem; }
    .vee-msg-content th  { background:#1a1a1a; color:#ff8800; }

    /* Scrollbar */
    .vee-sidebar ::-webkit-scrollbar,
    .vee-call-panel ::-webkit-scrollbar { width:4px; }
    ::-webkit-scrollbar-track { background:transparent; }
    ::-webkit-scrollbar-thumb { background:#2e2e2e; border-radius:4px; }
</style>

<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('veeDrawer', () => ({
        open: false,

        // ---- UI state ----
        tab: 'plan',
        orchView: 'A',
        activeAgentId: 0,

        // ---- call state ----
        callOpen: false,
        callMuted: false,
        callMode: 'listening',
        callTranscript: [
            { who:'user', text:'Quero refatorar o módulo de pagamento.' },
            { who:'vee',  text:'Entendido. Já acionei o Code Analyst. Qual sua maior preocupação: performance ou escalabilidade?' },
        ],
        _callTimer: null,

        // ---- chat / session state ----
        currentSession: null,
        messages: [],
        isStreaming: false,
        streamController: null,
        sessions: [],
        starredSessions: [],
        inputText: '',

        // ---- log state ----
        logEvents: [],
        logEventSource: null,
        logConnected: false,
        logFilter: 'all',
        logFilters: [
            { key:'all',  label:'Todos' },
            { key:'tool', label:'Tools' },
            { key:'n8n',  label:'n8n'   },
            { key:'ssh',  label:'SSH'   },
            { key:'db',   label:'DB'    },
        ],

        // ---- config ----
        agentUrl: window.VEE_AGENT_URL   || '',
        agentToken: window.VEE_AGENT_TOKEN || '',

        // ---- static demo data ----
        tabLabels: { plan:'Planejamento', orch:'Orquestramento', cowork:'Cowork', logs:'Logs' },

        orchAgents: [
            { id:0, name:'Code Analyst', env:'local',      status:'done',    task:'Deps mapeadas ✓' },
            { id:1, name:'Architect',    env:'local',      status:'working', task:'Propondo arquitetura...' },
            { id:2, name:'DB Agent',     env:'prod/db',    status:'working', task:'Preparando migration...' },
            { id:3, name:'Deployer',     env:'containers', status:'idle',    task:'Aguardando' },
            { id:4, name:'Vault Agent',  env:'vault',      status:'done',    task:'Secrets validados ✓' },
            { id:5, name:'Obsidian',     env:'obsidian',   status:'working', task:'Documentando...' },
        ],
        orchFeed: [
            { id:0, from:'Code Analyst', to:'Vee',     msg:'47 dependências mapeadas. 3 contextos: billing, reconciliation, notifications.', time:'14:34', status:'done' },
            { id:1, from:'Vault Agent',  to:'Vee',     msg:'12 variáveis identificadas. Política de rotação validada. Sem conflitos.',       time:'14:33', status:'done' },
            { id:2, from:'Vee', to:'Architect',        msg:'Baseado em deps.json, proponha separação em micro-serviços. Defina contratos.',   time:'14:34', status:'working', pct:62 },
            { id:3, from:'Vee', to:'DB Agent',         msg:'Prepare scripts de schema migration por contexto. Não execute ainda.',            time:'14:34', status:'working', pct:28 },
            { id:4, from:'Obsidian', to:'Vee',         msg:'Criando notas de decisão arquitetural. ADR-042 em progresso.',                   time:'14:34', status:'working', pct:40 },
        ],
        orchPipeline: [
            { agent:'Code Analyst', label:'Análise de código',       status:'done',    out:'deps.json' },
            { agent:'Architect',    label:'Proposta de arquitetura', status:'working', pct:62 },
            { agent:'DB Agent',     label:'Schema migration',        status:'waiting' },
            { agent:'Deployer',     label:'Build & Deploy',          status:'waiting' },
        ],
        orchGraphNodes: [
            { label:'Vee',          x:46, y:42, main:true },
            { label:'Code\nAnalyst',x:15, y:12, status:'done' },
            { label:'Architect',    x:77, y:12, status:'working' },
            { label:'DB Agent',     x:10, y:72, status:'working' },
            { label:'Deployer',     x:82, y:72, status:'idle' },
            { label:'Obsidian',     x:46, y:82, status:'working' },
            { label:'Vault',        x:78, y:47, status:'done' },
        ],
        orchGraphEdges: [[0,1],[0,2],[0,3],[0,4],[0,5],[0,6],[1,2],[1,3]],

        coworkTasks: [
            { id:1, title:'Gerar diagrama ER do módulo payment',    agent:'DB Agent',     status:'done',    pct:100, env:'prod/db',    due:'14:30' },
            { id:2, title:'Análise de dependências',                agent:'Code Analyst', status:'done',    pct:100, env:'local',      due:'14:32' },
            { id:3, title:'Proposta de arquitetura micro-serviços', agent:'Architect',    status:'running', pct:62,  env:'local',      due:'14:45' },
            { id:4, title:'Preparar schema migration scripts',      agent:'DB Agent',     status:'running', pct:28,  env:'prod/db',    due:'15:00' },
            { id:5, title:'Documentar decisões arquiteturais',      agent:'Obsidian',     status:'running', pct:40,  env:'obsidian',   due:'15:10' },
            { id:6, title:'Build e push das imagens Docker',        agent:'Deployer',     status:'pending', pct:0,   env:'containers', due:'15:30' },
            { id:7, title:'Deploy em staging',                      agent:'Deployer',     status:'pending', pct:0,   env:'containers', due:'16:00' },
            { id:8, title:'Smoke tests pós-deploy',                 agent:'Code Analyst', status:'pending', pct:0,   env:'local',      due:'16:15' },
        ],
        coworkCrons: [
            { id:1, name:'secrets-rotation', schedule:'0 2 * * 0',    next:'dom 02:00',    agent:'Vault Agent', active:true  },
            { id:2, name:'db-backup',        schedule:'0 */6 * * *',  next:'em 2h 18min',  agent:'DB Agent',    active:true  },
            { id:3, name:'log-cleanup',      schedule:'0 0 * * *',    next:'amanhã 00:00', agent:'Deployer',    active:false },
            { id:4, name:'obsidian-sync',    schedule:'*/30 * * * *', next:'em 12min',     agent:'Obsidian',    active:true  },
        ],

        // ---- computed ----
        get logEventsFilt() {
            if (this.logFilter === 'all') return this.logEvents;
            return this.logEvents.filter(e => {
                if (this.logFilter === 'tool') return e.tool_name;
                if (this.logFilter === 'n8n')  return e.tool_name && e.tool_name.startsWith('n8n');
                if (this.logFilter === 'ssh')  return e.tool_name && /^(ssh|disk|memory|container|tail|service|restart|inspect)/.test(e.tool_name);
                if (this.logFilter === 'db')   return e.tool_name && e.tool_name.startsWith('db');
                return true;
            });
        },

        get callStatusText() {
            return this.callMode === 'speaking' ? 'Falando...' : this.callMode === 'thinking' ? 'Processando...' : 'Ouvindo...';
        },

        get callStatusColor() {
            return this.callMode === 'speaking' ? '#ff8800' : this.callMode === 'thinking' ? '#f59e0b' : '#888';
        },

        // ---- lifecycle ----
        init() {
            const saved = JSON.parse(localStorage.getItem('vee-ui-state') || '{}');
            if (saved.tab)      this.tab      = saved.tab;
            if (saved.orchView) this.orchView = saved.orchView;

            this.$watch('tab', val => {
                const s = JSON.parse(localStorage.getItem('vee-ui-state') || '{}');
                localStorage.setItem('vee-ui-state', JSON.stringify({ ...s, tab: val }));
            });
            this.$watch('orchView', val => {
                const s = JSON.parse(localStorage.getItem('vee-ui-state') || '{}');
                localStorage.setItem('vee-ui-state', JSON.stringify({ ...s, orchView: val }));
            });
            this.$watch('callOpen', val => {
                if (val) this._startCallCycle();
                else     this._stopCallCycle();
            });
        },

        openDrawer() {
            this.open = true;
            document.body.classList.add('overflow-hidden');
            this.loadSessions();
            this.$nextTick(() => { if (window.lucide) lucide.createIcons(); });
        },

        closeDrawer() {
            if (this.isStreaming && this.streamController) {
                this.streamController.abort();
                this.isStreaming = false;
            }
            this.callOpen = false;
            this.open = false;
            document.body.classList.remove('overflow-hidden');
        },

        switchToLogs() {
            this.tab = 'logs';
            this.startLogFeed();
        },

        toggleCall() {
            this.callOpen = !this.callOpen;
        },

        toggleStar(id) {
            if (this.starredSessions.includes(id)) {
                this.starredSessions = this.starredSessions.filter(x => x !== id);
            } else {
                this.starredSessions.push(id);
            }
        },

        // ---- call simulation ----
        _startCallCycle() {
            this._stopCallCycle();
            const cycle = [
                { mode:'listening', dur:2800 },
                { mode:'thinking',  dur:1100 },
                { mode:'speaking',  dur:3000 },
                { mode:'listening', dur:2400 },
            ];
            let i = 0;
            const tick = () => {
                if (!this.callOpen) return;
                const c = cycle[i % cycle.length];
                this.callMode = c.mode;
                if (c.mode === 'speaking') {
                    this.callTranscript = [...this.callTranscript.slice(-5),
                        { who:'vee', text:'Recomendo começar pelo contexto de cobrança — é o mais isolado.' }];
                    this.$nextTick(() => {
                        const el = this.$refs.callTranscript;
                        if (el) el.scrollTop = el.scrollHeight;
                    });
                }
                if (c.mode === 'listening' && i > 0) {
                    this.callTranscript = [...this.callTranscript.slice(-5),
                        { who:'user', text:'Faz sentido. E o DB Agent já está preparado?' }];
                }
                i++;
                this._callTimer = setTimeout(tick, c.dur);
            };
            this._callTimer = setTimeout(tick, 1800);
        },

        _stopCallCycle() {
            if (this._callTimer) {
                clearTimeout(this._callTimer);
                this._callTimer = null;
            }
            this.callMode = 'listening';
        },

        // ---- sessions ----
        newSession() {
            this.currentSession = null;
            this.messages = [];
        },

        async loadSessions() {
            if (!this.agentUrl) return;
            try {
                const res = await fetch(`${this.agentUrl}/sessions?limit=50`, { headers: this.authHeaders() });
                if (!res.ok) return;
                const data = await res.json();
                this.sessions = data.sessions || [];
            } catch {}
        },

        async loadSession(id) {
            if (!this.agentUrl) return;
            try {
                const res = await fetch(`${this.agentUrl}/sessions/${id}`, { headers: this.authHeaders() });
                if (!res.ok) return;
                const data = await res.json();
                this.currentSession = data.session;
                this.messages = this.normalizeMessages(data.messages || []);
                this.$nextTick(() => this.scrollToBottom());
            } catch {}
        },

        async deleteSession(id) {
            if (!this.agentUrl) return;
            try {
                await fetch(`${this.agentUrl}/sessions/${id}`, { method:'DELETE', headers: this.authHeaders() });
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
                if (m.role === 'user')      msgs.push({ role:'user',      content: m.content || '', toolActivities:[] });
                else if (m.role === 'assistant') msgs.push({ role:'assistant', content: m.content || '', toolActivities:[] });
            }
            return msgs;
        },

        // ---- streaming ----
        async sendMessage() {
            const text = this.inputText.trim();
            if (!text || this.isStreaming) return;

            this.inputText = '';
            this.messages.push({ role:'user', content:text, toolActivities:[] });
            await this.$nextTick();
            this.scrollToBottom();
            await this.streamSSE(text);
        },

        async streamSSE(message) {
            this.isStreaming = true;
            this.streamController = new AbortController();

            const assistantIdx = this.messages.length;
            this.messages.push({ role:'assistant', content:'', toolActivities:[] });

            try {
                const res = await fetch(`${this.agentUrl}/stream`, {
                    method: 'POST',
                    headers: { 'Content-Type':'application/json', ...this.authHeaders() },
                    body: JSON.stringify({
                        message,
                        session_id: this.currentSession?.id ?? undefined,
                        mode: 'chat',
                    }),
                    signal: this.streamController.signal,
                });

                if (!res.ok) {
                    this.messages[assistantIdx].content = `Erro: ${res.status} ${res.statusText}`;
                    this.isStreaming = false;
                    return;
                }

                const reader  = res.body.getReader();
                const decoder = new TextDecoder();
                let buffer = '';

                while (true) {
                    const { done, value } = await reader.read();
                    if (done) break;

                    buffer += decoder.decode(value, { stream:true });
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
                                this.currentSession = { id:evt.session_id, title:'Nova tarefa', mode:evt.mode };
                                break;
                            case 'text':
                                this.messages[assistantIdx].content += evt.content;
                                this.$nextTick(() => this.scrollToBottom());
                                break;
                            case 'tool_start':
                                this.messages[assistantIdx].toolActivities.push({
                                    call_id:evt.call_id, name:evt.name, args:evt.args,
                                    status:'running', result:null, error:null, duration_ms:null,
                                });
                                this.$nextTick(() => this.scrollToBottom());
                                break;
                            case 'tool_result': {
                                const t = this.messages[assistantIdx].toolActivities.find(x => x.call_id === evt.call_id);
                                if (t) { t.status='done'; t.result=evt.result; t.duration_ms=evt.duration_ms; }
                                break;
                            }
                            case 'tool_error': {
                                const t = this.messages[assistantIdx].toolActivities.find(x => x.call_id === evt.call_id);
                                if (t) { t.status='error'; t.error=evt.error; t.duration_ms=evt.duration_ms; }
                                break;
                            }
                            case 'session_titled':
                                if (this.currentSession) {
                                    this.currentSession.title = evt.title;
                                    const s = this.sessions.find(s => s.id === evt.session_id);
                                    if (s) s.title = evt.title; else this.loadSessions();
                                }
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
            if (!this.agentUrl) return;
            const url = `${this.agentUrl}/log/feed`;
            this.logEventSource = new EventSource(
                this.agentToken ? `${url}?token=${encodeURIComponent(this.agentToken)}` : url
            );
            this.logEventSource.onopen   = () => { this.logConnected = true; };
            this.logEventSource.onerror  = () => { this.logConnected = false; };
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

        clearLogEvents() { this.logEvents = []; },

        // ---- helpers ----
        agentStatusColor(status) {
            return status === 'working' ? '#ff8800' : status === 'done' ? '#22c55e' : '#555';
        },

        logLevelColor(level) {
            if (!level) return '#555';
            const l = level.toLowerCase();
            if (l.includes('error')) return '#ef4444';
            if (l.includes('warn'))  return '#f59e0b';
            if (l.includes('success') || l.includes('tool_result')) return '#22c55e';
            if (l.includes('tool_start')) return '#ff8800';
            return '#555';
        },

        authHeaders() {
            return this.agentToken ? { 'Authorization': `Bearer ${this.agentToken}` } : {};
        },

        renderMarkdown(text) {
            if (!text) return '';
            if (window.marked) return window.marked.parse(text, { breaks:true, gfm:true });
            return text.replace(/\n/g, '<br>');
        },

        scrollToBottom() {
            const el = this.$refs.messagesArea;
            if (el) el.scrollTop = el.scrollHeight;
        },

        formatDate(iso) {
            if (!iso) return '';
            const d    = new Date(iso);
            const diff = Date.now() - d;
            if (diff < 60000)   return 'agora';
            if (diff < 3600000) return Math.floor(diff/60000) + 'min';
            if (diff < 86400000) return Math.floor(diff/3600000) + 'h';
            return d.toLocaleDateString('pt-BR', { day:'2-digit', month:'2-digit' });
        },

        formatTime(iso) {
            if (!iso) return '';
            return new Date(iso).toLocaleTimeString('pt-BR', { hour:'2-digit', minute:'2-digit', second:'2-digit' });
        },

        truncate(str, len) {
            if (!str) return '';
            const s = typeof str === 'object' ? JSON.stringify(str) : String(str);
            return s.length > len ? s.slice(0, len) + '…' : s;
        },
    }));
});
</script>
