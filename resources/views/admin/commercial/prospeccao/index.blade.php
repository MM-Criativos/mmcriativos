@php
    $title = 'Prospecção';
    $subTitle = 'Controle de leads para o MM Cloud';

    $statusColors = [
        'nao_iniciado' => 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300',
        'em_andamento' => 'bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300',
        'concluido' => 'bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-300',
    ];

    $etapaColors = [
        'novo_lead' => 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300',
        'iniciado' => 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/40 dark:text-yellow-300',
        'respondeu' => 'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300',
        'engajado' => 'bg-orange-100 text-orange-700 dark:bg-orange-900/40 dark:text-orange-300',
        'dor_identificado' => 'bg-slate-200 text-slate-700 dark:bg-slate-700 dark:text-slate-300',
        'qualificado' => 'bg-teal-100 text-teal-700 dark:bg-teal-900/40 dark:text-teal-300',
        'reuniao_agendada' => 'bg-purple-100 text-purple-700 dark:bg-purple-900/40 dark:text-purple-300',
        'follow_up' => 'bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300',
        'perdido' => 'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300',
    ];

    $etapaOrder = array_keys($etapaColors);

    // Cores sólidas para os dots do dropdown (mais vivas que as do badge)
    $statusDots = [
        'nao_iniciado' => 'bg-gray-400',
        'em_andamento' => 'bg-blue-500',
        'concluido'    => 'bg-green-500',
    ];

    $etapaDots = [
        'novo_lead'        => 'bg-gray-400',
        'iniciado'         => 'bg-yellow-400',
        'respondeu'        => 'bg-amber-400',
        'engajado'         => 'bg-orange-500',
        'dor_identificado' => 'bg-slate-500',
        'qualificado'      => 'bg-teal-500',
        'reuniao_agendada' => 'bg-purple-500',
        'follow_up'        => 'bg-blue-500',
        'perdido'          => 'bg-red-500',
    ];

    $isAdmin = auth()->user()->role === 'admin';
@endphp

@extends('layouts.app')

