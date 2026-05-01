{{-- Vee Drawer v3 --}}
<link rel="stylesheet" href="/css/vee-drawer.css">

<style>
@keyframes spin { to { transform: rotate(360deg); } }
</style>
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
        class="fixed inset-0 z-[70] flex overflow-hidden transform vee-drawer-panel"
        style="display:none;"
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
                <button @click="switchToApprovals()" title="Approvals"
                    :class="tab==='approvals' ? 'vee-nav-btn--active' : 'vee-nav-btn--inactive'"
                    class="vee-nav-btn vee-nav-btn--with-dot">
                    <i data-lucide="shield-alert"></i>
                    <span x-show="pendingApprovalsTotal > 0"
                          x-text="pendingApprovalsTotal > 9 ? '9+' : String(pendingApprovalsTotal)"
                          class="vee-nav-pending-dot"></span>
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
                <div class="vee-call-header__group">
                    <span class="vee-call-dot-pulse"></span>
                    <span class="vee-call-header__label">Em chamada</span>
                </div>
                <button @click="toggleCall()" class="vee-call-header__end-btn">
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
            <div class="vee-call-status">
                <div class="vee-call-status__name">Vee</div>
                <div x-text="callStatusText"
                     :style="`font-size:11px;font-weight:500;margin-top:2px;letter-spacing:.3px;color:${callStatusColor};`"></div>
            </div>

            {{-- Transcript --}}
            <div x-ref="callTranscript" class="vee-call-transcript">
                <template x-for="(m, i) in callTranscript" :key="i">
                    <div :style="m.who==='user'
                            ? 'background:rgba(255,136,0,.12);border:1px solid rgba(255,136,0,.35);'
                            : 'background:#222;border:1px solid #2e2e2e;'"
                         class="vee-call-transcript__msg">
                        <div :style="m.who==='user' ? 'color:#ff8800;' : 'color:#555;'"
                             class="vee-call-transcript__who"
                             x-text="m.who==='user'?'Você':'Vee'"></div>
                        <span x-text="m.text"></span>
                    </div>
                </template>
            </div>

            {{-- Mute --}}
            <div class="vee-call-mute">
                <button @click="callMuted=!callMuted"
                    :style="callMuted ? 'background:rgba(239,68,68,.12);border:1px solid rgba(239,68,68,.67);' : 'background:#222;border:1px solid #2e2e2e;'"
                    class="vee-call-mute__btn"
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
        <div class="vee-main">

            {{-- Top Bar (48px) --}}
            <div class="vee-topbar">
                <span x-text="tabLabels[tab]" class="vee-topbar__title"></span>

                {{-- Orch view toggle --}}
                <div x-show="tab==='orch'" class="vee-topbar__view-toggle">
                    <span class="vee-topbar__label">Visualização:</span>
                    <template x-for="[v, l] in [['A','Feed'],['B','Pipeline'],['C','Grafo']]" :key="v">
                        <button @click="orchView=v"
                            :style="orchView===v ? 'background:#ff8800;border-color:#ff8800;color:#fff;' : 'background:transparent;border-color:#2e2e2e;color:#999;'"
                            class="vee-topbar__view-btn"
                            x-text="l"></button>
                    </template>
                </div>
            {{-- ===== TOASTS ===== --}}
            <div style="position:fixed;top:16px;right:72px;z-index:200;display:flex;flex-direction:column;gap:8px;pointer-events:none;">
                <template x-for="toast in orchToasts" :key="toast.id">
                    <div @click="switchToLogs(); dismissToast(toast.id)"
                         :style="`background:${toast.type==='pass'?'rgba(34,197,94,.18)':toast.type==='fail'?'rgba(239,68,68,.18)':'rgba(255,136,0,.18)'};border:1px solid ${toast.type==='pass'?'rgba(34,197,94,.5)':toast.type==='fail'?'rgba(239,68,68,.5)':'rgba(255,136,0,.5)'};`"
                         style="padding:10px 14px;border-radius:6px;cursor:pointer;min-width:220px;max-width:320px;backdrop-filter:blur(8px);pointer-events:all;"
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 translate-x-4"
                         x-transition:enter-end="opacity-100 translate-x-0"
                         x-transition:leave="transition ease-in duration-150"
                         x-transition:leave-start="opacity-100"
                         x-transition:leave-end="opacity-0">
                        <div style="font-size:12px;font-weight:600;color:#fff;" x-text="toast.msg"></div>
                        <div style="font-size:10px;color:#888;margin-top:2px;">Clique para ver nos logs</div>
                    </div>
                </template>
            </div>



                {{-- Env status --}}
                <div class="vee-topbar__env">
                    <span class="vee-topbar__env-dot"></span>
                    <span class="vee-topbar__env-text" x-text="orchAgents.length + ' agente' + (orchAgents.length !== 1 ? 's' : '') + ' ativos'"></span>
                </div>
            </div>

            {{-- Content --}}
            <div class="vee-content">

                {{-- ===== TAB: PLANEJAMENTO ===== --}}
                <div x-show="tab==='plan'" class="flex h-full" style="display:none;">

                    {{-- Conversations Sidebar (220px) --}}
                    <div class="vee-sessions">
                        <div class="vee-sessions__header">
                            <span class="vee-sessions__title">Conversas</span>
                            <button @click="newSession()" class="vee-sessions__new-btn">
                                <i data-lucide="plus" style="stroke:#fff;"></i>
                            </button>
                        </div>
                        <div class="vee-sessions__list">
                            <template x-if="sessions.length === 0">
                                <p class="vee-sessions__empty">Nenhuma conversa ainda</p>
                            </template>
                            <template x-for="sess in sessions" :key="sess.id">
                                <div @click="editingSessionId !== sess.id && loadSession(sess.id)"
                                    :style="currentSession && currentSession.id === sess.id
                                        ? 'background:rgba(255,136,0,.1);border-left-color:#ff8800;'
                                        : 'background:#222;border-left-color:#444;'"
                                    class="vee-sidebar-card">
                                    <div class="vee-sidebar-card__header">
                                        {{-- Título normal --}}
                                        <template x-if="editingSessionId !== sess.id">
                                            <span x-text="sess.title" class="vee-sidebar-card__title"></span>
                                        </template>
                                        {{-- Input de edição --}}
                                        <template x-if="editingSessionId === sess.id">
                                            <input
                                                x-model="editingTitle"
                                                @click.stop
                                                @keydown.enter.stop="renameSession(sess.id)"
                                                @keydown.escape.stop="editingSessionId = null"
                                                @blur="renameSession(sess.id)"
                                                x-init="$nextTick(() => $el.focus())"
                                                style="flex:1;background:#333;border:1px solid #ff8800;border-radius:4px;padding:2px 6px;color:#fff;font-size:13px;font-weight:600;outline:none;min-width:0;font-family:'Inter',sans-serif;"
                                            >
                                        </template>
                                        <span x-text="formatDate(sess.updated_at)" class="vee-sidebar-card__date"></span>
                                    </div>
                                    <div class="vee-sidebar-card__preview"
                                         x-text="sess.preview || '...'"></div>
                                    <div class="vee-sidebar-card__actions">
                                        {{-- Favorito --}}
                                        <button @click.stop="toggleStar(sess.id)"
                                            :style="starredSessions.includes(sess.id) ? 'color:#f59e0b;' : 'color:#555;'"
                                            class="vee-icon-action-btn" title="Favoritar">
                                            <svg width="12" height="12" viewBox="0 0 24 24" :fill="starredSessions.includes(sess.id) ? '#f59e0b' : 'none'" :stroke="starredSessions.includes(sess.id) ? '#f59e0b' : '#555'" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>
                                        </button>
                                        {{-- Editar título --}}
                                        <button @click.stop="editingSessionId = sess.id; editingTitle = sess.title"
                                            class="vee-icon-action-btn" title="Renomear">
                                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="#555" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                                        </button>
                                        {{-- Deletar --}}
                                        <button @click.stop="deleteSession(sess.id)"
                                            class="vee-icon-action-btn" title="Deletar">
                                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="#555" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"></path></svg>
                                        </button>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>

                    {{-- Chat area --}}
                    <div class="vee-chat">
                        {{-- Chat header --}}
                        <div class="vee-chat-header">
                            <div>
                                <div class="vee-chat-header__title"
                                     x-text="currentSession ? currentSession.title : 'Nova conversa'"></div>
                                <div class="vee-chat-header__count"
                                     x-text="messages.length + ' mensagem' + (messages.length !== 1 ? 's' : '')"></div>
                            </div>
                            <div class="vee-chat-header__badges">
                                <span class="vee-tag">local</span>
                                <span class="vee-tag">vault</span>
                            </div>
                        </div>

                        {{-- Messages --}}
                        <div x-ref="messagesArea" class="vee-messages">

                            {{-- Empty state --}}
                            <template x-if="messages.length === 0">
                                <div class="vee-empty-state">
                                    <div class="vee-empty-state__icon">
                                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#ff8800" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M4 6l8 12 8-12"/>
                                        </svg>
                                    </div>
                                    <p class="vee-empty-state__title">Olá! Sou a Vee.</p>
                                    <p class="vee-empty-state__sub">Aqui para planejar, discutir e coordenar agentes.</p>
                                </div>
                            </template>

                            {{-- Messages list --}}
                            <template x-for="(msg, idx) in messages" :key="idx">
                                <div>
                                    {{-- User message --}}
                                    <template x-if="msg.role === 'user'">
                                        <div class="vee-msg-user">
                                            <div class="vee-msg-user__bubble">
                                                <template x-if="editingMessageId !== null && msg.id !== null && Number(editingMessageId) === Number(msg.id)">
                                                    <div class="vee-msg-edit-wrap">
                                                        <textarea
                                                            x-model="editingMessageText"
                                                            class="vee-msg-edit-textarea"
                                                            rows="3"
                                                            maxlength="4000"
                                                            :disabled="isStreaming"
                                                            @keydown.enter="if (!$event.shiftKey) { $event.preventDefault(); if (!isStreaming) submitMessageEdit(msg); }"
                                                        ></textarea>
                                                        <div class="vee-msg-edit-actions">
                                                            <button
                                                                type="button"
                                                                class="vee-msg-edit-btn vee-msg-edit-btn--cancel"
                                                                @click="cancelMessageEdit()"
                                                                :disabled="isStreaming"
                                                            >
                                                                Cancelar
                                                            </button>
                                                            <button
                                                                type="button"
                                                                class="vee-msg-edit-btn vee-msg-edit-btn--save"
                                                                @click="submitMessageEdit(msg)"
                                                                :disabled="isStreaming || !editingMessageText.trim()"
                                                            >
                                                                Salvar
                                                            </button>
                                                        </div>
                                                    </div>
                                                </template>
                                                <template x-if="editingMessageId === null || msg.id === null || Number(editingMessageId) !== Number(msg.id)">
                                                    <div>
                                                        <p x-text="msg.content" class="vee-msg-user__text"></p>
                                                        <div class="vee-msg-user__actions" x-show="msg.can_edit">
                                                            <button
                                                                type="button"
                                                                class="vee-msg-user__edit-btn"
                                                                @click="startMessageEdit(msg)"
                                                                title="Editar mensagem"
                                                                aria-label="Editar mensagem"
                                                                :disabled="isStreaming"
                                                            >
                                                                <i data-lucide="pencil" class="vee-icon-btn"></i>
                                                            </button>
                                                        </div>
                                                    </div>
                                                </template>
                                            </div>
                                        </div>
                                    </template>

                                    {{-- Assistant message --}}
                                    <template x-if="msg.role === 'assistant'">
                                        <div class="vee-msg-assistant">
                                            <div class="vee-msg-avatar">
                                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#ff8800" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                                    <path d="M4 6l8 12 8-12"/>
                                                </svg>
                                            </div>
                                            <div class="vee-msg-body">
                                                {{-- Tool activities --}}
                                                <template x-if="msg.toolActivities && msg.toolActivities.length > 0">
                                                    <div class="vee-tool-activities">
                                                        <template x-for="(tool, ti) in msg.toolActivities" :key="ti">
                                                            <div :style="tool.status==='running' ? 'border-color:rgba(255,136,0,.5);background:rgba(255,136,0,.05);' : tool.status==='done' ? 'border-color:rgba(34,197,94,.4);background:rgba(34,197,94,.05);' : 'border-color:rgba(239,68,68,.4);background:rgba(239,68,68,.05);'"
                                                                 class="vee-tool-row">
                                                                <div class="vee-tool-row__inner">
                                                                    <span x-show="tool.status === 'running'"
                                                                          class="vee-tool-spinner"></span>
                                                                    <svg x-show="tool.status === 'done'" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="#22c55e" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="vee-tool-row__icon"><polyline points="20 6 9 17 4 12"></polyline></svg>
                                                                    <svg x-show="tool.status === 'error'" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="#ef4444" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="vee-tool-row__icon"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                                                                    <span class="vee-tool-row__name" x-text="tool.name"></span>
                                                                    <span x-show="tool.duration_ms"
                                                                          x-text="tool.duration_ms + 'ms'"
                                                                          class="vee-tool-row__duration"></span>
                                                                </div>
                                                                <template x-if="tool.status === 'error' && tool.error">
                                                                    <p x-text="tool.error" class="vee-tool-row__error"></p>
                                                                </template>
                                                                <template x-if="tool.approval">
                                                                    <div class="vee-approval-card" style="margin-top:8px;">
                                                                        <div class="vee-approval-card__head">
                                                                            <div class="vee-approval-card__title-wrap">
                                                                                <div class="vee-approval-card__title"
                                                                                     x-text="tool.approval.action_name || tool.approval.tool_name || tool.name || 'Acao protegida'"></div>
                                                                                <div class="vee-approval-card__meta">
                                                                                    <span class="vee-approval-card__id"
                                                                                          x-text="`id: ${tool.approval.approval_id || 'n/a'}`"></span>
                                                                                    <span class="vee-approval-card__tool"
                                                                                          x-text="`tool: ${tool.approval.tool_name || tool.name || 'n/a'}`"></span>
                                                                                    <span class="vee-approval-card__time"
                                                                                          x-text="formatTime(tool.approval.created_at)"></span>
                                                                                </div>
                                                                            </div>
                                                                            <span class="vee-approval-card__status"
                                                                                  :class="approvalStatusBadgeClass(tool.approval.status)"
                                                                                  x-text="approvalStatusLabel(tool.approval.status)"></span>
                                                                        </div>

                                                                        <template x-if="tool.approval.summary">
                                                                            <p class="vee-approval-card__summary" x-text="tool.approval.summary"></p>
                                                                        </template>

                                                                        <template x-if="tool.approval.request_payload">
                                                                            <pre class="vee-approval-card__payload"
                                                                                 x-text="truncate(tool.approval.request_payload, 420)"></pre>
                                                                        </template>

                                                                        <template x-if="String(tool.approval.status || '').toLowerCase() === 'pending'">
                                                                            <div class="vee-approval-card__actions">
                                                                                <input
                                                                                    type="text"
                                                                                    placeholder="Motivo da rejeicao (opcional)"
                                                                                    class="vee-approval-card__reason"
                                                                                    x-model="approvalRejectReasonById[tool.approval.approval_id]"
                                                                                />
                                                                                <button
                                                                                    @click="approvePendingApproval(tool.approval.approval_id)"
                                                                                    :disabled="isApprovalBusy(tool.approval.approval_id)"
                                                                                    class="vee-approval-btn vee-approval-btn--approve"
                                                                                    x-text="isApprovalBusy(tool.approval.approval_id) ? 'Enviando...' : 'Aprovar'"
                                                                                ></button>
                                                                                <button
                                                                                    @click="rejectPendingApproval(tool.approval.approval_id)"
                                                                                    :disabled="isApprovalBusy(tool.approval.approval_id)"
                                                                                    class="vee-approval-btn vee-approval-btn--reject"
                                                                                    x-text="isApprovalBusy(tool.approval.approval_id) ? 'Enviando...' : 'Rejeitar'"
                                                                                ></button>
                                                                            </div>
                                                                        </template>

                                                                        <template x-if="String(tool.approval.status || '').toLowerCase() === 'approved'">
                                                                            <p class="vee-approval-card__summary">Aprovacao enviada. Aguardando execucao da acao.</p>
                                                                        </template>

                                                                        <template x-if="String(tool.approval.status || '').toLowerCase() === 'rejected'">
                                                                            <p class="vee-approval-card__summary"
                                                                               x-text="tool.approval.rejection_reason ? `Solicitacao rejeitada: ${tool.approval.rejection_reason}` : 'Solicitacao rejeitada.'"></p>
                                                                        </template>

                                                                        <template x-if="String(tool.approval.status || '').toLowerCase() === 'executed'">
                                                                            <p class="vee-approval-card__summary">Acao executada apos aprovacao.</p>
                                                                        </template>
                                                                    </div>
                                                                </template>
                                                            </div>
                                                        </template>
                                                    </div>
                                                </template>

                                                {{-- Text content --}}
                                                <template x-if="msg.content">
                                                    <div class="vee-msg-bubble vee-msg-content"
                                                         x-html="renderMarkdown(msg.content)">
                                                    </div>
                                                </template>

                                                {{-- Streaming cursor --}}
                                                <template x-if="isStreaming && idx === messages.length - 1 && !msg.content">
                                                    <div class="vee-msg-bubble">
                                                        <span class="vee-cursor"></span>
                                                    </div>
                                                </template>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </template>

                            {{-- Streaming placeholder --}}
                            <template x-if="isStreaming && (messages.length === 0 || messages[messages.length-1].role === 'user')">
                                <div class="vee-msg-assistant">
                                    <div class="vee-msg-avatar">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#ff8800" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M4 6l8 12 8-12"/>
                                        </svg>
                                    </div>
                                    <div class="vee-msg-bubble">
                                        <span class="vee-cursor"></span>
                                    </div>
                                </div>
                            </template>
                        </div>

                        {{-- Input --}}
                        <div class="vee-input-wrap">
                            <div class="vee-input-box">
                                <textarea
                                    x-model="inputText"
                                    @keydown.enter="if (!$event.shiftKey) { $event.preventDefault(); if (!isStreaming) sendMessage(); }"
                                    :disabled="isStreaming"
                                    placeholder="Escreva para a Vee..."
                                    rows="1"
                                    class="vee-input-textarea"
                                    @input="$el.style.height='auto'; $el.style.height=Math.min($el.scrollHeight,180)+'px'"
                                ></textarea>
                                <div class="vee-input-actions">
                                    <div class="vee-input-left">
                                        <button class="vee-input-icon-btn">
                                            <i data-lucide="mic"></i>
                                        </button>
                                        <button class="vee-input-icon-btn">
                                            <i data-lucide="paperclip"></i>
                                        </button>
                                    </div>
                                    <button @click="sendMessage()"
                                        :disabled="isStreaming || !inputText.trim()"
                                        class="vee-send-btn"
                                        :style="(isStreaming || !inputText.trim()) ? 'opacity:.4;cursor:not-allowed;' : ''">
                                        <i x-show="!isStreaming" data-lucide="send" class="vee-send-btn__icon"></i>
                                        <span x-show="isStreaming" class="vee-send-spinner"></span>
                                        Enviar
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ===== TAB: ORQUESTRAMENTO ===== --}}
                <div x-show="tab==='orch'" class="flex h-full" style="display:none;">

                    {{-- Agents sidebar --}}
                    <div class="vee-agents">
                        <div class="vee-sidebar-header">Agentes</div>
                        <div class="vee-agents__list">
                            <template x-for="agent in orchAgents" :key="agent.id">
                                <div @click="activeAgentId=agent.id"
                                    :style="activeAgentId===agent.id ? 'background:rgba(255,136,0,.1);border-left-color:#ff8800;' : 'background:#222;border-left-color:#444;'"
                                    class="vee-sidebar-card">
                                    <div class="vee-agent-card__header">
                                        <span x-text="agent.name" class="vee-sidebar-card__name"></span>
                                        <span :style="`background:${agentStatusColor(agent.status)};`" class="vee-status-dot"></span>
                                    </div>
                                    <div x-text="agent.env" class="vee-agent-card__env"></div>
                                    <div x-text="agent.task" :style="`color:${agentStatusColor(agent.status)};`" class="vee-agent-card__task"></div>
                                </div>
                            </template>
                        </div>
                    </div>

                    {{-- Feed view --}}
                    <div x-show="orchView==='A'" style="flex:1;display:flex;flex-direction:column;overflow:hidden;display:none;">

                        {{-- Header com badges dinâmicos --}}
                        <div class="vee-section-header">
                            <span class="vee-section-header__title">Feed de atividade</span>
                            <div class="vee-spacer"></div>
                            <template x-if="activeAgentId === 'test-runner'">
                                <span class="vee-badge vee-badge--accent"
                                      x-text="orchTestGroups.filter(function(g){return g.status!=='skip';}).length + ' grupos'"></span>
                            </template>
                            <template x-if="activeAgentId === 'test-runner'">
                                <span class="vee-badge vee-badge--muted"
                                      x-text="orchTestGroups.filter(function(g){return g.status==='skip';}).length + ' skip'"></span>
                            </template>
                            <template x-if="activeAgentId !== 'test-runner'">
                                <span class="vee-badge vee-badge--accent"
                                      x-text="orchAgents.filter(function(a){return a.status==='active'||a.status==='working';}).length + ' ativos'"></span>
                            </template>
                        </div>

                        {{-- Cards de grupos --}}
                        <div class="vee-feed-list">
                            {{-- Skeleton enquanto carrega --}}
                            <template x-if="orchStatusLoading">
                                <div style="padding:24px;text-align:center;color:#555;font-size:13px;">
                                    <div style="display:inline-flex;align-items:center;gap:8px;">
                                        <span style="display:inline-block;width:14px;height:14px;border:2px solid #ff8800;border-top-color:transparent;border-radius:50%;animation:spin 0.8s linear infinite;"></span>
                                        Carregando grupos...
                                    </div>
                                </div>
                            </template>
                            <template x-if="!orchStatusLoading && orchStatusError">
                                <div style="padding:16px 24px;color:#ef4444;font-size:12px;" x-text="'Erro ao carregar: ' + orchStatusError"></div>
                            </template>
                            <template x-if="!orchStatusLoading">
                            <template x-for="item in orchFeed" :key="item.id">
                                <div :style="`border-left-color:${item.status==='pass'||item.status==='done'?'#22c55e':item.status==='running'||item.status==='working'?'#ff8800':item.status==='fail'?'#ef4444':item.isSkipped?'#555':'#444'};`"
                                     class="vee-content-card">

                                    {{-- Header: rota + hora + botão Rodar --}}
                                    <div class="vee-content-card__header">
                                        <span class="vee-feed-card__route" x-text="item.from + ' → ' + item.to"></span>
                                        <div style="display:flex;align-items:center;gap:8px;">
                                            <span class="vee-feed-card__time" x-text="item.time"></span>
                                            <template x-if="!item.isSkipped && activeAgentId === 'test-runner'">
                                                <button
                                                    @click.stop="runTestGroup(item.groupId)"
                                                    :disabled="!!orchRunning[item.groupId]"
                                                    :style="orchRunning[item.groupId]
                                                        ? 'opacity:.5;cursor:not-allowed;background:rgba(255,136,0,.08);border:1px solid rgba(255,136,0,.25);color:#ff8800;'
                                                        : 'background:rgba(255,136,0,.14);border:1px solid rgba(255,136,0,.4);color:#ff8800;cursor:pointer;'"
                                                    style="padding:2px 10px;border-radius:4px;font-size:11px;font-weight:600;display:inline-flex;align-items:center;gap:4px;">
                                                    <span x-text="orchRunning[item.groupId] ? '⟳ Rodando…' : '▶ Rodar'"></span>
                                                </button>
                                            </template>
                                        </div>
                                    </div>

                                    {{-- Mensagem descritiva --}}
                                    <div class="vee-feed-card__msg" x-text="item.msg"></div>

                                    {{-- Progress bar quando rodando --}}
                                    <template x-if="orchRunning[item.groupId]">
                                        <div class="vee-progress" style="margin-top:6px;">
                                            <div class="vee-progress__fill" style="background:#ff8800;width:100%;animation:vee-indeterminate 1.2s linear infinite;"></div>
                                        </div>
                                    </template>

                                    {{-- Skip note --}}
                                    <template x-if="item.isSkipped">
                                        <div style="font-size:10px;color:#666;margin-top:4px;font-style:italic;" x-text="'Skip: ' + item.skipReason"></div>
                                    </template>

                                    {{-- Bug tags --}}
                                    <template x-if="item.bugs && item.bugs.length > 0">
                                        <div style="display:flex;gap:4px;flex-wrap:wrap;margin-top:6px;">
                                            <template x-for="bug in item.bugs" :key="bug">
                                                <span style="background:rgba(239,68,68,.1);color:#ef4444;border:1px solid rgba(239,68,68,.25);padding:1px 6px;border-radius:3px;font-size:10px;font-weight:600;"
                                                      x-text="bug"></span>
                                            </template>
                                        </div>
                                    </template>

                                    {{-- Route badge --}}
                                    <template x-if="item.route">
                                        <div style="margin-top:5px;">
                                            <span :style="`background:${item.route==='atendimento'?'rgba(59,130,246,.12)':'rgba(168,85,247,.12)'};color:${item.route==='atendimento'?'#60a5fa':'#c084fc'};border:1px solid ${item.route==='atendimento'?'rgba(59,130,246,.3)':'rgba(168,85,247,.3)'};padding:1px 7px;border-radius:3px;font-size:10px;font-weight:600;`"
                                                  x-text="item.route"></span>
                                        </div>
                                    </template>

                                </div>
                            </template>
                            </template>{{-- fecha x-if orchStatusLoading --}}
                        </div>
                        {{-- Sem input de chat neste tab --}}

                    </div>

                    {{-- Pipeline view --}}
                    <div x-show="orchView==='B'" style="flex:1;overflow-y:auto;padding:24px;display:none;">
                        <div class="vee-pipeline-label">Pipeline: Test Runner</div>
                        <div class="vee-pipeline">
                            <template x-for="(step, i) in orchPipeline" :key="step.agent">
                                <div class="vee-pipeline__step">
                                    <div :style="`border-color:${step.status==='done'?'#22c55e':step.status==='working'?'#ff8800':'#2e2e2e'};`"
                                         class="vee-pipeline-card">
                                        <div :style="`color:${step.status==='done'?'#22c55e':step.status==='working'?'#ff8800':'#999'};`"
                                             class="vee-pipeline-card__agent" x-text="step.agent"></div>
                                        <div class="vee-pipeline-card__label" x-text="step.label"></div>
                                    </div>
                                    <template x-if="i < orchPipeline.length - 1">
                                        <div class="vee-pipeline__arrow">→</div>
                                    </template>
                                </div>
                            </template>
                        </div>
                    </div>

                    {{-- Graph view --}}
                    <div x-show="orchView==='C'" style="flex:1;overflow-y:auto;padding:20px;display:none;">
                        <div class="vee-graph-label">Topologia — Test Runner</div>
                        <div class="vee-graph">
                            <svg style="position:absolute;inset:0;width:100%;height:100%;">
                                <template x-for="([a,b], i) in orchGraphEdges" :key="i">
                                    <line :x1="`${orchGraphNodes[a].x}%`" :y1="`${orchGraphNodes[a].y}%`"
                                          :x2="`${orchGraphNodes[b].x}%`" :y2="`${orchGraphNodes[b].y}%`"
                                          stroke="#2e2e2e" stroke-width="1.5" stroke-dasharray="5 3"/>
                                </template>
                            </svg>
                            <template x-for="node in orchGraphNodes" :key="node.label">
                                <div :style="`left:${node.x}%;top:${node.y}%;border-color:${node.main?'#ff8800':agentStatusColor(node.status||'idle')};background:${node.main?'rgba(255,136,0,.12)':'#222'};color:${node.main?'#ff8800':'#fff'};font-weight:${node.main?'700':'600'};`"
                                     class="vee-graph-node">
                                    <span x-text="node.label"></span>
                                </div>
                            </template>
                        </div>
                    </div>

                </div>

                {{-- ===== TAB: COWORK ===== --}}
                <div x-show="tab==='cowork'" class="flex h-full" style="display:none;">

                    {{-- Tasks (flex: 1) --}}
                    <div class="vee-tasks">
                        <div class="vee-tasks__header">
                            <span class="vee-tasks__title">Tasks do Projeto</span>
                            <div class="vee-tasks__badges">
                                <span class="vee-badge vee-badge--success">2 concluídas</span>
                                <span class="vee-badge vee-badge--accent">3 rodando</span>
                                <span class="vee-badge vee-badge--muted">3 pendentes</span>
                            </div>
                        </div>
                        <div class="vee-tasks__list">
                            <template x-for="task in coworkTasks" :key="task.id">
                                <div :style="`border-left-color:${task.status==='done'?'#22c55e':task.status==='running'?'#ff8800':'#444'};`"
                                     class="vee-content-card">
                                    <div class="vee-task-card__header">
                                        <div>
                                            <div class="vee-task-card__title-row">
                                                <span :style="`color:${task.status==='done'?'#22c55e':task.status==='running'?'#ff8800':'#555'};`"
                                                      x-text="task.status==='done'?'✓':task.status==='running'?'●':'○'"
                                                      class="vee-task-card__status-icon"></span>
                                                <span x-text="task.title"></span>
                                            </div>
                                            <div class="vee-task-card__meta">
                                                <span x-text="task.agent" class="vee-task-card__agent"></span>
                                                <span x-text="'env:'+task.env" class="vee-task-card__meta-item"></span>
                                                <span x-text="'due '+task.due" class="vee-task-card__meta-item"></span>
                                            </div>
                                        </div>
                                        <span :style="`background:${task.status==='done'?'rgba(34,197,94,.12)':task.status==='running'?'rgba(255,136,0,.12)':'rgba(85,85,85,.12)'};color:${task.status==='done'?'#22c55e':task.status==='running'?'#ff8800':'#555'};border:1px solid ${task.status==='done'?'rgba(34,197,94,.27)':task.status==='running'?'rgba(255,136,0,.27)':'rgba(85,85,85,.27)'};`"
                                              class="vee-task-card__badge"
                                              x-text="task.status==='done'?'concluído':task.status==='running'?'rodando':'pendente'"></span>
                                    </div>
                                    <template x-if="task.status !== 'pending'">
                                        <div class="vee-task-card__progress">
                                            <div class="vee-progress" style="flex:1;margin-top:0;">
                                                <div :style="`width:${task.pct}%;background:${task.status==='done'?'#22c55e':'#ff8800'};`"
                                                     class="vee-progress__fill"></div>
                                            </div>
                                            <span x-text="task.pct+'%'" class="vee-task-card__pct"></span>
                                        </div>
                                    </template>
                                </div>
                            </template>
                        </div>
                    </div>

                    {{-- Crons sidebar (260px) --}}
                    <div class="vee-crons">
                        <div class="vee-sidebar-header">Agendamentos</div>
                        <div class="vee-crons__list">
                            <template x-for="cron in coworkCrons" :key="cron.id">
                                <div :style="!cron.active ? 'opacity:.5;' : ''"
                                     class="vee-cron-card">
                                    <div class="vee-cron-card__header">
                                        <span x-text="cron.name" class="vee-cron-card__name"></span>
                                        <span :style="`background:${cron.active?'#ff8800':'#555'};`"
                                              class="vee-status-dot"></span>
                                    </div>
                                    <div x-text="cron.schedule" class="vee-cron-card__schedule"></div>
                                    <div class="vee-cron-card__next" x-text="'próximo: '+cron.next"></div>
                                    <div class="vee-cron-card__agent" x-text="'→ '+cron.agent"></div>
                                </div>
                            </template>
                            <button class="vee-cron-add-btn">
                                <i data-lucide="plus"></i> Novo agendamento
                            </button>
                        </div>
                    </div>
                </div>

                {{-- ===== TAB: APPROVALS ===== --}}
                <div x-show="tab==='approvals'" style="display:flex;flex-direction:column;height:100%;display:none;">
                    <div class="vee-approvals-toolbar">
                        <div class="vee-approvals-toolbar__stats">
                            <span class="vee-badge vee-badge--accent" x-text="`${pendingApprovalsTotal} pendentes`"></span>
                            <span class="vee-badge vee-badge--muted" x-text="`${approvals.length} exibidas`"></span>
                        </div>
                        <div class="vee-spacer"></div>
                        <div class="vee-approvals-toolbar__filters">
                            <template x-for="filter in approvalFilters" :key="filter.key">
                                <button @click="setApprovalFilter(filter.key)"
                                    :style="approvalStatusFilter===filter.key ? 'background:rgba(255,136,0,.12);border-color:#ff8800;color:#ff8800;' : 'background:transparent;border-color:#2e2e2e;color:#999;'"
                                    class="vee-filter-btn"
                                    x-text="filter.label"></button>
                            </template>
                        </div>
                        <button @click="refreshApprovals()" class="vee-approvals-refresh-btn">Atualizar</button>
                    </div>

                    <template x-if="approvalsError">
                        <div class="vee-approvals-error" x-text="approvalsError"></div>
                    </template>

                    <div class="vee-approvals-list">
                        <template x-if="approvalsLoading && approvals.length === 0">
                            <div class="vee-approvals-empty">Carregando approvals...</div>
                        </template>
                        <template x-if="!approvalsLoading && approvals.length === 0">
                            <div class="vee-approvals-empty">Nenhuma acao encontrada para o filtro atual.</div>
                        </template>

                        <template x-for="approval in approvals" :key="approval.approval_id">
                            <div class="vee-approval-card">
                                <div class="vee-approval-card__head">
                                    <div class="vee-approval-card__title-wrap">
                                        <div class="vee-approval-card__title"
                                             x-text="approval.action_name || approval.tool_name || 'Acao protegida'"></div>
                                        <div class="vee-approval-card__meta">
                                            <span class="vee-approval-card__id"
                                                  x-text="`id: ${approval.approval_id || 'n/a'}`"></span>
                                            <span class="vee-approval-card__tool"
                                                  x-text="`tool: ${approval.tool_name || 'n/a'}`"></span>
                                            <span class="vee-approval-card__time"
                                                  x-text="formatTime(approval.created_at)"></span>
                                        </div>
                                    </div>
                                    <span class="vee-approval-card__status"
                                          :class="approvalStatusBadgeClass(approval.status)"
                                          x-text="approvalStatusLabel(approval.status)"></span>
                                </div>

                                <template x-if="approval.summary">
                                    <p class="vee-approval-card__summary" x-text="approval.summary"></p>
                                </template>

                                <template x-if="approval.request_payload">
                                    <pre class="vee-approval-card__payload"
                                         x-text="truncate(approval.request_payload, 560)"></pre>
                                </template>

                                <template x-if="approval.status === 'pending'">
                                    <div class="vee-approval-card__actions">
                                        <input
                                            type="text"
                                            placeholder="Motivo da rejeicao (opcional)"
                                            class="vee-approval-card__reason"
                                            x-model="approvalRejectReasonById[approval.approval_id]"
                                        />
                                        <button
                                            @click="approvePendingApproval(approval.approval_id)"
                                            :disabled="isApprovalBusy(approval.approval_id)"
                                            class="vee-approval-btn vee-approval-btn--approve"
                                        >
                                            Aprovar
                                        </button>
                                        <button
                                            @click="rejectPendingApproval(approval.approval_id)"
                                            :disabled="isApprovalBusy(approval.approval_id)"
                                            class="vee-approval-btn vee-approval-btn--reject"
                                        >
                                            Rejeitar
                                        </button>
                                    </div>
                                </template>
                            </div>
                        </template>
                    </div>
                </div>

                {{-- ===== TAB: LOGS ===== --}}
                <div x-show="tab==='logs'" style="display:flex;flex-direction:column;height:100%;display:none;">

                    {{-- Toolbar --}}
                    <div class="vee-log-toolbar">
                        <i data-lucide="filter" class="vee-filter-icon"></i>
                        <template x-for="f in logFilters" :key="f.key">
                            <button @click="logFilter=f.key"
                                :style="logFilter===f.key ? 'background:rgba(255,136,0,.12);border-color:#ff8800;color:#ff8800;' : 'background:transparent;border-color:#2e2e2e;color:#999;'"
                                class="vee-filter-btn"
                                x-text="f.label"></button>
                        </template>
                        <div class="vee-spacer"></div>
                        <span x-show="logConnected" class="vee-live-indicator">
                            <span class="vee-live-dot"></span>
                            Ao vivo
                        </span>
                        <span x-show="!logConnected" class="vee-log-disconnected">Desconectado</span>
                        <span x-text="logEventsFilt.length+' eventos'" class="vee-log-count"></span>
                        <button @click="clearLogEvents()" class="vee-log-clear-btn">Limpar</button>
                    </div>

                    {{-- Timeline --}}
                    <div x-ref="logArea" class="vee-log-area">
                        <template x-if="logEventsFilt.length === 0">
                            <div class="vee-log-empty">
                                <p>Nenhum evento ainda. Aguardando atividade...</p>
                            </div>
                        </template>
                        <div class="vee-log-timeline">
                            <template x-for="(log, i) in logEventsFilt" :key="i">
                                <div class="vee-log-entry">
                                    <div :style="`background:${logLevelColor(log.level||log.type)};`"
                                         class="vee-log-entry__dot"></div>
                                    <div :style="`border-color:${(log.level==='ERROR'||log.type==='tool_error')?'rgba(239,68,68,.27)':(log.level==='WARN')?'rgba(245,158,11,.2)':'#2e2e2e'};`"
                                         class="vee-log-card">
                                        <div class="vee-log-card__meta">
                                            <span x-text="formatTime(log.timestamp||log.time)" class="vee-log-card__time"></span>
                                            <span :style="`color:${logLevelColor(log.level||log.type)};`"
                                                  x-text="log.level||log.type" class="vee-log-card__level"></span>
                                            <span x-show="log.agent||log.tool_name"
                                                  x-text="log.agent||log.tool_name" class="vee-log-card__agent"></span>
                                            <span x-show="log.env" x-text="'env:'+log.env" class="vee-log-card__env"></span>
                                            <span x-show="log.duration_ms" x-text="log.duration_ms+'ms'" class="vee-log-card__duration"></span>
                                        </div>
                                        <div x-text="log.msg||log.message" class="vee-log-card__msg"></div>
                                        <template x-if="log.error">
                                            <p x-text="log.error" class="vee-log-card__error"></p>
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

