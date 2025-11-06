@php
    $teamMembers = $teamMembers instanceof \Illuminate\Support\Collection ? $teamMembers : collect($teamMembers ?? []);
    $skillOptions = $skillOptions instanceof \Illuminate\Support\Collection ? $skillOptions : collect($skillOptions ?? []);

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
            <button type="button"
                @class([
                    'inline-flex items-center gap-2 px-4 py-2 rounded-md border border-orange-600 text-orange-600 hover:bg-orange-50 transition-colors',
                    'opacity-60 cursor-not-allowed' => ! $canCreateTasks,
                ])
                @click="createTaskModal = true"
                @if (!$canCreateTasks) disabled title="Cadastre skills e competências primeiro." @endif>
                <i class="fa-solid fa-plus"></i>
                Criar tarefa
            </button>
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
                        <p class="text-xs text-gray-500">
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
                                <button type="button"
                                    class="pb-2 text-sm font-medium border-b-2 transition-colors"
                                    :class="tab === '{{ $statusKey }}' ? 'text-orange-600 border-orange-600' : 'text-gray-500 border-transparent hover:text-gray-700'"
                                    @click="tab = '{{ $statusKey }}'">
                                    {{ $label }}
                                    <span class="ml-1 text-xs text-gray-400">{{ $statusCount }}</span>
                                </button>
                            @endforeach
                        </div>

                        @foreach ($statusTabs as $statusKey => $label)
                            @php $statusTasks = $tasksByStatus->get($statusKey, collect()); @endphp
                            <div x-show="tab === '{{ $statusKey }}'">
                                <div class="space-y-4 py-4">
                                    @forelse ($statusTasks as $task)
                                        <div x-data="{ modalOpen: false }" @keydown.escape.window="modalOpen = false"
                                            class="bg-white border border-gray-200 rounded-lg p-4 shadow-sm">
                                            <div class="flex flex-col gap-3 md:flex-row md:items-start md:justify-between">
                                                <div class="space-y-1">
                                                    <div class="flex items-center gap-2 flex-wrap">
                                                        <p class="font-semibold text-gray-900">{{ $task->title }}</p>
                                                        <button type="button" @click="modalOpen = true"
                                                            class="text-xs inline-flex items-center gap-1 text-orange-600 hover:text-orange-700">
                                                            <i class="fa-solid fa-pen-to-square"></i>
                                                            Editar
                                                        </button>
                                                    </div>
                                                    <p class="text-sm text-gray-600">{{ $task->description ?? "Sem descri\u{00E7}\u{00E3}o" }}</p>
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
                                                    class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium {{ $badge['classes'] }}">
                                                    {{ $badge['label'] }}
                                                </span>
                                            </div>

                                            <div class="mt-3 grid grid-cols-1 md:grid-cols-3 gap-4 text-sm text-gray-600">
                                                <div>
                                                    <p class="text-xs uppercase tracking-widest text-gray-400">Responsável</p>
                                                    <p class="font-medium text-gray-800">
                                                        {{ $task->assignedUser?->name ?? "N\u{00E3}o atrib\u{00ED}do" }}
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
                                            </div>

                                            <div class="mt-3">
                                                <p class="text-xs uppercase tracking-widest text-gray-400 mb-1">Notas de progresso</p>
                                                <p class="text-sm text-gray-700 whitespace-pre-line">
                                                    {{ $task->progress_notes ?: 'Sem notas registradas.' }}
                                                </p>
                                            </div>

                                            @include('admin.projects.steps.develepoment.edit', [
                                                'task' => $task,
                                                'teamMembers' => $teamMembers,
                                                'skillOptions' => $skillOptions,
                                            ])
                                        </div>
                                    @empty
                                        <div class="border border-dashed border-gray-200 rounded-md p-4 text-sm text-gray-500">
                                            Nenhuma tarefa marcada como {{ mb_strtolower($label, 'UTF-8') }} para esta skill.
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
