<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-100 leading-tight">
                    Painel principal
                </h2>
                <p class="text-sm text-gray-500">
                    Resumo do que precisa da sua atencao hoje.
                </p>
            </div>
            <a href="{{ route('admin.tasks.index') }}"
                class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold rounded-md bg-orange-600 text-white hover:bg-orange-500">
                <i class="fa-solid fa-list-check"></i>
                Ver todas as tarefas
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">
            <section class="space-y-4">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-100">Painel resumo</h3>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
                    @foreach ($summaryCards as $card)
                        <div class="bg-white dark:bg-dark-800 border border-gray-100 dark:border-dark-700 rounded-lg shadow-sm p-5 h-full">
                            <div class="flex items-center justify-between text-sm text-gray-500 uppercase tracking-wide">
                                <span>{{ $card['title'] }}</span>
                                @if (!empty($card['icon']))
                                    <span class="text-lg text-gray-400 dark:text-gray-500">
                                        <i class="{{ $card['icon'] }}"></i>
                                    </span>
                                @endif
                            </div>
                            <div class="mt-4 space-y-3">
                                @foreach ($card['metrics'] as $metric)
                                    <div class="flex items-center justify-between text-gray-700 dark:text-gray-200">
                                        <span class="text-sm text-gray-500 dark:text-gray-400">{{ $metric['label'] }}</span>
                                        <span class="text-2xl font-semibold text-gray-900 dark:text-white">{{ $metric['value'] }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>

            <section class="bg-white dark:bg-dark-800 border border-gray-100 dark:border-dark-700 rounded-lg shadow-sm">
                <div class="px-6 py-4 border-b border-gray-100 dark:border-dark-700 flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-100">Tarefas em destaque</h3>
                        <p class="text-sm text-gray-500">
                            Priorizamos itens em andamento e proximos do prazo planejado.
                        </p>
                    </div>
                    <form method="GET" action="{{ route('dashboard') }}" class="w-full flex flex-col gap-3 md:flex-row md:items-center md:justify-end">
                        <input type="hidden" name="personal_status" value="{{ $personalFilters['status'] }}">
                        <input type="hidden" name="personal_end" value="{{ $personalFilters['end'] }}">
                        <label for="status" class="sr-only">Status</label>
                        <select
                            id="status"
                            name="status"
                            class="w-full md:w-48 border border-gray-200 dark:border-dark-600 rounded-md text-sm bg-white dark:bg-dark-700 text-gray-700 dark:text-gray-100 focus:border-orange-500 focus:ring-orange-500">
                            <option value="">Todos</option>
                            @foreach ($taskStatuses as $value => $label)
                                <option value="{{ $value }}" @selected($taskStatusFilter === $value)>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                        <label for="q" class="sr-only">Buscar</label>
                        <div class="relative w-full md:w-72">
                            <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400">
                                <i class="fa-solid fa-magnifying-glass"></i>
                            </span>
                            <input
                                id="q"
                                type="search"
                                name="q"
                                value="{{ $search }}"
                                placeholder="Tarefa, projeto ou responsavel"
                                class="w-full pl-9 pr-3 py-2 border border-gray-200 dark:border-dark-600 rounded-md text-sm bg-white dark:bg-dark-700 text-gray-700 dark:text-gray-100 focus:border-orange-500 focus:ring-orange-500"
                            >
                        </div>
                    </form>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-dark-600 text-sm">
                        <thead class="bg-gray-50 dark:bg-dark-700 text-gray-600 dark:text-gray-300 uppercase tracking-wide text-xs">
                            <tr>
                                <th class="px-6 py-3 text-left font-semibold">Tarefa</th>
                                <th class="px-6 py-3 text-left font-semibold">Responsavel</th>
                                <th class="px-6 py-3 text-left font-semibold">Skill</th>
                                <th class="px-6 py-3 text-left font-semibold">Data</th>
                                <th class="px-6 py-3 text-left font-semibold">Ação</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-dark-700">
                            @forelse ($tasks as $task)
                                @php
                                    $badge = $statusBadges[$task->status] ?? [
                                        'label' => ucfirst($task->status),
                                        'classes' => 'bg-gray-100 text-gray-700 border border-gray-200',
                                    ];
                                    $referenceDate = $task->planned_at ?? $task->completed_at;
                                    $isLate = $task->planned_at && $task->planned_at->isPast() && $task->status !== \App\Models\ProjectTask::STATUS_DONE;
                                    $dateLabel = $referenceDate ? $referenceDate->format('d/m/Y') : 'Sem data';
                                @endphp
                                <tr class="hover:bg-gray-50 dark:hover:bg-dark-700">
                                    <td class="px-6 py-4 align-top">
                                        <div class="font-semibold text-gray-900 dark:text-white">{{ $task->title }}</div>
                                        <div class="mt-2 flex flex-wrap items-center gap-2 text-xs text-gray-500 dark:text-gray-400">
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full border {{ $badge['classes'] }}">
                                                {{ $badge['label'] }}
                                            </span>
                                            @if ($task->project)
                                                <span class="inline-flex items-center gap-1">
                                                    <i class="fa-regular fa-folder-closed text-gray-400"></i>
                                                    {{ $task->project->name }}
                                                </span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-gray-700 dark:text-gray-100">
                                        {{ optional($task->assignedUser)->name ?? 'Nao definido' }}
                                    </td>
                                    <td class="px-6 py-4 text-gray-700 dark:text-gray-100">
                                        {{ optional($task->skill)->name ?? 'Sem skill' }}
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-medium border {{ $isLate ? 'bg-amber-100 text-amber-800 border-amber-200' : 'bg-gray-100 text-gray-700 border-gray-200 dark:bg-dark-700 dark:text-gray-200 dark:border-dark-600' }}">
                                            <i class="fa-regular fa-clock"></i>
                                            {{ $dateLabel }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        @if ($task->project)
                                            <a href="{{ route('admin.projects.steps.show', [$task->project, 'tab' => 'development']) }}"
                                                class="inline-flex items-center gap-2 px-3 py-1.5 text-xs font-semibold rounded-md border border-orange-200 text-orange-700 bg-orange-50 hover:bg-orange-100">
                                                <i class="fa-solid fa-arrow-up-right-from-square"></i>
                                                Ver tarefa
                                            </a>
                                        @else
                                            <span class="text-xs text-gray-400">Projeto indisponível</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-8 text-center text-sm text-gray-500 dark:text-gray-300">
                                        Nenhuma tarefa encontrada para os filtros aplicados.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="px-6 py-4 border-t border-gray-100 dark:border-dark-700">
                    {{ $tasks->links() }}
                </div>
            </section>

            @php
                $quickActions = [
                    [
                        'label' => 'Criar projeto',
                        'description' => 'Defina briefing, equipe e entregas.',
                        'icon' => 'fa-solid fa-diagram-project',
                        'href' => route('admin.projects.create'),
                    ],
                    [
                        'label' => 'Criar orcamento',
                        'description' => 'Monte propostas personalizadas.',
                        'icon' => 'fa-solid fa-file-signature',
                        'href' => route('admin.commercial.budgets.create'),
                    ],
                    [
                        'label' => 'Ver servicos',
                        'description' => 'Revise ofertas e processos.',
                        'icon' => 'fa-solid fa-briefcase',
                        'href' => route('admin.services.index'),
                    ],
                    [
                        'label' => 'Ver habilidades',
                        'description' => 'Atualize skills e competencias.',
                        'icon' => 'fa-solid fa-lightbulb',
                        'href' => route('admin.skills.index'),
                    ],
                ];
            @endphp

            <section class="bg-white dark:bg-dark-800 border border-gray-100 dark:border-dark-700 rounded-lg shadow-sm">
                <div class="px-6 py-4 border-b border-gray-100 dark:border-dark-700">
                    <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-100">Acoes rapidas</h3>
                    <p class="text-sm text-gray-500">
                        Crie ou navegue para secoes-chave sem sair do painel.
                    </p>
                </div>
                <div class="px-6 py-6">
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                        @foreach ($quickActions as $action)
                            <a href="{{ $action['href'] }}"
                                class="flex flex-col gap-3 p-4 border border-gray-200 dark:border-dark-600 rounded-lg hover:border-orange-400 hover:bg-orange-50/60 dark:hover:border-orange-400 dark:hover:bg-white/5 transition">
                                <div class="w-10 h-10 rounded-full bg-orange-100 text-orange-600 flex items-center justify-center text-lg">
                                    <i class="{{ $action['icon'] }}"></i>
                                </div>
                                <div>
                                    <h5 class="text-sm font-semibold text-gray-900 dark:text-white">{{ $action['label'] }}</h5>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ $action['description'] }}</p>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </div>
            </section>

            <section class="bg-white dark:bg-dark-800 border border-gray-100 dark:border-dark-700 rounded-lg shadow-sm">
                <div class="px-6 py-4 border-b border-gray-100 dark:border-dark-700">
                    <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-100">Painel analitico</h3>
                    <p class="text-sm text-gray-500">
                        Entenda o andamento dos projetos e a produtividade da equipe.
                    </p>
                </div>
                <div class="p-6 grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <div class="border border-dashed border-gray-200 dark:border-dark-600 rounded-xl p-4 bg-white dark:bg-dark-900">
                        <div class="flex items-center justify-between mb-4">
                            <div>
                                <h4 class="text-base font-semibold text-gray-800 dark:text-gray-100">Progresso de projetos</h4>
                                <p class="text-xs text-gray-500">Relacao entre projetos ativos e concluidos.</p>
                            </div>
                        </div>
                        <div class="h-64 flex items-center justify-center">
                            <canvas id="chart-project-progress"></canvas>
                        </div>
                        <div class="mt-4 space-y-2 text-sm">
                            <div class="flex items-center justify-between">
                                <span class="flex items-center gap-2 text-gray-600">
                                    <span class="w-3 h-3 rounded-full bg-orange-500"></span>
                                    Em andamento
                                </span>
                                <span class="font-semibold text-gray-900 dark:text-white">{{ $analytics['projectProgress']['data'][0] ?? 0 }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="flex items-center gap-2 text-gray-600">
                                    <span class="w-3 h-3 rounded-full bg-emerald-500"></span>
                                    Concluidos
                                </span>
                                <span class="font-semibold text-gray-900 dark:text-white">{{ $analytics['projectProgress']['data'][1] ?? 0 }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="border border-dashed border-gray-200 dark:border-dark-600 rounded-xl p-4 bg-white dark:bg-dark-900">
                        <div class="flex items-center justify-between mb-4">
                            <div>
                                <h4 class="text-base font-semibold text-gray-800 dark:text-gray-100">Tarefas por status</h4>
                                <p class="text-xs text-gray-500">Avalie gargalos por etapa.</p>
                            </div>
                        </div>
                        <div class="h-64">
                            <canvas id="chart-tasks-status"></canvas>
                        </div>
                    </div>

                    <div class="border border-dashed border-gray-200 dark:border-dark-600 rounded-xl p-4 bg-white dark:bg-dark-900">
                        <div class="flex items-center justify-between mb-4">
                            <div>
                                <h4 class="text-base font-semibold text-gray-800 dark:text-gray-100">Tarefas por skill</h4>
                                <p class="text-xs text-gray-500">Quais competencias estao mais demandadas.</p>
                            </div>
                        </div>
                        <div class="h-64">
                            <canvas id="chart-tasks-skill"></canvas>
                        </div>
                    </div>

                    <div class="border border-dashed border-gray-200 dark:border-dark-600 rounded-xl p-4 bg-white dark:bg-dark-900">
                        <div class="flex items-center justify-between mb-4">
                            <div>
                                <h4 class="text-base font-semibold text-gray-800 dark:text-gray-100">Conclusoes por semana</h4>
                                <p class="text-xs text-gray-500">Volume de tarefas finalizadas nas ultimas semanas.</p>
                            </div>
                        </div>
                        <div class="h-64">
                            <canvas id="chart-tasks-weekly"></canvas>
                        </div>
                    </div>
                </div>
            </section>

            <section class="bg-white dark:bg-dark-800 border border-gray-100 dark:border-dark-700 rounded-lg shadow-sm">
                <div class="px-6 py-4 border-b border-gray-100 dark:border-dark-700">
                    <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-100">Painel pessoal</h3>
                    <p class="text-sm text-gray-500">
                        Visao das entregas diretamente associadas a voce dentro dos projetos.
                    </p>
                </div>
                <div class="p-6 space-y-8">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        @foreach ($personalCards as $card)
                            <div class="p-4 border border-gray-200 dark:border-dark-600 rounded-xl bg-white dark:bg-dark-900 flex flex-col gap-2">
                                <span class="text-sm text-gray-500">{{ $card['title'] }}</span>
                                <span class="text-3xl font-semibold text-gray-900 dark:text-white">{{ $card['value'] }}</span>
                                <span class="text-xs text-gray-500">{{ $card['meta'] }}</span>
                            </div>
                        @endforeach
                    </div>

                    <div class="border border-gray-200 dark:border-dark-600 rounded-xl overflow-hidden">
                        <div class="px-6 py-4 border-b border-gray-100 dark:border-dark-700 flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                            <div>
                                <h4 class="text-base font-semibold text-gray-800 dark:text-gray-100">Tarefas atribuídas</h4>
                                <p class="text-sm text-gray-500">Filtre por status e data planejada.</p>
                            </div>
                            <form method="GET" action="{{ route('dashboard') }}" class="w-full grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                                <input type="hidden" name="q" value="{{ $search }}">
                                <input type="hidden" name="status" value="{{ $taskStatusFilter }}">
                                <div>
                                    <label for="personal_status" class="text-xs text-gray-500 uppercase tracking-wide">Status</label>
                                    <select id="personal_status" name="personal_status"
                                        class="mt-1 w-full border border-gray-200 dark:border-dark-600 rounded-md text-sm bg-white dark:bg-dark-700 text-gray-800 dark:text-gray-100 focus:border-orange-500 focus:ring-orange-500">
                                        <option value="">Todos</option>
                                        @foreach ($personalStatuses as $value => $label)
                                            <option value="{{ $value }}" @selected($personalFilters['status'] === $value)>
                                                {{ $label }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label for="personal_end" class="text-xs text-gray-500 uppercase tracking-wide">Data final</label>
                                    <input type="date" id="personal_end" name="personal_end" value="{{ $personalFilters['end'] }}"
                                        class="mt-1 w-full border border-gray-200 dark:border-dark-600 rounded-md text-sm bg-white dark:bg-dark-700 text-gray-800 dark:text-gray-100 focus:border-orange-500 focus:ring-orange-500">
                                </div>
                                <div class="flex items-end gap-2">
                                    <a href="{{ route('dashboard') }}"
                                        class="inline-flex justify-center w-full px-3 py-2 border border-gray-300 dark:border-dark-600 rounded-md text-sm text-gray-600 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-dark-700">Limpar</a>
                                    <button type="submit"
                                        class="inline-flex justify-center w-full px-3 py-2 bg-orange-600 text-white rounded-md text-sm font-semibold hover:bg-orange-500">
                                        Aplicar
                                    </button>
                                </div>
                            </form>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-dark-700 text-sm">
                                <thead class="bg-gray-50 dark:bg-dark-700 text-gray-600 dark:text-gray-300 uppercase tracking-wide text-xs">
                                    <tr>
                                        <th class="px-6 py-3 text-left font-semibold">Tarefa</th>
                                        <th class="px-6 py-3 text-left font-semibold">Projeto</th>
                                        <th class="px-6 py-3 text-left font-semibold">Status</th>
                                        <th class="px-6 py-3 text-left font-semibold">Skill</th>
                                        <th class="px-6 py-3 text-left font-semibold">Planejado</th>
                                        <th class="px-6 py-3 text-left font-semibold">Ação</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 dark:divide-dark-700">
                                    @forelse ($personalTasks as $task)
                                        @php
                                            $badge = $statusBadges[$task->status] ?? [
                                                'label' => ucfirst($task->status),
                                                'classes' => 'bg-gray-100 text-gray-700 border border-gray-200',
                                            ];
                                            $plannedLabel = $task->planned_at ? $task->planned_at->format('d/m/Y') : 'Sem data';
                                        @endphp
                                        <tr class="hover:bg-gray-50 dark:hover:bg-dark-700">
                                            <td class="px-6 py-4 text-gray-900 dark:text-white">
                                                <div class="font-semibold">{{ $task->title }}</div>
                                                <div class="text-xs text-gray-500">
                                                    Criado em {{ $task->created_at?->format('d/m/Y') }}
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 text-gray-700 dark:text-gray-100">
                                                {{ optional($task->project)->name ?? 'Projeto não definido' }}
                                            </td>
                                            <td class="px-6 py-4">
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full border {{ $badge['classes'] }}">
                                                    {{ $badge['label'] }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 text-gray-700 dark:text-gray-100">
                                                {{ optional($task->skill)->name ?? 'Sem skill' }}
                                            </td>
                                            <td class="px-6 py-4">
                                                <span class="text-sm text-gray-800 dark:text-gray-200">{{ $plannedLabel }}</span>
                                            </td>
                                            <td class="px-6 py-4">
                                                @if ($task->project)
                                                    <a href="{{ route('admin.projects.steps.show', [$task->project, 'tab' => 'development']) }}"
                                                        class="inline-flex items-center gap-2 px-3 py-1.5 text-xs font-semibold rounded-md border border-orange-200 text-orange-700 bg-orange-50 hover:bg-orange-100">
                                                        <i class="fa-solid fa-arrow-up-right-from-square"></i>
                                                        Ver tarefa
                                                    </a>
                                                @else
                                                    <span class="text-xs text-gray-400">Projeto indisponível</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="px-6 py-8 text-center text-sm text-gray-500 dark:text-gray-300">
                                                Nenhuma tarefa encontrada para os filtros selecionados.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        <div class="px-6 py-4 border-t border-gray-100 dark:border-dark-700">
                            {{ $personalTasks->links() }}
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>
</x-app-layout>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const chartColors = {
            primary: '#f97316',
            secondary: '#fbbf24',
            success: '#10b981',
            info: '#0ea5e9',
            purple: '#8b5cf6',
            slate: '#94a3b8'
        };

        const projectProgressData = @json($analytics['projectProgress'] ?? ['labels' => [], 'data' => []]);
        const tasksByStatusData = @json($analytics['tasksByStatus'] ?? ['labels' => [], 'data' => []]);
        const tasksBySkillData = @json($analytics['tasksBySkill'] ?? ['labels' => [], 'data' => []]);
        const weeklyCompletedData = @json($analytics['weeklyCompleted'] ?? ['labels' => [], 'data' => []]);

        const chartProjectProgress = document.getElementById('chart-project-progress');
        if (chartProjectProgress) {
            new Chart(chartProjectProgress, {
                type: 'doughnut',
                data: {
                    labels: projectProgressData.labels,
                    datasets: [{
                        data: projectProgressData.data,
                        backgroundColor: [chartColors.primary, chartColors.success],
                        borderWidth: 0
                    }]
                },
                options: {
                    responsive: true,
                    cutout: '65%',
                    plugins: {
                        legend: { display: false }
                    }
                }
            });
        }

        const chartTasksStatus = document.getElementById('chart-tasks-status');
        if (chartTasksStatus) {
            new Chart(chartTasksStatus, {
                type: 'bar',
                data: {
                    labels: tasksByStatusData.labels,
                    datasets: [{
                        data: tasksByStatusData.data,
                        backgroundColor: [chartColors.primary, chartColors.info, chartColors.success],
                        borderRadius: 6,
                        borderSkipped: false
                    }]
                },
                options: {
                    responsive: true,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: { stepSize: 1 }
                        }
                    },
                    plugins: {
                        legend: { display: false }
                    }
                }
            });
        }

        const chartTasksSkill = document.getElementById('chart-tasks-skill');
        if (chartTasksSkill) {
            const skillColors = ['#f97316', '#fb923c', '#fbbf24', '#fde68a', '#60a5fa', '#c084fc'];
            const skillValues = Array.isArray(tasksBySkillData.data) ? tasksBySkillData.data : [];
            const highestSkillValue = skillValues.length ? Math.max(...skillValues) : 0;
            const skillAxisTarget = Math.max(40, Math.ceil(Math.max(highestSkillValue, 1) / 5) * 5);

            new Chart(chartTasksSkill, {
                type: 'bar',
                data: {
                    labels: tasksBySkillData.labels,
                    datasets: [{
                        data: skillValues,
                        backgroundColor: skillColors,
                        borderRadius: 6,
                        borderSkipped: false
                    }]
                },
                options: {
                    responsive: true,
                    indexAxis: 'y',
                    scales: {
                        x: {
                            beginAtZero: true,
                            suggestedMax: skillAxisTarget,
                            max: skillAxisTarget,
                            ticks: {
                                stepSize: 5,
                                callback: (value) => value
                            }
                        }
                    },
                    plugins: {
                        legend: { display: false }
                    }
                }
            });
        }

        const chartTasksWeekly = document.getElementById('chart-tasks-weekly');
        if (chartTasksWeekly) {
            new Chart(chartTasksWeekly, {
                type: 'bar',
                data: {
                    labels: weeklyCompletedData.labels,
                    datasets: [{
                        data: weeklyCompletedData.data,
                        backgroundColor: chartColors.purple,
                        borderRadius: 6,
                        borderSkipped: false
                    }]
                },
                options: {
                    responsive: true,
                    scales: {
                        y: { beginAtZero: true }
                    },
                    plugins: {
                        legend: { display: false }
                    }
                }
            });
        }
    });
</script>