<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('veeDrawer', () => ({
        open: false,

        // ---- UI state ----
        tab: 'plan',
        orchView: 'A',
        activeAgentId: 'test-runner',
        orchRunning: {},
        orchStatusLoading: false,
        orchStatusError: null,
        orchToasts:  [],
        testCasesByGroup: {
            'A': { route:'atendimento', tenant_id:5, tenant_slug:'veetest',  message:'Ol\u00e1, quero saber sobre voc\u00eas' },
            'B': { route:'atendimento', tenant_id:5, tenant_slug:'veetest',  message:'Quero marcar um hor\u00e1rio' },
            'C': { route:'atendimento', tenant_id:5, tenant_slug:'veetest',  message:'Ol\u00e1 de novo' },
            'D': { route:'agendamento', tenant_id:5, tenant_slug:'veetest',  message:'Quero agendar Avalia\u00e7\u00e3o Gratuita' },
            'E': { route:'agendamento', tenant_id:5, tenant_slug:'veetest',  message:'Quero marcar para amanh\u00e3 \u00e0s 10h com a Ana' },
            'F': null,
            'G': { route:'agendamento', tenant_id:5, tenant_slug:'veetest',  message:'Avalia\u00e7\u00e3o Gratuita, amanh\u00e3 \u00e0s 09h, com a Ana' },
            'H': { route:'agendamento', tenant_id:5, tenant_slug:'veetest',  message:'Quero remarcar meu hor\u00e1rio' },
            'I': { route:'agendamento', tenant_id:5, tenant_slug:'veetest',  message:'Quero cancelar meu agendamento' },
            'J': null,
            'K': { route:'agendamento', tenant_id:5, tenant_slug:'veetest',  message:'Quero agendar' },
            'L': { route:'atendimento', tenant_id:5, tenant_slug:'veetest',  message:'Ol\u00e1' },
            'M': { route:'agendamento', tenant_id:3, tenant_slug:'mmbeauty', message:'Quero agendar um corte' },
        },

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
        starredSessions: JSON.parse(localStorage.getItem('vee-starred') || '[]'),
        editingSessionId: null,
        editingTitle: '',
        inputText: '',
        editingMessageId: null,
        editingMessageText: '',

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
        approvals: [],
        approvalsLoading: false,
        approvalsError: '',
        approvalStatusFilter: 'pending',
        approvalFilters: [
            { key:'pending', label:'Pendentes' },
            { key:'all', label:'Todos' },
            { key:'approved', label:'Aprovadas' },
            { key:'rejected', label:'Rejeitadas' },
            { key:'executed', label:'Executadas' },
        ],
        approvalRejectReasonById: {},
        approvalBusyById: {},
        approvalSeenIds: {},
        pendingApprovalsTotal: 0,
        approvalsPollTimer: null,

        // ---- config ----
        agentUrl: window.VEE_AGENT_URL   || '',
        agentToken: window.VEE_AGENT_TOKEN || '',

        // ---- static demo data ----
        tabLabels: { plan:'Planejamento', orch:'Orquestramento', cowork:'Cowork', approvals:'Approvals', logs:'Logs' },

        orchAgents: [
            { id:'test-runner', name:'Test Runner', env:'mmcloud',  status:'active', task:'Pronto para executar' },
            { id:'vault-agent', name:'Vault Agent', env:'vault',    status:'active', task:'Secrets validados ✓' },
            { id:'obsidian',    name:'Obsidian',    env:'obsidian', status:'active', task:'Documentando...' },
        ],
        orchTestGroups: [
            { id:'A', name:'Onboarding',        route:'atendimento', tenant_slug:'veetest',   daily:3,  total:4,  bugs:[],          status:'idle' },
            { id:'B', name:'Intent',            route:'atendimento', tenant_slug:'veetest',   daily:5,  total:5,  bugs:[],          status:'idle' },
            { id:'C', name:'Fluxo Completo',    route:'atendimento', tenant_slug:'veetest',   daily:3,  total:4,  bugs:[],          status:'idle' },
            { id:'D', name:'Entity Resolver',   route:'agendamento', tenant_slug:'veetest',   daily:6,  total:7,  bugs:['BUG-001'], status:'idle' },
            { id:'E', name:'Missing Fields',    route:'agendamento', tenant_slug:'veetest',   daily:5,  total:5,  bugs:['BUG-001'], status:'idle' },
            { id:'F', name:'PreCheck/Blocking', route:'agendamento', tenant_slug:'veetest-c', daily:0,  total:3,  bugs:[],          status:'skip', skipReason:'veetest-c nao criado' },
            { id:'G', name:'Criacao Agend.',    route:'agendamento', tenant_slug:'veetest',   daily:2,  total:4,  bugs:['BUG-001'], status:'idle' },
            { id:'H', name:'Reagendamento',     route:'agendamento', tenant_slug:'veetest',   daily:0,  total:3,  bugs:['BUG-001'], status:'idle' },
            { id:'I', name:'Cancelamento',      route:'agendamento', tenant_slug:'veetest',   daily:0,  total:2,  bugs:['BUG-001'], status:'idle' },
            { id:'J', name:'Desambiguacao',     route:'agendamento', tenant_slug:'veetest-c', daily:0,  total:2,  bugs:[],          status:'skip', skipReason:'veetest-c nao criado' },
            { id:'K', name:'Save Process',      route:'agendamento', tenant_slug:'veetest',   daily:2,  total:2,  bugs:['BUG-002'], status:'idle' },
            { id:'L', name:'Hub/Roteamento',    route:'atendimento', tenant_slug:'veetest',   daily:2,  total:2,  bugs:[],          status:'idle' },
            { id:'M', name:'mmbeauty',          route:'agendamento', tenant_slug:'mmbeauty',  daily:6,  total:12, bugs:['BUG-001'], status:'idle' },
        ],
        get orchFeed() {
            if (this.activeAgentId === 'test-runner') {
                return this.orchTestGroups.map(function(g) {
                    return {
                        id: g.id,
                        from: 'Test Runner',
                        to: 'Grupo ' + g.id + ' — ' + g.name,
                        msg: g.daily + ' casos daily · ' + g.total + ' total · tenant: ' + g.tenant_slug + (g.bugs.length ? ' · ' + g.bugs.join(', ') : ''),
                        time: g.lastRun || '—',
                        status: g.status === 'skip' ? 'idle' : g.status,
                        pct: g.pct || 0,
                        bugs: g.bugs,
                        route: g.route,
                        isSkipped: g.status === 'skip',
                        skipReason: g.skipReason || '',
                        groupId: g.id,
                    };
                });
            }
            return [
                { id:0, from:'Vault Agent', to:'Vee', msg:'Secrets validados. Politica de rotacao OK.', time: new Date().toTimeString().slice(0,5), status:'done', bugs:[], isSkipped:false },
                { id:1, from:'Obsidian',    to:'Vee', msg:'Notas sincronizadas com o vault.',            time: new Date().toTimeString().slice(0,5), status:'done', bugs:[], isSkipped:false },
            ];
        }
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
                if (val === 'orch')  this.loadOrchStatus();
                if (val === 'logs') this.startLogFeed();
                if (val !== 'logs') this.stopLogFeed();
                if (val === 'approvals') {
                    this.loadApprovals();
                    this.startApprovalsPoll();
                } else {
                    this.stopApprovalsPoll();
                }
            });
            this.$watch('orchView', val => {
                const s = JSON.parse(localStorage.getItem('vee-ui-state') || '{}');
                localStorage.setItem('vee-ui-state', JSON.stringify({ ...s, orchView: val }));
            });
            this.$watch('callOpen', val => {
                if (val) this._startCallCycle();
                else     this._stopCallCycle();
            });

            // Pré-carrega sessões em background para o sidebar já estar pronto quando o drawer abrir
            if (this.agentUrl) this.loadSessions();
            this.loadApprovals({ silent: true });
        },

        openDrawer() {
            this.open = true;
            document.body.classList.add('overflow-hidden');
            this.loadSessions().then(() => {
                if (!this.currentSession) {
                    const lastId = localStorage.getItem('vee-last-session');
                    if (lastId && this.sessions.find(s => s.id === lastId)) {
                        this.loadSession(lastId);
                    }
                }
            });
            if (this.tab === 'approvals') {
                this.loadApprovals();
                this.startApprovalsPoll();
            }
            if (this.tab === 'logs') {
                this.startLogFeed();
            }
            this.$nextTick(() => { if (window.lucide) lucide.createIcons(); });
        },

        closeDrawer() {
            if (this.isStreaming && this.streamController) {
                this.streamController.abort();
                this.isStreaming = false;
            }
            this.cancelMessageEdit();
            this.callOpen = false;
            this.open = false;
            document.body.classList.remove('overflow-hidden');
            this.stopLogFeed();
            this.stopApprovalsPoll();
        },

        switchToLogs() {
            this.tab = 'logs';
            this.startLogFeed();
        },

        switchToApprovals() {
            this.tab = 'approvals';
            this.loadApprovals();
            this.startApprovalsPoll();
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
            localStorage.setItem('vee-starred', JSON.stringify(this.starredSessions));
            this._sortSessions();
        },

        _sortSessions() {
            this.sessions = [
                ...this.sessions.filter(s => this.starredSessions.includes(s.id)),
                ...this.sessions.filter(s => !this.starredSessions.includes(s.id)),
            ];
        },

        setApprovalFilter(status) {
            this.approvalStatusFilter = status;
            this.loadApprovals();
        },

        async refreshApprovals() {
            await this.loadApprovals();
        },

        startApprovalsPoll() {
            this.stopApprovalsPoll();
            this.approvalsPollTimer = window.setInterval(() => {
                this.loadApprovals({ silent: true });
            }, 12000);
        },

        stopApprovalsPoll() {
            if (this.approvalsPollTimer) {
                window.clearInterval(this.approvalsPollTimer);
                this.approvalsPollTimer = null;
            }
        },

        isApprovalBusy(approvalId) {
            const id = String(approvalId || '');
            return Boolean(this.approvalBusyById[id]);
        },

        setApprovalBusy(approvalId, busy) {
            const id = String(approvalId || '');
            if (!id) return;
            this.approvalBusyById = {
                ...this.approvalBusyById,
                [id]: Boolean(busy),
            };
        },

        async fetchApprovalsByStatus(status, limit = 120, options = {}) {
            const params = new URLSearchParams();
            params.set('limit', String(limit));
            if (status && status !== 'all') {
                params.set('status', status);
            }
            const actionName = String(options?.actionName || '').trim();
            const toolName = String(options?.toolName || '').trim();
            if (actionName) {
                params.set('action_name', actionName);
            }
            if (toolName) {
                params.set('tool_name', toolName);
            }
            const res = await fetch(`${this.agentUrl}/approvals?${params.toString()}`, {
                headers: this.authHeaders(),
            });
            if (!res.ok) {
                throw new Error(`Falha ao carregar approvals (${res.status})`);
            }
            const data = await res.json();
            return {
                total: Number(data.total || 0),
                approvals: Array.isArray(data.approvals) ? data.approvals : [],
            };
        },

        async loadApprovals(options = {}) {
            if (!this.agentUrl) {
                this.approvalsError = 'VEE_AGENT_URL nao configurada.';
                return;
            }

            const silent = Boolean(options.silent);
            if (!silent) this.approvalsLoading = true;
            this.approvalsError = '';

            try {
                const [selected, pending] = await Promise.all([
                    this.fetchApprovalsByStatus(this.approvalStatusFilter),
                    this.approvalStatusFilter === 'pending'
                        ? Promise.resolve(null)
                        : this.fetchApprovalsByStatus('pending', 200),
                ]);

                this.approvals = selected.approvals;
                this.pendingApprovalsTotal = this.approvalStatusFilter === 'pending'
                    ? selected.total
                    : Number(pending?.total || 0);
                const mergedApprovals = [
                    ...(Array.isArray(selected.approvals) ? selected.approvals : []),
                    ...(Array.isArray(pending?.approvals) ? pending.approvals : []),
                ];
                this.syncInlineApprovalsFromList(mergedApprovals);
            } catch (err) {
                this.approvalsError = err?.message || 'Falha ao carregar approvals.';
            } finally {
                this.approvalsLoading = false;
            }
        },

        async approvePendingApproval(approvalId) {
            const id = String(approvalId || '').trim();
            if (!id || this.isApprovalBusy(id)) return;

            this.setApprovalBusy(id, true);
            this.approvalsError = '';
            try {
                const res = await fetch(`${this.agentUrl}/approvals/${encodeURIComponent(id)}/approve`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', ...this.authHeaders() },
                    body: JSON.stringify({ approved_by: 'vee-drawer-ui' }),
                });
                if (!res.ok) {
                    throw new Error(`Falha ao aprovar (${res.status})`);
                }
                const data = await res.json().catch(() => ({}));
                this.applyApprovalRecordToMessages({
                    approval_id: id,
                    status: String(data?.status || 'approved').toLowerCase(),
                });
                await this.loadApprovals({ silent: true });
            } catch (err) {
                this.approvalsError = err?.message || 'Falha ao aprovar acao.';
            } finally {
                this.setApprovalBusy(id, false);
            }
        },

        async rejectPendingApproval(approvalId) {
            const id = String(approvalId || '').trim();
            if (!id || this.isApprovalBusy(id)) return;

            const reason = String(this.approvalRejectReasonById[id] || '').trim();
            this.setApprovalBusy(id, true);
            this.approvalsError = '';
            try {
                const res = await fetch(`${this.agentUrl}/approvals/${encodeURIComponent(id)}/reject`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', ...this.authHeaders() },
                    body: JSON.stringify({
                        rejected_by: 'vee-drawer-ui',
                        reason: reason || null,
                    }),
                });
                if (!res.ok) {
                    throw new Error(`Falha ao rejeitar (${res.status})`);
                }
                const data = await res.json().catch(() => ({}));
                this.approvalRejectReasonById = {
                    ...this.approvalRejectReasonById,
                    [id]: '',
                };
                this.applyApprovalRecordToMessages({
                    approval_id: id,
                    status: String(data?.status || 'rejected').toLowerCase(),
                    rejection_reason: reason || null,
                });
                await this.loadApprovals({ silent: true });
            } catch (err) {
                this.approvalsError = err?.message || 'Falha ao rejeitar acao.';
            } finally {
                this.setApprovalBusy(id, false);
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
            this.cancelMessageEdit();
        },

        async loadSessions() {
            if (!this.agentUrl) return;
            try {
                const res = await fetch(`${this.agentUrl}/sessions?limit=50`, { headers: this.authHeaders() });
                if (!res.ok) return;
                const data = await res.json();
                // Merge previews: memória > localStorage > vazio
                const memMap  = Object.fromEntries(this.sessions.map(s => [s.id, s]));
                const lsPrev  = JSON.parse(localStorage.getItem('vee-previews') || '{}');
                this.sessions = (data.sessions || []).map(s => ({
                    ...s,
                    preview: memMap[s.id]?.preview || lsPrev[s.id] || '',
                }));
                this._sortSessions();
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
                this.cancelMessageEdit();
                localStorage.setItem('vee-last-session', id);
                this.$nextTick(() => {
                    this.decorateMessageHtml();
                    this.refreshLucide();
                    this.scrollToBottom();
                });
            } catch {}
        },

        async renameSession(id) {
            const title = this.editingTitle.trim();
            this.editingSessionId = null;
            this.editingTitle = '';
            if (!title || !this.agentUrl) return;
            try {
                await fetch(`${this.agentUrl}/sessions/${id}`, {
                    method: 'PATCH',
                    headers: { 'Content-Type': 'application/json', ...this.authHeaders() },
                    body: JSON.stringify({ title }),
                });
                const s = this.sessions.find(s => s.id === id);
                if (s) s.title = title;
                if (this.currentSession?.id === id) this.currentSession.title = title;
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
                const role = m?.role;
                if (role === 'user') {
                    msgs.push({
                        id: Number(m?.id || 0) || null,
                        role: 'user',
                        content: m?.content || '',
                        can_edit: Boolean(m?.can_edit),
                        toolActivities: [],
                    });
                } else if (role === 'assistant') {
                    msgs.push({
                        id: Number(m?.id || 0) || null,
                        role: 'assistant',
                        content: m?.content || '',
                        can_edit: false,
                        toolActivities: [],
                    });
                }
            }
            return msgs;
        },

        startMessageEdit(msg) {
            if (this.isStreaming) return;
            const messageId = Number(msg?.id || 0);
            const content = String(msg?.content || '').trim();
            if (messageId <= 0 || !content || !msg?.can_edit) return;
            this.editingMessageId = messageId;
            this.editingMessageText = content;
            this.$nextTick(() => this.scrollToBottom());
        },

        cancelMessageEdit() {
            this.editingMessageId = null;
            this.editingMessageText = '';
        },

        async submitMessageEdit(msg) {
            if (this.isStreaming) return;
            const messageId = Number(msg?.id || 0);
            const newText = this.editingMessageText.trim();
            if (messageId <= 0 || !newText) return;

            const index = this.messages.findIndex((item) => Number(item?.id || 0) === messageId);
            if (index < 0) {
                this.cancelMessageEdit();
                return;
            }

            const previous = String(this.messages[index]?.content || '').trim();
            if (newText === previous) {
                this.cancelMessageEdit();
                return;
            }

            this.messages[index].content = newText;
            this.messages[index].can_edit = false;
            this.messages = this.messages.slice(0, index + 1);
            this.cancelMessageEdit();

            await this.$nextTick();
            this.scrollToBottom();
            await this.streamSSE(newText, { editMessageId: messageId });
        },

        // ---- streaming ----
        async sendMessage() {
            const text = this.inputText.trim();
            if (!text || this.isStreaming) return;

            this.cancelMessageEdit();
            this.inputText = '';
            this.messages.push({ id:null, role:'user', content:text, can_edit:false, toolActivities:[] });
            await this.$nextTick();
            this.scrollToBottom();
            await this.streamSSE(text);
        },

        async streamSSE(message, options = {}) {
            const editMessageId = Number(options?.editMessageId || 0);
            this.isStreaming = true;
            this.streamController = new AbortController();

            if (editMessageId > 0 && this.messages.length > 0 && this.messages[this.messages.length - 1]?.role === 'assistant') {
                this.messages.pop();
            }

            const assistantIdx = this.messages.length;
            this.messages.push({ id:null, role:'assistant', content:'', can_edit:false, toolActivities:[] });

            try {
                const res = await fetch(`${this.agentUrl}/stream`, {
                    method: 'POST',
                    headers: { 'Content-Type':'application/json', ...this.authHeaders() },
                    body: JSON.stringify({
                        message,
                        session_id: this.currentSession?.id ?? undefined,
                        mode: 'chat',
                        edit_message_id: editMessageId > 0 ? editMessageId : undefined,
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
                            case 'session_created': {
                                const newSess = { id:evt.session_id, title:'Nova conversa', mode:evt.mode, updated_at: new Date().toISOString(), preview:'' };
                                this.currentSession = newSess;
                                this.sessions = [newSess, ...this.sessions.filter(s => s.id !== evt.session_id)];
                                localStorage.setItem('vee-last-session', evt.session_id);
                                break;
                            }
                            case 'text':
                                this.messages[assistantIdx].content += evt.content;
                                // Captura as primeiras palavras como preview da sessão
                                if (this.currentSession && !this.messages[assistantIdx]._previewSet) {
                                    const full = this.messages[assistantIdx].content;
                                    if (full.length > 20) {
                                        const preview = full.replace(/[#*`\n]/g, ' ').trim().slice(0, 80);
                                        this.messages[assistantIdx]._previewSet = true;
                                        const s = this.sessions.find(s => s.id === this.currentSession.id);
                                        if (s) s.preview = preview;
                                        if (this.currentSession) this.currentSession.preview = preview;
                                        // Persiste preview no localStorage para sobreviver ao refresh
                                        try {
                                            const lsPrev = JSON.parse(localStorage.getItem('vee-previews') || '{}');
                                            lsPrev[this.currentSession.id] = preview;
                                            localStorage.setItem('vee-previews', JSON.stringify(lsPrev));
                                        } catch {}
                                    }
                                }
                                this.$nextTick(() => {
                                    this.decorateMessageHtml();
                                    this.refreshLucide();
                                    this.scrollToBottom();
                                });
                                break;
                            case 'tool_start':
                                this.messages[assistantIdx].toolActivities.push({
                                    call_id:evt.call_id, name:evt.name, args:evt.args,
                                    status:'running', result:null, error:null, duration_ms:null, started_at:new Date().toISOString(), approval:null,
                                });
                                this.$nextTick(() => {
                                    this.decorateMessageHtml();
                                    this.refreshLucide();
                                    this.scrollToBottom();
                                });
                                break;
                            case 'tool_result': {
                                const t = this.messages[assistantIdx].toolActivities.find(x => x.call_id === evt.call_id);
                                if (t) {
                                    t.status='done';
                                    t.result=evt.result;
                                    t.duration_ms=evt.duration_ms;
                                    await this.attachPendingApprovalToTool(t, evt);
                                }
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
                                    if (s) s.title = evt.title;
                                    else this.loadSessions();
                                }
                                break;
                            case 'done':
                                this.isStreaming = false;
                                if (this.currentSession?.id) {
                                    await this.loadSession(this.currentSession.id);
                                }
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

        loadOrchStatus() {
            const agentUrl = window.VEE_AGENT_URL;
            const token    = window.VEE_AGENT_TOKEN;
            if (!agentUrl) return;
            this.orchStatusLoading = true;
            this.orchStatusError   = null;
            const self = this;
            fetch(agentUrl + '/orch/status', {
                headers: { 'Authorization': 'Bearer ' + token, 'Accept': 'application/json' },
                signal: AbortSignal.timeout(10000),
            })
            .then(function(r) {
                if (!r.ok) throw new Error('HTTP ' + r.status);
                return r.json();
            })
            .then(function(data) {
                if (data.workflow) {
                    const wf = data.workflow;
                    self.orchAgents = self.orchAgents.map(function(a) {
                        if (a.id === 'test-runner') {
                            return Object.assign({}, a, {
                                status: wf.active === true ? 'active' : wf.active === false ? 'idle' : a.status,
                                task:   wf.active === true ? 'Ativo no n8n' : 'Inativo',
                            });
                        }
                        return a;
                    });
                }
                if (Array.isArray(data.groups)) {
                    self.orchTestGroups = data.groups.map(function(g) {
                        return Object.assign({}, g, {
                            lastRun: g.lastRun || '—',
                        });
                    });
                }
            })
            .catch(function(err) {
                self.orchStatusError = err.message;
            })
            .finally(function() {
                self.orchStatusLoading = false;
            });
        },

        saveOrchRun(groupId, status, executionId) {
            const agentUrl = window.VEE_AGENT_URL;
            const token    = window.VEE_AGENT_TOKEN;
            if (!agentUrl) return;
            const group = this.orchTestGroups.find(function(g) { return g.id === groupId; });
            fetch(agentUrl + '/orch/runs', {
                method: 'POST',
                headers: {
                    'Authorization': 'Bearer ' + token,
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    group_id:     groupId,
                    status:       status,
                    execution_id: executionId || null,
                    route:        group ? group.route : null,
                    tenant_slug:  group ? group.tenant_slug : null,
                }),
            }).catch(function() { /* best-effort */ });
        },

        runTestGroup(groupId) {
            if (this.orchRunning[groupId]) return;
            const tc = this.testCasesByGroup[groupId];
            if (!tc) return; // skip (no test case defined)

            this.orchRunning = Object.assign({}, this.orchRunning, { [groupId]: true });

            const self = this;
            const startedAt = Date.now();

            fetch('https://n8n.mmcriativos.cloud/webhook/WRPPK93a5dttEwzA/webhook/test-runner', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    target:      tc.route,
                    tenant_slug: tc.tenant_slug,
                    tenant_id:   tc.tenant_id,
                    user_message: tc.message,
                    remoteJid:   '5511900000001@s.whatsapp.net',
                    pushName:    'Tester',
                    idMessage:   'TEST-' + startedAt,
                }),
            })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                const verdict = data.status === 'success' ? 'pass' : 'fail';
                const now = new Date().toTimeString().slice(0, 5);
                const g = self.orchTestGroups.find(function(g) { return g.id === groupId; });
                if (g) { g.status = verdict; g.lastRun = now; }

                self.saveOrchRun(groupId, verdict, data && data.executionId ? data.executionId : null);
                const toastId = Date.now();
                self.orchToasts.push({
                    id:      toastId,
                    msg:     'Grupo ' + groupId + (verdict === 'pass' ? ' — ✓ Passou' : ' — ✗ Falhou'),
                    type:    verdict,
                    groupId: groupId,
                });
                setTimeout(function() { self.dismissToast(toastId); }, 6000);
            })
            .catch(function(err) {
                const toastId = Date.now();
                self.orchToasts.push({
                    id:      toastId,
                    msg:     'Grupo ' + groupId + ' — erro: ' + err.message,
                    type:    'fail',
                    groupId: groupId,
                });
                setTimeout(function() { self.dismissToast(toastId); }, 6000);
            })
            .finally(function() {
                const updated = Object.assign({}, self.orchRunning);
                delete updated[groupId];
                self.orchRunning = updated;
            });
        },

        dismissToast(toastId) {
            this.orchToasts = this.orchToasts.filter(function(t) { return t.id !== toastId; });
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

        approvalStatusLabel(status) {
            const value = String(status || '').toLowerCase();
            if (value === 'pending') return 'pendente';
            if (value === 'approved') return 'aprovada';
            if (value === 'rejected') return 'rejeitada';
            if (value === 'executed') return 'executada';
            return value || 'desconhecido';
        },

        approvalStatusBadgeClass(status) {
            const value = String(status || '').toLowerCase();
            if (value === 'pending') return 'vee-approval-card__status--pending';
            if (value === 'approved') return 'vee-approval-card__status--approved';
            if (value === 'rejected') return 'vee-approval-card__status--rejected';
            if (value === 'executed') return 'vee-approval-card__status--executed';
            return 'vee-approval-card__status--unknown';
        },

        normalizeApprovalRecord(approval) {
            if (!approval || typeof approval !== 'object') return null;
            const approvalId = String(approval.approval_id || '').trim();
            if (!approvalId) return null;

            return {
                approval_id: approvalId,
                action_name: String(approval.action_name || '').trim(),
                tool_name: String(approval.tool_name || '').trim(),
                status: String(approval.status || 'pending').toLowerCase(),
                summary: typeof approval.summary === 'string' ? approval.summary : '',
                request_payload: approval.request_payload ?? null,
                rejection_reason: typeof approval.rejection_reason === 'string' ? approval.rejection_reason : '',
                created_at: approval.created_at || null,
            };
        },

        extractApprovalIdFromText(rawText) {
            const text = String(rawText || '');
            if (!text) return '';
            const match = text.match(/[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}/i);
            return match ? String(match[0]).toLowerCase() : '';
        },

        extractApprovalId(source) {
            if (!source) return '';
            if (typeof source === 'string') {
                return this.extractApprovalIdFromText(source);
            }
            if (typeof source !== 'object') return '';

            const directCandidates = [source.approval_id, source.approvalId];
            for (const candidate of directCandidates) {
                const id = this.extractApprovalIdFromText(candidate);
                if (id) return id;
            }

            if (source.approval && typeof source.approval === 'object') {
                const nestedId = this.extractApprovalId(source.approval);
                if (nestedId) return nestedId;
            }

            if (typeof source.result === 'string') {
                const fromResult = this.extractApprovalIdFromText(source.result);
                if (fromResult) return fromResult;
            }

            try {
                return this.extractApprovalIdFromText(JSON.stringify(source));
            } catch {
                return '';
            }
        },

        bindApprovalToToolActivity(tool, approval) {
            const normalized = this.normalizeApprovalRecord(approval);
            if (!normalized || !tool) return false;

            this.approvalSeenIds = {
                ...this.approvalSeenIds,
                [normalized.approval_id]: true,
            };

            const previous = this.normalizeApprovalRecord(tool.approval) || {};
            tool.approval = { ...previous, ...normalized };
            return true;
        },

        syncInlineApprovalsFromList(approvals = []) {
            if (!Array.isArray(approvals) || approvals.length === 0) return;
            approvals.forEach((approval) => {
                this.applyApprovalRecordToMessages(approval);
            });
        },

        applyApprovalRecordToMessages(approval) {
            const normalized = this.normalizeApprovalRecord(approval);
            if (!normalized) return false;

            this.approvalSeenIds = {
                ...this.approvalSeenIds,
                [normalized.approval_id]: true,
            };

            let updated = false;
            this.messages.forEach((msg) => {
                if (!msg || !Array.isArray(msg.toolActivities)) return;
                msg.toolActivities.forEach((tool) => {
                    const existingId = String(tool?.approval?.approval_id || '').trim();
                    if (existingId !== normalized.approval_id) return;
                    const previous = this.normalizeApprovalRecord(tool.approval) || {};
                    tool.approval = { ...previous, ...normalized };
                    updated = true;
                });
            });

            return updated;
        },

        async fetchApprovalById(approvalId) {
            const id = String(approvalId || '').trim();
            if (!id || !this.agentUrl) return null;

            const res = await fetch(`${this.agentUrl}/approvals/${encodeURIComponent(id)}`, {
                headers: this.authHeaders(),
            });
            if (!res.ok) return null;
            const data = await res.json();
            return data?.approval || null;
        },

        async attachPendingApprovalToTool(tool, evt = null) {
            if (!tool || !this.agentUrl) return;
            if (tool.approval && tool.approval.approval_id) return;

            try {
                const directApprovalId = this.extractApprovalId(evt?.result);
                if (directApprovalId) {
                    const approval = await this.fetchApprovalById(directApprovalId);
                    if (approval && this.bindApprovalToToolActivity(tool, approval)) {
                        this.pendingApprovalsTotal = Math.max(1, Number(this.pendingApprovalsTotal || 0));
                        this.$nextTick(() => {
                            this.refreshLucide();
                            this.scrollToBottom();
                        });
                        return;
                    }
                }

                const toolName = String(tool.name || '').trim();
                if (!toolName) return;

                const pending = await this.fetchApprovalsByStatus('pending', 30, { toolName });
                const startedAtMs = Date.parse(String(tool.started_at || ''));
                const nowMs = Date.now();

                const candidate = (Array.isArray(pending.approvals) ? pending.approvals : [])
                    .map((item) => this.normalizeApprovalRecord(item))
                    .filter(Boolean)
                    .find((item) => {
                        if (!item || item.status !== 'pending') return false;
                        if (this.approvalSeenIds[item.approval_id]) return false;
                        if (item.tool_name && String(item.tool_name).trim() && String(item.tool_name).trim() !== toolName) return false;

                        const createdAtMs = Date.parse(String(item.created_at || ''));
                        if (Number.isFinite(createdAtMs)) {
                            if (nowMs - createdAtMs > 20 * 60 * 1000) return false;
                            if (Number.isFinite(startedAtMs) && createdAtMs < startedAtMs - 2 * 60 * 1000) return false;
                        }

                        return true;
                    });

                if (candidate && this.bindApprovalToToolActivity(tool, candidate)) {
                    this.pendingApprovalsTotal = Math.max(1, Number(this.pendingApprovalsTotal || 0));
                    this.$nextTick(() => {
                        this.refreshLucide();
                        this.scrollToBottom();
                    });
                }
            } catch {}
        },

        authHeaders() {
            return this.agentToken ? { 'Authorization': `Bearer ${this.agentToken}` } : {};
        },

        async copyText(text) {
            const content = String(text || '');
            if (!content) return false;
            try {
                if (navigator?.clipboard?.writeText) {
                    await navigator.clipboard.writeText(content);
                    return true;
                }
            } catch {}

            try {
                const temp = document.createElement('textarea');
                temp.value = content;
                temp.setAttribute('readonly', '');
                temp.style.position = 'fixed';
                temp.style.opacity = '0';
                temp.style.pointerEvents = 'none';
                document.body.appendChild(temp);
                temp.focus();
                temp.select();
                const copied = document.execCommand('copy');
                temp.remove();
                return copied;
            } catch {
                return false;
            }
        },

        refreshLucide() {
            if (!window.lucide || typeof window.lucide.createIcons !== 'function') {
                return;
            }
            window.lucide.createIcons();
        },

        setCopyButtonState(button, state) {
            if (!button) return;
            const iconByState = {
                default: 'copy',
                copied: 'check',
                error: 'alert-circle',
            };
            const ariaByState = {
                default: 'Copiar bloco',
                copied: 'Copiado',
                error: 'Falha ao copiar',
            };

            const nextState = Object.prototype.hasOwnProperty.call(iconByState, state) ? state : 'default';
            const icon = iconByState[nextState];
            const ariaLabel = ariaByState[nextState];

            const previousTimer = Number(button.dataset.resetTimer || 0);
            if (previousTimer) {
                window.clearTimeout(previousTimer);
            }

            button.innerHTML = `<i data-lucide="${icon}" class="vee-icon-btn"></i>`;
            button.setAttribute('aria-label', ariaLabel);
            button.setAttribute('title', ariaLabel);
            this.refreshLucide();

            if (nextState === 'default') {
                button.dataset.resetTimer = '';
                return;
            }

            const timer = window.setTimeout(() => {
                this.setCopyButtonState(button, 'default');
            }, 1400);
            button.dataset.resetTimer = String(timer);
        },

        decorateMessageHtml() {
            const area = this.$refs.messagesArea;
            if (!area) return;

            area.querySelectorAll('.vee-msg-content pre').forEach((pre) => {
                let wrapper = pre.parentElement;
                if (!wrapper || !wrapper.classList.contains('vee-md-code-block')) {
                    wrapper = document.createElement('div');
                    wrapper.className = 'vee-md-code-block';
                    pre.replaceWith(wrapper);
                    wrapper.appendChild(pre);
                }

                if (wrapper.querySelector('.vee-md-copy-btn')) {
                    return;
                }

                const btn = document.createElement('button');
                btn.type = 'button';
                btn.className = 'vee-md-copy-btn';
                this.setCopyButtonState(btn, 'default');
                btn.addEventListener('click', async (event) => {
                    event.preventDefault();
                    event.stopPropagation();
                    const codeEl = pre.querySelector('code');
                    const codeText = String(codeEl?.textContent || pre.textContent || '').trimEnd();
                    const copied = await this.copyText(codeText);
                    this.setCopyButtonState(btn, copied ? 'copied' : 'error');
                });
                wrapper.appendChild(btn);
            });
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
