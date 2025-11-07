@php
    $teamMembers = $teamMembers instanceof \Illuminate\Support\Collection ? $teamMembers : collect($teamMembers ?? []);
    $skillOptions =
        $skillOptions instanceof \Illuminate\Support\Collection ? $skillOptions : collect($skillOptions ?? []);

    $statusBadges = [
        \App\Models\ProjectTask::STATUS_PENDING => [
            'label' => "N\u{00E3}o iniciado",
            'classes' => 'bg-red-100 text-red-800 border border-red-200',
        ],
        \App\Models\ProjectTask::STATUS_IN_PROGRESS => [
            'label' => 'Em progresso',
            'classes' => 'bg-yellow-100 text-yellow-800 border border-yellow-200',
        ],
        \App\Models\ProjectTask::STATUS_DONE => [
            'label' => 'Completo',
            'classes' => 'bg-green-100 text-green-800 border border-green-200',
        ],
    ];

    $statusTabs = [
        \App\Models\ProjectTask::STATUS_IN_PROGRESS => 'Em progresso',
        \App\Models\ProjectTask::STATUS_PENDING => "N\u{00E3}o iniciado",
        \App\Models\ProjectTask::STATUS_DONE => 'Completo',
    ];

    $taskGroups = $project->tasks->groupBy(fn($task) => $task->skill_id ?? 'sem-skill');
    $canCreateTasks = $skillOptions->isNotEmpty();
@endphp

