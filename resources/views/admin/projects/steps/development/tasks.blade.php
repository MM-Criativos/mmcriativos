<style>
    .badge-inprogress {
        background-color: #ff8800;
        color: #fff;
    }

    .badge-completed {
        background-color: #008800;
        color: #fff;
    }

    .badge-pendent {
        background-color: #ff0000;
        color: #fff;
    }
</style>

@php
    $teamMembers = $teamMembers instanceof \Illuminate\Support\Collection ? $teamMembers : collect($teamMembers ?? []);
    $skillOptions =
        $skillOptions instanceof \Illuminate\Support\Collection ? $skillOptions : collect($skillOptions ?? []);

    $statusBadges = [
        \App\Models\ProjectTask::STATUS_PENDING => [
            'label' => 'Não iniciado',
            'classes' => 'badge-pendent',
        ],
        \App\Models\ProjectTask::STATUS_IN_PROGRESS => [
            'label' => 'Em progresso',
            'classes' => 'badge-inprogress',
        ],
        \App\Models\ProjectTask::STATUS_DONE => [
            'label' => 'Completo',
            'classes' => 'badge-completed',
        ],
    ];

    $statusTabs = [
        \App\Models\ProjectTask::STATUS_IN_PROGRESS => 'Em progresso',
        \App\Models\ProjectTask::STATUS_PENDING => 'Não iniciado',
        \App\Models\ProjectTask::STATUS_DONE => 'Completo',
    ];

    $taskGroups = $project->tasks->groupBy(fn($task) => $task->skill_id ?? 'sem-skill');
    $canCreateTasks = $skillOptions->isNotEmpty();
@endphp

