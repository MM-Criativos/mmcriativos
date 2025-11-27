@php
    $skillKey = $task->skill_id ? (string) $task->skill_id : 'sem-skill';
    $badge = $statusBadges[$task->status] ?? [
        'label' => ucfirst($task->status),
        'classes' => 'bg-gray-100 text-gray-800 border border-gray-200',
    ];
@endphp

<div x-data="{ modalOpen: false, expanded: false }" @keydown.escape.window="modalOpen = false"
    class="bg-[#f5f5f5] dark:bg-[#262626] border border-gray-200 rounded-lg p-4 shadow-sm"
    data-project-task-card="{{ $task->id }}"
    data-task-skill="{{ $skillKey }}"
    data-task-status="{{ $task->status }}">
    <div class="flex flex-col gap-3 md:flex-row md:items-start md:justify-between">
        <div class="space-y-1">
            <div class="flex items-center gap-2 flex-wrap">
                <p class="font-semibold text-gray-900">{{ $task->title }}</p>
                <button type="button" @click="modalOpen = true"
                    class="btn-mmcriativos text-xs inline-flex items-center px-3 py-3 rounded-md gap-1">
                    <i class="fa-duotone fa-solid fa-pen-to-square icon-project"></i>
                </button>
                <button type="button"
                    class="inline-flex items-center gap-1 rounded-md border border-gray-300 px-3.5 py-3.5 text-xs text-gray-600 hover:border-orange-500 hover:text-orange-600"
                    @click.stop="expanded = !expanded">
                    <i class="fa-solid" :class="expanded ? 'fa-chevron-up' : 'fa-chevron-down'"></i>
                </button>
            </div>
            <p class="text-sm text-gray-600">{{ $task->description ?? 'Sem descrição' }}</p>
            <p class="text-xs text-gray-500">
                {{ $task->competency?->competency ?? 'Competência não definida' }}
            </p>
        </div>
        <span
            class="inline-flex items-center justify-center min-w-[110px] px-4 py-1.5 rounded-full text-xs font-medium text-center whitespace-nowrap {{ $badge['classes'] }}"
            data-task-status-badge>
            {{ $badge['label'] }}
        </span>
    </div>

    <div x-show="expanded" x-collapse
        class="mt-3 grid grid-cols-1 md:grid-cols-4 gap-4 text-sm text-gray-600">
        <div>
            <p class="text-xs uppercase tracking-widest text-gray-400">Responsável</p>
            <p class="font-medium text-gray-800">
                {{ $task->assignedUser?->name ?? 'Não atribuído' }}
            </p>
        </div>
        <div>
            <p class="text-xs uppercase tracking-widest text-gray-400">Atualizado em</p>
            <p>{{ optional($task->updated_at)->format('d/m/Y H:i') ?? '—' }}</p>
        </div>
        <div>
            <p class="text-xs uppercase tracking-widest text-gray-400">Status interno</p>
            <p>{{ $badge['label'] }}</p>
        </div>
        <div>
            <p class="text-xs uppercase tracking-widest text-gray-400">Data final planejada</p>
            <p>{{ optional($task->planned_at)->format('d/m/Y') ?? 'Sem previsão' }}</p>
            @if ($task->planned_at && !$task->isCompleted() && $task->planned_at->isPast())
                <span class="inline-flex items-center mt-1 px-2 py-0.5 rounded-full text-xs font-semibold badge-pendent">
                    Em atraso
                </span>
            @endif
        </div>
    </div>

    <div x-show="expanded" x-collapse class="mt-4 flex flex-wrap gap-3 justify-end">
        <div data-task-completion-section class="flex items-center gap-3">
            @if (!$task->isCompleted())
                <form method="POST" action="{{ route('admin.project-tasks.complete', $task) }}"
                    data-ajax="complete-task">
                    @csrf
                    <button type="submit"
                        class="btn-mmcriativos inline-flex items-center gap-2 px-4 py-2.5 rounded-md text-sm font-medium"
                        data-task-complete-button>
                        <i class="fa-duotone fa-solid fa-circle-check icon-project"></i>
                        Finalizar tarefa
                    </button>
                </form>
            @else
                <span
                    class="inline-flex items-center gap-2 px-3 py-1.5 rounded-md text-sm font-medium bg-green-700 text-white border-green-200">
                    <i class="fa-solid fa-flag-checkered"></i>
                    Concluída em {{ optional($task->completed_at)->format('d/m/Y H:i') ?? 'data não registrada' }}
                </span>
            @endif
        </div>

        <form method="POST" action="{{ route('admin.project-tasks.destroy', $task) }}"
            onsubmit="return confirm('Deseja remover esta tarefa? Esta ação não pode ser desfeita.');">
            @csrf
            @method('DELETE')
            <button type="submit"
                class="inline-flex items-center gap-2 rounded-md text-sm font-medium w-full px-4 py-3 bg-red-500 text-white border-red-500 hover:bg-white dark:hover:bg-black hover:text-red-500">
                <i class="fa-solid fa-trash"></i>
                Excluir tarefa
            </button>
        </form>
    </div>

    <div x-show="expanded" x-collapse class="mt-4 space-y-2">
        <p class="text-xs uppercase tracking-widest text-gray-400">Itens da tarefa</p>
        @if ($task->items->isEmpty())
            <div class="border border-dashed border-gray-200 rounded-md p-4 text-sm text-gray-500">
                Nenhuma subtarefa cadastrada.
            </div>
        @else
            <div class="space-y-2">
                @foreach ($task->items as $item)
                    @php
                        $itemDoneLabel = $item->done_at ? $item->done_at->format('d/m/Y H:i') : 'data não registrada';
                        $itemCompetency =
                            $item->competency?->competency ??
                            ($task->competency?->competency ?? 'Competência não definida');
                        $inheritedCompetency = !$item->competency && $task->competency;
                    @endphp
                    <div class="border border-gray-200 rounded-lg p-3 bg-white dark:bg-black"
                        data-item-card="{{ $item->id }}">
                        <div class="flex flex-col gap-3 md:flex-row md:items-start md:justify-between">
                            <div class="space-y-1">
                                <p class="text-sm font-medium text-gray-900">{{ $item->title }}</p>
                                <p class="text-xs text-gray-500">
                                    {{ $itemCompetency }}
                                    @if ($inheritedCompetency)
                                        <span class="ml-1 uppercase tracking-widest text-[10px] text-gray-400">(herdada)</span>
                                    @endif
                                    &bull;
                                    {{ $item->assignedUser?->name ?? 'Sem responsável' }}
                                </p>
                                @if ($item->description)
                                    <p class="text-sm text-gray-600">{{ $item->description }}</p>
                                @endif
                                <p data-item-done-text class="text-xs text-green-600"
                                    style="{{ $item->is_done ? '' : 'display:none;' }}">
                                    Finalizado em {{ $itemDoneLabel }}
                                </p>
                            </div>
                            <div class="flex flex-col items-end gap-2 shrink-0">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium {{ $item->is_done ? 'bg-green-800 text-white' : 'bg-red-500 text-white' }}"
                                    data-item-badge>
                                    {{ $item->is_done ? 'Concluído' : 'Pendente' }}
                                </span>
                                <form method="POST" action="{{ route('admin.project-task-items.toggle', $item) }}"
                                    data-ajax="toggle-item" data-item-id="{{ $item->id }}">
                                    @csrf
                                    <button type="submit"
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-md text-xs font-medium border transition-colors {{ $item->is_done ? 'border-[#ff8800] text-[#ff8800] bg-white hover:bg-[#ff8800] hover:text-white' : 'border-green-800 text-green-800 bg-green-50 hover:bg-green-800 hover:text-white' }}"
                                        data-item-toggle-btn>
                                        <i class="fa-solid {{ $item->is_done ? 'fa-rotate-left' : 'fa-check' }}"></i>
                                        {{ $item->is_done ? 'Reabrir' : 'Finalizar' }}
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <div x-show="expanded" x-collapse>
        @include('admin.projects.steps.development.edit', [
            'task' => $task,
            'teamMembers' => $teamMembers,
            'skillOptions' => $skillOptions,
        ])
    </div>
</div>