<section class="space-y-8" x-data="{ createTaskModal: {{ $errors->hasBag('projectTasksStore') ? 'true' : 'false' }} }">
    <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
        <div>
            <h3 class="text-lg font-semibold text-gray-800">Tarefas de desenvolvimento</h3>
            <p class="text-sm text-gray-500">Cadastre e organize as entregas por skill e acompanhe o status.</p>
            @unless ($canCreateTasks)
                <p class="text-xs text-orange-600 mt-1">
                    Cadastre skills e compet&ecirc;ncias no m&oacute;dulo Skills para liberar o cadastro de tarefas.
                </p>
            @endunless
        </div>
        <div class="flex items-center gap-3">

            <span class="text-sm text-gray-500">
                {{ $project->tasks->count() }} {{ \Illuminate\Support\Str::plural('tarefa', $project->tasks->count()) }}
            </span>
            <button type="button" @class([
                'inline-flex items-center gap-2 px-4 py-2 rounded-md border border-orange-600 text-orange-600 hover:bg-orange-50 transition-colors',
                'opacity-60 cursor-not-allowed' => !$canCreateTasks,
            ]) @click="createTaskModal = true"
                @if (!$canCreateTasks) disabled title="Cadastre skills e competências primeiro." @endif>
                <i class="fa-solid fa-plus"></i>
                Criar tarefa
            </button>
            <a href="{{ route('admin.project-tasks.completed', ['project_id' => $project->id]) }}"
                class="inline-flex items-center gap-2 px-4 py-2 rounded-md border border-gray-300 text-gray-700 hover:bg-gray-50 transition-colors">
                <i class="fa-solid fa-list-check"></i>
                Tarefas conclu&iacute;das
            </a>
        </div>
    </div>

    @include('admin.projects.steps.develepoment.create', [
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

            <div x-data="{ open: true, tab: 'in_progress' }" class="border rounded-lg bg-white overflow-hidden">
                <button @click="open = !open"
                    class="w-full px-4 py-3 flex items-center justify-between bg-gray-50 hover:bg-gray-100">
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
                                    :class="tab === '{{ $statusKey }}' ? 'text-orange-600 border-orange-600' :
                                        'text-gray-500 border-transparent hover:text-gray-700'"
                                    @click="tab = '{{ $statusKey }}'">
                                    {{ $label }}
                                    <span class="ml-1 text-xs text-gray-400">{{ $statusCount }}</span>
                                </button>
                            @endforeach
                        </div>

                        @foreach ($statusTabs as $statusKey => $label)
                            @php
                                $statusTasks = $tasksByStatus->get($statusKey, collect())
                                    ->sortBy(function ($task) {
                                        $isLate = $task->planned_at && $task->planned_at->isPast() && $task->status !== \App\Models\ProjectTask::STATUS_DONE;
                                        $plannedTimestamp = $task->planned_at ? $task->planned_at->timestamp : PHP_INT_MAX;
                                        return [
                                            $isLate ? 0 : 1,
                                            $plannedTimestamp,
                                            $task->id,
                                        ];
                                    })
                                    ->values();
                            @endphp
                            <div x-show="tab === '{{ $statusKey }}'">
                                <div class="space-y-4 py-4">
                                    @forelse ($statusTasks as $task)
                                        <div x-data="{ modalOpen: false }" @keydown.escape.window="modalOpen = false"
                                            class="bg-white border border-gray-200 rounded-lg p-4 shadow-sm">
                                            <div
                                                class="flex flex-col gap-3 md:flex-row md:items-start md:justify-between">
                                                <div class="space-y-1">
                                                    <div class="flex items-center gap-2 flex-wrap">
                                                        <p class="font-semibold text-gray-900">{{ $task->title }}</p>
                                                        <button type="button" @click="modalOpen = true"
                                                            class="text-xs inline-flex items-center gap-1 text-orange-600 hover:text-orange-700">
                                                            <i class="fa-solid fa-pen-to-square"></i>
                                                            Editar
                                                        </button>
                                                    </div>
                                                    <p class="text-sm text-gray-600">
                                                        {{ $task->description ?? "Sem descri\u{00E7}\u{00E3}o" }}</p>
                                                    <p class="text-xs text-gray-500">
                                                        {{ $task->competency?->competency ?? "Compet\u{00EA}ncia n\u{00E3}o definida" }}
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

                                            <div
                                                class="mt-3 grid grid-cols-1 md:grid-cols-4 gap-4 text-sm text-gray-600">
                                                <div>
                                                    <p class="text-xs uppercase tracking-widest text-gray-400">
                                                        Responsável</p>
                                                    <p class="font-medium text-gray-800">
                                                        {{ $task->assignedUser?->name ?? "N\u{00E3}o atrib\u{00ED}do" }}
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
                                                    @if ($task->planned_at && ! $task->isCompleted() && $task->planned_at->isPast())
                                                        <span
                                                            class="inline-flex items-center mt-1 px-2 py-0.5 rounded-full text-xs font-semibold border border-red-200 text-red-700 bg-red-50">
                                                            Em atraso
                                                        </span>
                                                    @endif
                                                </div>
                                            </div>

                                            <div class="mt-4 flex flex-wrap gap-3 justify-end">
                                                @if (! $task->isCompleted())
                                                    <form method="POST" action="{{ route('admin.project-tasks.complete', $task) }}">
                                                        @csrf
                                                        <button type="submit"
                                                            class="inline-flex items-center gap-2 px-4 py-2 rounded-md text-sm font-medium border border-green-600 text-green-700 hover:bg-green-50">
                                                            <i class="fa-solid fa-check"></i>
                                                            Finalizar tarefa
                                                        </button>
                                                    </form>
                                                @else
                                                    <span
                                                        class="inline-flex items-center gap-2 px-3 py-1.5 rounded-md text-sm font-medium bg-green-100 text-green-700 border border-green-200">
                                                        <i class="fa-solid fa-flag-checkered"></i>
                                                        Concluída em {{ optional($task->completed_at)->format('d/m/Y H:i') ?? 'data não registrada' }}
                                                    </span>
                                                @endif

                                                <form method="POST" action="{{ route('admin.project-tasks.destroy', $task) }}"
                                                    onsubmit="return confirm('Deseja remover esta tarefa? Esta ação não pode ser desfeita.');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit"
                                                        class="inline-flex items-center gap-2 px-4 py-2 rounded-md text-sm font-medium border border-red-600 text-red-700 hover:bg-red-50">
                                                        <i class="fa-solid fa-trash"></i>
                                                        Excluir tarefa
                                                    </button>
                                                </form>
                                            </div>

                                            <div class="mt-4 space-y-2">
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
                                                                class="border border-gray-200 rounded-lg p-3 bg-white/60">
                                                                <div
                                                                    class="flex flex-col gap-3 md:flex-row md:items-start md:justify-between">
                                                                    <div class="space-y-1">
                                                                        <p class="text-sm font-medium text-gray-900">
                                                                            {{ $item->title }}</p>
                                                                        @php
                                                                            $itemCompetency =
                                                                                $item->competency?->competency ??
                                                                                ($task->competency?->competency ??
                                                                                    'Compet&ecirc;ncia n&atilde;o definida');
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
                                                                            {{ $item->assignedUser?->name ?? 'Sem respons&aacute;vel' }}
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
                                                                            class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium {{ $item->is_done ? 'bg-green-100 text-green-700 border border-green-200' : 'bg-gray-100 text-gray-700 border border-gray-200' }}">
                                                                            {{ $item->is_done ? 'Concluído' : 'Pendente' }}
                                                                        </span>
                                                                        <form method="POST"
                                                                            action="{{ route('admin.project-task-items.toggle', $item) }}">
                                                                            @csrf
                                                                            <button type="submit"
                                                                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-md text-xs font-medium border transition-colors {{ $item->is_done ? 'border-gray-300 text-gray-600 hover:bg-gray-50' : 'border-green-600 text-green-700 hover:bg-green-50' }}">
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

                                            @include('admin.projects.steps.develepoment.edit', [
                                                'task' => $task,
                                                'teamMembers' => $teamMembers,
                                                'skillOptions' => $skillOptions,
                                            ])
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