@section('content')
    <div x-data="prospeccao()" x-init="init()">

        {{-- ─── Flash ─────────────────────────────────────────────── --}}
        @if (session('status'))
            <div class="mb-4 p-3 bg-green-100 text-green-800 rounded">{{ session('status') }}</div>
        @endif

        <div class="bg-white dark:bg-black overflow-hidden shadow-sm sm:rounded-lg">

            {{-- ─── Cabeçalho ──────────────────────────────────────── --}}
            <div class="px-6 py-6 border-b border-neutral-300 dark:border-neutral-800 flex items-center justify-between">
                <div>
                    <h6 class="text-lg font-semibold text-neutral-800 dark:text-white">Prospecção MM Cloud</h6>
                    <p class="text-sm text-neutral-500 dark:text-neutral-400 mt-1">Leads de clientes para o MM Cloud</p>
                </div>
                <div class="flex items-center gap-3">
                    {{-- Tabs Tabela / Kanban --}}
                    <div class="flex rounded-md overflow-hidden border border-neutral-300 dark:border-neutral-700 text-sm">
                        <button @click="view = 'tabela'"
                            :class="view === 'tabela' ? 'bg-orange-500 text-white' :
                                'bg-white dark:bg-neutral-900 text-neutral-600 dark:text-neutral-300 hover:bg-neutral-100 dark:hover:bg-neutral-800'"
                            class="px-4 py-2 transition">
                            <i data-lucide="table" class="inline w-4 h-4 mr-1"></i> Tabela
                        </button>
                        <button @click="view = 'kanban'"
                            :class="view === 'kanban' ? 'bg-orange-500 text-white' :
                                'bg-white dark:bg-neutral-900 text-neutral-600 dark:text-neutral-300 hover:bg-neutral-100 dark:hover:bg-neutral-800'"
                            class="px-4 py-2 transition">
                            <i data-lucide="kanban" class="inline w-4 h-4 mr-1"></i> Kanban
                        </button>
                    </div>

                    {{-- Novo Lead --}}
                    <button @click="openDrawer(null)"
                        class="inline-flex items-center gap-2 px-4 py-2 rounded-md btn-mmcriativos">
                        <i data-lucide="circle-plus" class="icon-project"></i> Novo Lead
                    </button>
                </div>
            </div>

            {{-- ─── Filtros ─────────────────────────────────────────── --}}
            <div class="px-6 pt-5">
                <form method="GET" class="flex flex-wrap gap-3 items-end">

                    {{-- Busca --}}
                    <div class="flex-1 min-w-[160px]">
                        <label class="block text-xs text-gray-500 dark:text-gray-400 mb-1">Buscar</label>
                        <input type="text" name="busca" value="{{ request('busca') }}"
                            placeholder="Empresa ou decisor…"
                            class="w-full text-sm border-gray-300 dark:border-neutral-700 dark:bg-neutral-800 rounded">
                    </div>

                    {{-- Etapa --}}
                    <div class="min-w-[150px]">
                        <label class="block text-xs text-gray-500 dark:text-gray-400 mb-1">Etapa</label>
                        <select name="etapa"
                            class="w-full text-sm border-gray-300 dark:border-neutral-700 dark:bg-neutral-800 rounded">
                            <option value="">Todas</option>
                            @foreach ($etapaOpts as $v => $l)
                                <option value="{{ $v }}" @selected(request('etapa') === $v)>{{ $l }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Status --}}
                    <div class="min-w-[140px]">
                        <label class="block text-xs text-gray-500 dark:text-gray-400 mb-1">Status</label>
                        <select name="status"
                            class="w-full text-sm border-gray-300 dark:border-neutral-700 dark:bg-neutral-800 rounded">
                            <option value="">Todos</option>
                            @foreach ($statusOpts as $v => $l)
                                <option value="{{ $v }}" @selected(request('status') === $v)>{{ $l }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    @if ($isAdmin)
                        {{-- Responsável (admin only) --}}
                        <div class="min-w-[150px]">
                            <label class="block text-xs text-gray-500 dark:text-gray-400 mb-1">Responsável</label>
                            <select name="responsavel_id"
                                class="w-full text-sm border-gray-300 dark:border-neutral-700 dark:bg-neutral-800 rounded">
                                <option value="">Todos</option>
                                @foreach ($users as $u)
                                    <option value="{{ $u->id }}" @selected(request('responsavel_id') == $u->id)>{{ $u->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    @endif

                    <div class="flex gap-2">
                        <button class="px-4 py-2 text-sm rounded-md btn-mmcriativos">Filtrar</button>
                        <a href="{{ route('admin.commercial.prospeccao.index') }}"
                            class="px-4 py-2 text-sm bg-red-500 text-white rounded-md hover:bg-red-600 transition">Limpar</a>
                    </div>
                </form>
            </div>

            {{-- ─── Conteúdo ────────────────────────────────────────── --}}
            <div class="p-6">

                {{-- ═══ TABELA ══════════════════════════════════════════ --}}
                <div x-show="view === 'tabela'">
                    @if ($leads->isEmpty())
                        <div class="py-16 text-center text-gray-400 dark:text-gray-500">
                            <i data-lucide="inbox" class="mx-auto mb-3 w-10 h-10 opacity-40"></i>
                            <p>Nenhum lead encontrado. Adicione o primeiro!</p>
                        </div>
                    @else
                        <div class="overflow-x-auto w-full">
                            <table class="w-full table-auto text-sm">
                                <thead>
                                    <tr
                                        class="text-left text-gray-500 dark:text-gray-400 border-b border-neutral-200 dark:border-neutral-700">
                                        <th class="py-2 pr-3 font-medium">Empresa</th>
                                        <th class="py-2 pr-3 font-medium">Status</th>
                                        <th class="py-2 pr-3 font-medium">Etapa</th>
                                        <th class="py-2 pr-3 font-medium">Responsável</th>
                                        <th class="py-2 pr-3 font-medium">Contato</th>
                                        <th class="py-2 pr-3 font-medium">Seguidores</th>
                                        <th class="py-2 pr-3 font-medium">Decisor</th>
                                        <th class="py-2 pr-3 font-medium">Instagram</th>
                                        <th class="py-2 pr-3 font-medium">Atualizado</th>
                                        <th class="py-2 font-medium">Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($leads as $lead)
                                    @php
                                    $canEdit = $isAdmin || $lead->responsavel_id === auth()->id();
                                    @endphp
                                    <tr class="border-b border-neutral-100 dark:border-neutral-800 hover:bg-neutral-100 dark:hover:bg-neutral-800 transition"
                                    id="lead-row-{{ $lead->id }}">

                                            {{-- Empresa --}}
                                            <td
                                                class="py-3 pr-3 font-medium text-neutral-800 dark:text-white max-w-[180px]">
                                                <button @click="openDrawer({{ $lead->id }})"
                                                    class="text-left hover:text-orange-500 transition truncate w-full">
                                                    {{ $lead->empresa }}
                                                </button>
                                            </td>

                                            {{-- Status (inline click) --}}
                                            <td class="py-3 pr-3">
                                                @if ($canEdit)
                                                    <div x-data="{
                                                        open: false,
                                                        top: 0, left: 0,
                                                        toggle(btn) {
                                                            const r = btn.getBoundingClientRect();
                                                            this.top = r.bottom + 6;
                                                            this.left = r.left;
                                                            this.open = !this.open;
                                                        }
                                                    }">
                                                        <button @click="toggle($el)"
                                                            :class="open ? 'ring-2 ring-orange-400/60' : ''"
                                                            class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold {{ $statusColors[$lead->status->value] }} cursor-pointer hover:opacity-90 transition"
                                                            id="status-btn-{{ $lead->id }}">
                                                            <span id="status-label-{{ $lead->id }}">{{ $lead->status->label() }}</span>
                                                            <i data-lucide="chevron-down" class="w-3 h-3 flex-shrink-0 transition-transform duration-150" :class="open ? 'rotate-180' : ''"></i>
                                                        </button>
                                                        <div x-show="open"
                                                             x-transition:enter="transition ease-out duration-100"
                                                             x-transition:enter-start="opacity-0 scale-95"
                                                             x-transition:enter-end="opacity-100 scale-100"
                                                             x-transition:leave="transition ease-in duration-75"
                                                             x-transition:leave-start="opacity-100 scale-100"
                                                             x-transition:leave-end="opacity-0 scale-95"
                                                             @click.outside="open = false"
                                                             :style="{ position: 'fixed', top: top + 'px', left: left + 'px' }"
                                                             class="z-[9999] w-40 bg-white dark:bg-neutral-900 rounded-xl shadow-xl border border-neutral-200 dark:border-neutral-700 py-1 overflow-hidden">
                                                            @foreach ($statusOpts as $sv => $sl)
                                                                <button
                                                                    @click="updateField({{ $lead->id }}, 'status', '{{ $sv }}'); open = false"
                                                                    class="w-full text-left px-3 py-2 text-xs font-medium hover:bg-neutral-50 dark:hover:bg-neutral-800 transition flex items-center gap-2">
                                                                    <span class="inline-block w-2 h-2 rounded-full flex-shrink-0 {{ $statusDots[$sv] }}"></span>
                                                                    {{ $sl }}
                                                                </button>
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                @else
                                                    <span
                                                        class="inline-flex px-2.5 py-1 rounded-full text-xs font-semibold {{ $statusColors[$lead->status->value] }}">
                                                        {{ $lead->status->label() }}
                                                    </span>
                                                @endif
                                            </td>

                                            {{-- Etapa (inline click) --}}
                                            <td class="py-3 pr-3">
                                                @if ($canEdit)
                                                    <div x-data="{
                                                        open: false,
                                                        top: 0, left: 0,
                                                        toggle(btn) {
                                                            const r = btn.getBoundingClientRect();
                                                            this.top = r.bottom + 6;
                                                            this.left = r.left;
                                                            this.open = !this.open;
                                                        }
                                                    }">
                                                        <button @click="toggle($el)"
                                                            :class="open ? 'ring-2 ring-orange-400/60' : ''"
                                                            class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold {{ $etapaColors[$lead->etapa->value] }} cursor-pointer hover:opacity-90 transition"
                                                            id="etapa-btn-{{ $lead->id }}">
                                                            <span id="etapa-label-{{ $lead->id }}">{{ $lead->etapa->label() }}</span>
                                                            <i data-lucide="chevron-down" class="w-3 h-3 flex-shrink-0 transition-transform duration-150" :class="open ? 'rotate-180' : ''"></i>
                                                        </button>
                                                        <div x-show="open"
                                                             x-transition:enter="transition ease-out duration-100"
                                                             x-transition:enter-start="opacity-0 scale-95"
                                                             x-transition:enter-end="opacity-100 scale-100"
                                                             x-transition:leave="transition ease-in duration-75"
                                                             x-transition:leave-start="opacity-100 scale-100"
                                                             x-transition:leave-end="opacity-0 scale-95"
                                                             @click.outside="open = false"
                                                             :style="{ position: 'fixed', top: top + 'px', left: left + 'px' }"
                                                             class="z-[9999] w-48 bg-white dark:bg-neutral-900 rounded-xl shadow-xl border border-neutral-200 dark:border-neutral-700 py-1 overflow-hidden">
                                                            @foreach ($etapaOpts as $ev => $el)
                                                                <button
                                                                    @click="updateField({{ $lead->id }}, 'etapa', '{{ $ev }}'); open = false"
                                                                    class="w-full text-left px-3 py-2 text-xs font-medium hover:bg-neutral-50 dark:hover:bg-neutral-800 transition flex items-center gap-2">
                                                                    <span class="inline-block w-2 h-2 rounded-full flex-shrink-0 {{ $etapaDots[$ev] }}"></span>
                                                                    {{ $el }}
                                                                </button>
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                @else
                                                    <span
                                                        class="inline-flex px-2.5 py-1 rounded-full text-xs font-semibold {{ $etapaColors[$lead->etapa->value] }}">
                                                        {{ $lead->etapa->label() }}
                                                    </span>
                                                @endif
                                            </td>

                                            {{-- Responsável --}}
                                            <td class="py-3 pr-3 text-gray-700 dark:text-gray-300">
                                                {{ $lead->responsavel?->name ?? '—' }}
                                            </td>

                                            {{-- Contato --}}
                                            <td class="py-3 pr-3 text-gray-700 dark:text-gray-300">
                                                @if ($lead->contato)
                                                    <a href="https://wa.me/{{ preg_replace('/\D/', '', $lead->contato) }}"
                                                        target="_blank"
                                                        class="inline-flex items-center gap-1 text-green-600 hover:text-green-700">
                                                        <i data-lucide="message-circle" class="w-3.5 h-3.5"></i>
                                                        {{ $lead->contato }}
                                                    </a>
                                                @else
                                                    <span class="text-gray-400">—</span>
                                                @endif
                                            </td>

                                            {{-- Seguidores --}}
                                            <td class="py-3 pr-3 text-gray-600 dark:text-gray-400">
                                                {{ $lead->seguidores ?? '—' }}
                                            </td>

                                            {{-- Decisor --}}
                                            <td class="py-3 pr-3 text-gray-700 dark:text-gray-300">
                                                {{ $lead->tomador_de_decisao ?? '—' }}
                                            </td>

                                            {{-- Instagram --}}
                                            <td class="py-3 pr-3">
                                                @if ($lead->instagram_url)
                                                    <a href="{{ $lead->instagram_url }}" target="_blank"
                                                        class="inline-flex items-center gap-1 text-pink-500 hover:text-pink-600 text-xs">
                                                        <i data-lucide="instagram" class="w-3.5 h-3.5"></i>
                                                        Ver perfil
                                                    </a>
                                                @else
                                                    <span class="text-gray-400">—</span>
                                                @endif
                                            </td>

                                            {{-- Atualizado --}}
                                            <td class="py-3 pr-3 text-gray-400 text-xs whitespace-nowrap">
                                                {{ $lead->updated_at->diffForHumans() }}
                                            </td>

                                            {{-- Ações --}}
                                            <td class="py-3">
                                                <div class="flex items-center gap-2">
                                                    {{-- Call SDR --}}
                                                    <button onclick="SdrCall.open({
                                                        leadId:   {{ $lead->id }},
                                                        company:  '{{ addslashes($lead->empresa) }}',
                                                        contact:  '{{ addslashes($lead->tomador_de_decisao ?? '') }}',
                                                        sdrName:  '{{ addslashes(auth()->user()->name) }}'
                                                    })"
                                                        class="inline-flex items-center px-3 py-2 rounded-lg bg-green-700 hover:bg-green-600 text-white transition"
                                                        title="Iniciar Call">
                                                        📞
                                                    </button>

                                                    @if ($canEdit)
                                                        <button @click="openDrawer({{ $lead->id }})"
                                                            class="inline-flex items-center px-3 py-2 rounded-lg btn-mmcriativos"
                                                            title="Editar">
                                                            <i data-lucide="square-pen" class="icon-project"></i>
                                                        </button>
                                                    @endif

                                                    @if ($isAdmin)
                                                        <form method="POST"
                                                            action="{{ route('admin.commercial.prospeccao.destroy', $lead) }}"
                                                            onsubmit="return confirm('Remover este lead?')">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button
                                                                class="inline-flex items-center px-3 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 transition">
                                                                <i data-lucide="trash-2" class="w-4 h-4"></i>
                                                            </button>
                                                        </form>
                                                    @endif
                                                </div>
                                            </td>

                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        {{-- Paginação --}}
                        @if ($leads->hasPages())
                            <div class="mt-4 flex items-center justify-between">
                                <p class="text-xs text-gray-400 dark:text-gray-500">
                                    Exibindo {{ $leads->firstItem() }}–{{ $leads->lastItem() }} de {{ $leads->total() }} leads
                                </p>
                                <div>
                                    {{ $leads->links() }}
                                </div>
                            </div>
                        @endif
                    @endif
                </div>

                {{-- ═══ KANBAN ══════════════════════════════════════════ --}}
                <div x-show="view === 'kanban'" class="overflow-x-auto">
                    <div class="flex gap-4 min-w-max pb-4">
                        @php
                            $etapas = \App\Enums\CloudLeadEtapa::cases();
                        @endphp
                        @foreach ($etapas as $etapa)
                            @php
                                $col = $leads->getCollection()->filter(fn($l) => $l->etapa->value === $etapa->value);
                            @endphp
                            <div class="w-56 flex-shrink-0">
                                {{-- Header da coluna --}}
                                <div class="flex items-center justify-between mb-3">
                                    <span
                                        class="inline-flex px-2 py-1 rounded-full text-xs font-semibold {{ $etapaColors[$etapa->value] }}">
                                        {{ $etapa->label() }}
                                    </span>
                                    <span class="text-xs text-gray-400">{{ $col->count() }}</span>
                                </div>

                                {{-- Cards --}}
                                <div class="space-y-2">
                                    @forelse ($col as $lead)
                                        <div class="bg-white dark:bg-neutral-800 border border-neutral-200 dark:border-neutral-700 rounded-lg p-3 shadow-sm hover:shadow-md transition cursor-pointer"
                                            @click="openDrawer({{ $lead->id }})">
                                            <p class="font-medium text-sm text-neutral-800 dark:text-white truncate mb-1">
                                                {{ $lead->empresa }}</p>
                                            @if ($lead->seguidores)
                                                <p class="text-xs text-gray-400">{{ $lead->seguidores }} seguidores</p>
                                            @endif
                                            @if ($lead->responsavel)
                                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1 truncate">
                                                    <i data-lucide="user" class="inline w-3 h-3"></i>
                                                    {{ $lead->responsavel->name }}
                                                </p>
                                            @endif
                                            <div class="mt-2">
                                                <span
                                                    class="inline-flex px-2 py-0.5 rounded-full text-xs {{ $statusColors[$lead->status->value] }}">
                                                    {{ $lead->status->label() }}
                                                </span>
                                            </div>
                                        </div>
                                    @empty
                                        <div
                                            class="text-center py-6 text-xs text-gray-300 dark:text-gray-600 border-2 border-dashed border-gray-200 dark:border-gray-700 rounded-lg">
                                            Vazio
                                        </div>
                                    @endforelse
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

            </div>
        </div>

        {{-- ═══════════════════════════════════════════════════════════
         DRAWER — Criar / Editar Lead
    ═══════════════════════════════════════════════════════════════ --}}
        <div x-show="drawerOpen" x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0" class="fixed inset-0 z-40 bg-black/50 backdrop-blur-sm"
            @click.self="drawerOpen = false">
        </div>

        <div x-show="drawerOpen" x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="translate-x-full" x-transition:enter-end="translate-x-0"
            x-transition:leave="transition ease-in duration-150" x-transition:leave-start="translate-x-0"
            x-transition:leave-end="translate-x-full"
            class="fixed inset-y-0 right-0 z-50 w-full max-w-lg bg-white dark:bg-neutral-900 shadow-2xl flex flex-col overflow-hidden">

            {{-- Cabeçalho do drawer --}}
            <div class="px-6 py-4 border-b border-neutral-200 dark:border-neutral-700 flex items-center justify-between">
                <h3 class="font-semibold text-neutral-800 dark:text-white text-base"
                    x-text="editingId ? 'Editar Lead' : 'Novo Lead'"></h3>
                <button @click="drawerOpen = false"
                    class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 transition">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            {{-- Formulário --}}
            <div class="flex-1 overflow-y-auto p-6">
                <form
                    :action="editingId
                        ?
                        '{{ url('admin/mmcloud/prospeccao') }}/' + editingId :
                        '{{ route('admin.commercial.prospeccao.store') }}'"
                    method="POST" id="lead-form">
                    @csrf
                    <template x-if="editingId">
                        <input name="_method" type="hidden" value="PATCH">
                    </template>

                    @if ($errors->any())
                        <div
                            class="mb-4 p-3 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded text-sm text-red-700 dark:text-red-300">
                            <ul class="list-disc list-inside space-y-1">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="space-y-4">

                        {{-- Empresa --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Empresa <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="empresa" x-model="form.empresa" required
                                class="w-full text-sm border-gray-300 dark:border-neutral-700 dark:bg-neutral-800 rounded"
                                placeholder="Nome da empresa ou perfil">
                        </div>

                        {{-- Status + Etapa --}}
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    Status <span class="text-red-500">*</span>
                                </label>
                                <select name="status" x-model="form.status" required
                                    class="w-full text-sm border-gray-300 dark:border-neutral-700 dark:bg-neutral-800 rounded">
                                    @foreach ($statusOpts as $v => $l)
                                        <option value="{{ $v }}">{{ $l }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    Etapa <span class="text-red-500">*</span>
                                </label>
                                <select name="etapa" x-model="form.etapa" required
                                    class="w-full text-sm border-gray-300 dark:border-neutral-700 dark:bg-neutral-800 rounded">
                                    @foreach ($etapaOpts as $v => $l)
                                        <option value="{{ $v }}">{{ $l }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        {{-- Responsável --}}
                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Responsável</label>
                            <select name="responsavel_id" x-model="form.responsavel_id"
                                class="w-full text-sm border-gray-300 dark:border-neutral-700 dark:bg-neutral-800 rounded">
                                <option value="">— Selecione —</option>
                                @foreach ($users as $u)
                                    <option value="{{ $u->id }}">{{ $u->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Instagram --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Instagram
                                URL</label>
                            <input type="url" name="instagram_url" x-model="form.instagram_url"
                                class="w-full text-sm border-gray-300 dark:border-neutral-700 dark:bg-neutral-800 rounded"
                                placeholder="https://www.instagram.com/...">
                        </div>

                        {{-- Contato + Seguidores --}}
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label
                                    class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Contato</label>
                                <input type="text" name="contato" x-model="form.contato"
                                    class="w-full text-sm border-gray-300 dark:border-neutral-700 dark:bg-neutral-800 rounded"
                                    placeholder="(11) 99999-9999">
                            </div>
                            <div>
                                <label
                                    class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Seguidores</label>
                                <input type="text" name="seguidores" x-model="form.seguidores"
                                    class="w-full text-sm border-gray-300 dark:border-neutral-700 dark:bg-neutral-800 rounded"
                                    placeholder="Ex: 47 mil">
                            </div>
                        </div>

                        {{-- Tomador de Decisão --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Tomador de
                                Decisão</label>
                            <input type="text" name="tomador_de_decisao" x-model="form.tomador_de_decisao"
                                class="w-full text-sm border-gray-300 dark:border-neutral-700 dark:bg-neutral-800 rounded"
                                placeholder="Nome do decisor">
                        </div>

                        {{-- Descrição --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Descrição /
                                Observações</label>
                            <textarea name="descricao" x-model="form.descricao" rows="4"
                                class="w-full text-sm border-gray-300 dark:border-neutral-700 dark:bg-neutral-800 rounded"
                                placeholder="Notas sobre o lead…"></textarea>
                        </div>

                    </div>
                </form>
            </div>

            {{-- Footer do drawer --}}
            <div class="px-6 py-4 border-t border-neutral-200 dark:border-neutral-700 flex items-center justify-end gap-3">
                <button @click="drawerOpen = false"
                    class="px-4 py-2 text-sm text-gray-600 dark:text-gray-300 hover:text-gray-800 dark:hover:text-white transition">
                    Cancelar
                </button>
                <button type="submit" form="lead-form" class="px-5 py-2 text-sm rounded-md btn-mmcriativos">
                    <span x-text="editingId ? 'Salvar alterações' : 'Adicionar lead'"></span>
                </button>
            </div>

        </div>

        {{-- Dados dos leads para o Alpine --}}
        @php
            $leadsForJs = $leads->getCollection()->keyBy('id')->map(function ($l) {
                return [
                    'id' => $l->id,
                    'empresa' => $l->empresa,
                    'status' => $l->status->value,
                    'etapa' => $l->etapa->value,
                    'responsavel_id' => $l->responsavel_id,
                    'contato' => $l->contato,
                    'seguidores' => $l->seguidores,
                    'tomador_de_decisao' => $l->tomador_de_decisao,
                    'instagram_url' => $l->instagram_url,
                    'descricao' => $l->descricao,
                ];
            });
        @endphp
        <script>
            window.__leads = @json($leadsForJs);

            window.__csrfToken = '{{ csrf_token() }}';

            function prospeccao() {
                return {
                    view: 'tabela',
                    drawerOpen: false,
                    editingId: null,

                    form: {
                        empresa: '',
                        status: 'nao_iniciado',
                        etapa: 'novo_lead',
                        responsavel_id: '',
                        contato: '',
                        seguidores: '',
                        tomador_de_decisao: '',
                        instagram_url: '',
                        descricao: '',
                    },

                    init() {
                        // Reabre o drawer se houver erros de validação
                        @if ($errors->any())
                            this.drawerOpen = true;
                            this.editingId = null;
                            @foreach (old() as $k => $v)
                                @if (in_array($k, [
                                        'empresa',
                                        'status',
                                        'etapa',
                                        'responsavel_id',
                                        'contato',
                                        'seguidores',
                                        'tomador_de_decisao',
                                        'instagram_url',
                                        'descricao',
                                    ]))
                                    this.form['{{ $k }}'] = @json($v);
                                @endif
                            @endforeach
                        @endif

                        this.$nextTick(() => {
                            if (window.lucide) lucide.createIcons();
                        });
                    },

                    openDrawer(id) {
                        if (id && window.__leads[id]) {
                            const l = window.__leads[id];
                            this.editingId = id;
                            this.form.empresa = l.empresa ?? '';
                            this.form.status = l.status ?? 'nao_iniciado';
                            this.form.etapa = l.etapa ?? 'novo_lead';
                            this.form.responsavel_id = l.responsavel_id ?? '';
                            this.form.contato = l.contato ?? '';
                            this.form.seguidores = l.seguidores ?? '';
                            this.form.tomador_de_decisao = l.tomador_de_decisao ?? '';
                            this.form.instagram_url = l.instagram_url ?? '';
                            this.form.descricao = l.descricao ?? '';
                        } else {
                            this.editingId = null;
                            this.form.empresa = '';
                            this.form.status = 'nao_iniciado';
                            this.form.etapa = 'novo_lead';
                            this.form.responsavel_id = '';
                            this.form.contato = '';
                            this.form.seguidores = '';
                            this.form.tomador_de_decisao = '';
                            this.form.instagram_url = '';
                            this.form.descricao = '';
                        }
                        this.drawerOpen = true;
                        this.$nextTick(() => {
                            if (window.lucide) lucide.createIcons();
                        });
                    },

                    async updateField(id, field, value) {
                        const url = `/admin/mmcloud/prospeccao/${id}/field`;
                        try {
                            const res = await fetch(url, {
                                method: 'PATCH',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': window.__csrfToken,
                                    'Accept': 'application/json',
                                },
                                body: JSON.stringify({
                                    field,
                                    value
                                }),
                            });

                            if (!res.ok) return;

                            const data = await res.json();

                            // Atualiza cache local
                            if (window.__leads[id]) {
                                window.__leads[id][field] = value;
                            }

                            // Atualiza o badge de status na linha
                            const statusBtn = document.getElementById(`status-btn-${id}`);
                            if (statusBtn && data.status_label) {
                                statusBtn.className = statusBtn.className.replace(
                                    /bg-\S+\s+text-\S+\s+dark:\S+\s+dark:\S+/g, ''
                                );
                                statusBtn.innerHTML =
                                    `${data.status_label} <i data-lucide="chevron-down" class="w-3 h-3"></i>`;
                            }

                            const etapaBtn = document.getElementById(`etapa-btn-${id}`);
                            if (etapaBtn && data.etapa_label) {
                                etapaBtn.innerHTML =
                                    `${data.etapa_label} <i data-lucide="chevron-down" class="w-3 h-3"></i>`;
                            }

                            if (window.lucide) lucide.createIcons();

                        } catch (e) {
                            console.error('Erro ao atualizar campo:', e);
                        }
                    },
                };
            }
        </script>

    </div>
@include('admin.commercial.prospeccao.call-modal')
@endsection