<section class="space-y-8" x-data="{ createTaskModal: {{ $errors->hasBag('projectTasksStore') ? 'true' : 'false' }} }">
    <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
        <div>
            <h3 class="text-lg font-semibold text-gray-800">Tarefas de desenvolvimento</h3>
            <p class="text-sm text-gray-500">Cadastre e organize as entregas por áreas e acompanhe o status.</p>
            @unless ($canCreateTasks)
                <p class="text-xs text-orange-600 mt-1">
                    Cadastre skills e competências no módulo Habilidades para liberar o cadastro de tarefas.
                </p>
            @endunless
        </div>
        <div class="flex items-center gap-3">

            <span class="text-sm text-gray-500">
                {{ $project->tasks->count() }} {{ \Illuminate\Support\Str::plural('tarefa', $project->tasks->count()) }}
            </span>
            <button type="button" @class([
                'btn-mmcriativos inline-flex items-center gap-2 px-4 py-2 rounded-md border',
                'opacity-60 cursor-not-allowed' => !$canCreateTasks,
            ]) @click="createTaskModal = true"
                @if (!$canCreateTasks) disabled title="Cadastre skills e competências primeiro." @endif>
                <i class="fa-duotone fa-solid fa-circle-plus icon-project"></i>
                Criar tarefa
            </button>
            <a href="{{ route('admin.project-tasks.completed', ['project_id' => $project->id]) }}"
                class="inline-flex items-center gap-2 px-4 py-2 rounded-md border btn-mmcriativos">
                <i class="fa-duotone fa-solid fa-list-check icon-project"></i>
                Tarefas concluídas
            </a>
        </div>
    </div>

    @include('admin.projects.steps.development.create', [
        'project' => $project,
        'skillOptions' => $skillOptions,
        'teamMembers' => $teamMembers,
    ])

    <div class="space-y-4">
        <div>
            <h4 class="text-base font-semibold text-gray-800">Tarefas por skill</h4>
            <p class="text-sm text-gray-500">Use os accordions para alternar entre as &aacute;reas e os status.</p>
        </div>

        @forelse ($taskGroups as $skillKey => $tasks)
            @php
                $firstTask = $tasks->first();
                $skillName = $firstTask?->skill?->name ?? 'Sem skill vinculada';
                $tasksByStatus = $tasks->groupBy('status');
            @endphp

            <div x-data="{ open: true, tab: 'in_progress' }"
                class="border rounded-lg bg-white dark:bg-black dark:border-[#262626] overflow-hidden">
                <button type="button" @click="open = !open"
                    class="w-full px-4 py-3 flex items-center justify-between bg-[#f5f5f5] dark:bg-[#262626]">
                    <div>
                        <h5 class="text-base font-semibold text-gray-800">{{ $skillName }}</h5>
                        <p class="text-xs text-gray-500 text-left">
                            {{ $tasks->count() }} {{ \Illuminate\Support\Str::plural('tarefa', $tasks->count()) }}
                        </p>

                    </div>
                    <i class="fa-solid" :class="{ 'fa-chevron-down': !open, 'fa-chevron-up': open }"></i>
                </button>

                <div x-show="open" x-collapse>
                    <div class="px-4 pt-4">
                        <div class="flex flex-wrap gap-4 border-b border-gray-200">
                            @foreach ($statusTabs as $statusKey => $label)
                                @php $statusCount = $tasksByStatus->get($statusKey, collect())->count(); @endphp
                                <button type="button" class="pb-2 text-sm font-medium border-b-2 transition-colors"
                                    :class="tab === '{{ $statusKey }}' ? 'text-[#ff8800] border-[#ff8800]' :
                                        'text-gray-500 border-transparent hover:text-gray-700'"
                                    @click="tab = '{{ $statusKey }}'">
                                    {{ $label }}
                                    <span class="ml-1 text-xs text-gray-400">{{ $statusCount }}</span>
                                </button>
                            @endforeach
                        </div>

                        @foreach ($statusTabs as $statusKey => $label)
                            @php
                                $statusTasks = $tasksByStatus
                                    ->get($statusKey, collect())
                                    ->sortBy(function ($task) {
                                        $isLate =
                                            $task->planned_at &&
                                            $task->planned_at->isPast() &&
                                            $task->status !== \App\Models\ProjectTask::STATUS_DONE;
                                        $plannedTimestamp = $task->planned_at
                                            ? $task->planned_at->timestamp
                                            : PHP_INT_MAX;
                                        return [$isLate ? 0 : 1, $plannedTimestamp, $task->id];
                                    })
                                    ->values();
                            @endphp
                            <div x-show="tab === '{{ $statusKey }}'">
                                <div class="space-y-4 py-4">
                                    @forelse ($statusTasks as $task)
                                        <div x-data="{ modalOpen: false, expanded: false }" @keydown.escape.window="modalOpen = false"
                                            class="bg-[#f5f5f5] dark:bg-[#262626] border border-gray-200 rounded-lg p-4 shadow-sm">
                                            <div
                                                class="flex flex-col gap-3 md:flex-row md:items-start md:justify-between">
                                                <div class="space-y-1">
                                                    <div class="flex items-center gap-2 flex-wrap">
                                                        <p class="font-semibold text-gray-900">{{ $task->title }}</p>
                                                        <button type="button" @click="modalOpen = true"
                                                            class="btn-mmcriativos text-xs inline-flex items-center px-3 py-3 rounded-md gap-1">
                                                            <i
                                                                class="fa-duotone fa-solid fa-pen-to-square icon-project"></i>
                                                        </button>
                                                        <button type="button"
                                                            class="inline-flex items-center gap-1 rounded-md border border-gray-300 px-3.5 py-3.5 text-xs text-gray-600 hover:border-orange-500 hover:text-orange-600"
                                                            @click.stop="expanded = !expanded">
                                                            <i class="fa-solid"
                                                                :class="expanded ? 'fa-chevron-up' : 'fa-chevron-down'"></i>
                                                        </button>
                                                    </div>
                                                    <p class="text-sm text-gray-600">
                                                        {{ $task->description ?? 'Sem descrição' }}</p>
                                                    <p class="text-xs text-gray-500">
                                                        {{ $task->competency?->competency ?? 'Competência não definida' }}
                                                    </p>
                                                </div>
                                                @php
                                                    $badge = $statusBadges[$task->status] ?? [
                                                        'label' => ucfirst($task->status),
                                                        'classes' => 'bg-gray-100 text-gray-800 border border-gray-200',
                                                    ];
                                                @endphp
                                                <span
                                                    class="inline-flex items-center justify-center min-w-[110px] px-4 py-1.5 rounded-full text-xs font-medium text-center whitespace-nowrap {{ $badge['classes'] }}">
                                                    {{ $badge['label'] }}
                                                </span>

                                            </div>

                                            <div x-show="expanded" x-collapse
                                                class="mt-3 grid grid-cols-1 md:grid-cols-4 gap-4 text-sm text-gray-600">
                                                <div>
                                                    <p class="text-xs uppercase tracking-widest text-gray-400">
                                                        Responsável</p>
                                                    <p class="font-medium text-gray-800">
                                                        {{ $task->assignedUser?->name ?? 'Não atribuído' }}
                                                    </p>
                                                </div>
                                                <div>
                                                    <p class="text-xs uppercase tracking-widest text-gray-400">
                                                        Atualizado em</p>
                                                    <p>{{ optional($task->updated_at)->format('d/m/Y H:i') ?? '—' }}
                                                    </p>
                                                </div>
                                                <div>
                                                    <p class="text-xs uppercase tracking-widest text-gray-400">Status
                                                        interno</p>
                                                    <p>{{ $badge['label'] }}</p>
                                                </div>
                                                <div>
                                                    <p class="text-xs uppercase tracking-widest text-gray-400">Data
                                                        final planejada</p>
                                                    <p>
                                                        {{ optional($task->planned_at)->format('d/m/Y') ?? 'Sem previs\u{00E3}o' }}
                                                    </p>
                                                    @if ($task->planned_at && !$task->isCompleted() && $task->planned_at->isPast())
                                                        <span
                                                            class="inline-flex items-center mt-1 px-2 py-0.5 rounded-full text-xs font-semibold badge-pendent">
                                                            Em atraso
                                                        </span>
                                                    @endif
                                                </div>
                                            </div>

                                            <div x-show="expanded" x-collapse
                                                class="mt-4 flex flex-wrap gap-3 justify-end">
                                                @if (!$task->isCompleted())
                                                    <form method="POST"
                                                        action="{{ route('admin.project-tasks.complete', $task) }}">
                                                        @csrf
                                                        <button type="submit"
                                                            class="btn-mmcriativos inline-flex items-center gap-2 px-4 py-2.5 rounded-md text-sm font-medium">
                                                            <i
                                                                class="fa-duotone fa-solid fa-circle-check icon-project"></i>
                                                            Finalizar tarefa
                                                        </button>
                                                    </form>
                                                @else
                                                    <span
                                                        class="inline-flex items-center gap-2 px-3 py-1.5 rounded-md text-sm font-medium bg-green-700 text-white border-green-200">
                                                        <i class="fa-solid fa-flag-checkered"></i>
                                                        Concluída em
                                                        {{ optional($task->completed_at)->format('d/m/Y H:i') ?? 'data não registrada' }}
                                                    </span>
                                                @endif

                                                <form method="POST"
                                                    action="{{ route('admin.project-tasks.destroy', $task) }}"
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
                                                <p class="text-xs uppercase tracking-widest text-gray-400">Itens da
                                                    tarefa</p>
                                                @if ($task->items->isEmpty())
                                                    <div
                                                        class="border border-dashed border-gray-200 rounded-md p-4 text-sm text-gray-500">
                                                        Nenhuma subtarefa cadastrada.
                                                    </div>
                                                @else
                                                    <div class="space-y-2">
                                                        @foreach ($task->items as $item)
                                                            <div
                                                                class="border border-gray-200 rounded-lg p-3 bg-white dark:bg-black">
                                                                <div
                                                                    class="flex flex-col gap-3 md:flex-row md:items-start md:justify-between">
                                                                    <div class="space-y-1">
                                                                        <p class="text-sm font-medium text-gray-900">
                                                                            {{ $item->title }}</p>
                                                                        @php
                                                                            $itemCompetency =
                                                                                $item->competency?->competency ??
                                                                                ($task->competency?->competency ??
                                                                                    'Competência não definida');
                                                                            $inheritedCompetency =
                                                                                !$item->competency && $task->competency;
                                                                        @endphp
                                                                        <p class="text-xs text-gray-500">
                                                                            {{ $itemCompetency }}
                                                                            @if ($inheritedCompetency)
                                                                                <span
                                                                                    class="ml-1 uppercase tracking-widest text-[10px] text-gray-400">(herdada)</span>
                                                                            @endif
                                                                            &bull;
                                                                            {{ $item->assignedUser?->name ?? 'Sem responsável' }}
                                                                        </p>
                                                                        @if ($item->description)
                                                                            <p class="text-sm text-gray-600">
                                                                                {{ $item->description }}</p>
                                                                        @endif
                                                                        @if ($item->is_done && $item->done_at)
                                                                            <p class="text-xs text-green-600">
                                                                                Finalizado em
                                                                                {{ $item->done_at->format('d/m/Y H:i') }}
                                                                            </p>
                                                                        @endif
                                                                    </div>
                                                                    <div class="flex flex-col items-end gap-2 shrink-0">
                                                                        <span
                                                                            class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium {{ $item->is_done ? 'bg-green-800 text-white' : 'bg-red-500 text-white' }}">
                                                                            {{ $item->is_done ? 'Concluído' : 'Pendente' }}
                                                                        </span>
                                                                        <form method="POST"
                                                                            action="{{ route('admin.project-task-items.toggle', $item) }}">
                                                                            @csrf
                                                                            <button type="submit"
                                                                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-md text-xs font-medium border transition-colors {{ $item->is_done ? 'border-[#ff8800] text-[#ff8800] bg-white hover:bg-[#ff8800] hover:text-white' : 'border-green-800 text-green-800 bg-green-50 hover:bg-green-800 hover:text-white' }}">
                                                                                <i
                                                                                    class="fa-solid {{ $item->is_done ? 'fa-rotate-left' : 'fa-check' }}"></i>
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
                                    @empty
                                        <div
                                            class="border border-dashed border-gray-200 rounded-md p-4 text-sm text-gray-500">
                                            Nenhuma tarefa marcada como {{ mb_strtolower($label, 'UTF-8') }} para esta
                                            skill.
                                        </div>
                                    @endforelse
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @empty
                <div class="border border-dashed border-gray-300 rounded-lg p-6 text-center text-gray-500">
                    Ainda n&atilde;o existem tarefas cadastradas para este projeto.
                </div>
            @endforelse
        </div>
    </section>
