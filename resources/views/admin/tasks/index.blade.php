<style>
    .tasks-viewdt-btn {
        background-color: transparent;
        border: 2px solid #f5f5f5;
        font-weight: 800;
        display: flex;
        justify-content: center;
        align-items: center;
        text-align: center;
        margin: 0 auto;
        border-radius: 8px;
        z-index: 1;
        transition: border-color 0.2s ease;
    }

    .dark .tasks-viewdt-btn {
        border-color: #262626;
    }

    .tasks-viewdt-btn:hover {
        border-color: #ff8800 !important;
    }

    #tasks-table tbody tr:hover .tasks-viewdt-btn {
        border-color: #000;
    }

    .dark #tasks-table tbody tr:hover .tasks-viewdt-btn {
        border-color: #fff;
    }

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
    $title = 'Tarefas';
    $subTitle = 'Veja suas tarefas ativas';
    $statusBadges = [
        \App\Models\ProjectTask::STATUS_PENDING => [
            'label' => 'Pendente',
            'classes' => 'badge-pendent',
        ],
        \App\Models\ProjectTask::STATUS_IN_PROGRESS => [
            'label' => 'Em andamento',
            'classes' => 'badge-inprogress',
        ],
    ];

    $statusSearchHidden = request()->except(['status', 'q', 'page']);
    $projectHidden = request()->except(['project_id', 'page']);
    $skillHidden = request()->except(['skill_id', 'page']);
    $clientHidden = request()->except(['client', 'page']);
    $professionalHidden = request()->except(['professional', 'page']);
    $deadlineHidden = request()->except(['deadline', 'page']);

    // TABS DO NAVBAR
    $navbarTabs = [
        [
            'label' => 'Dashboard',
            'icon' => 'fa-duotone fa-list-check',
            'route' => route('admin.tasks.index'),
            'active' => request()->routeIs('admin.tasks.index'),
        ],
        [
            'label' => 'Calendário',
            'icon' => 'fa-duotone fa-calendar-days',
            'route' => route('admin.tasks.calendar'),
            'active' => request()->routeIs('admin.tasks.calendar'),
        ],
        [
            'label' => 'Kanban',
            'icon' => 'fa-duotone fa-chart-kanban',
            'route' => route('admin.tasks.kanban'),
            'active' => request()->routeIs('admin.tasks.kanban'),
        ],
        [
            'label' => 'Finalizadas',
            'icon' => 'fa-duotone fa-circle-check',
            'route' => route('admin.tasks.completed'),
            'active' => request()->routeIs('admin.tasks.completed'),
        ],
    ];
@endphp

@extends('layouts.app')

@section('content')
    <div>
        <div class="grid grid-cols-1">
            <div
                class="card bg-white dark:bg-neutral-900 border border-neutral-300 dark:border-neutral-800 rounded-3xl overflow-hidden">
                <div
                    class="card-header flex justify-between items-center px-6 py-6 border-b border-neutral-300 dark:border-neutral-800">
                    <div>
                        <h6 class="card-title mb-0 text-lg font-semibold text-neutral-800 dark:text-white">Tarefas
                            Pendentes</h6>
                        <p class="text-sm text-neutral-500 dark:text-neutral-400 mt-1">Priorizamos itens em andamento e
                            próximos do prazo planejado.</p>
                    </div>

                    <form method="GET" action="{{ route('admin.tasks.index') }}"
                        class="flex flex-col gap-3 md:flex-row md:items-center">
                        @foreach ($statusSearchHidden as $param => $value)
                            @if (is_array($value))
                                @foreach ($value as $item)
                                    <input type="hidden" name="{{ $param }}[]" value="{{ $item }}">
                                @endforeach
                            @else
                                <input type="hidden" name="{{ $param }}" value="{{ $value }}">
                            @endif
                        @endforeach

                        <select name="status"
                            class="form-select form-select-sm w-full md:w-40 bg-white dark:bg-neutral-800 border border-neutral-300 dark:border-neutral-700 text-neutral-800 dark:text-white rounded-lg px-3 py-2 text-sm focus:border-[#ff8800] focus:ring-0">
                            <option value="">Todos</option>
                            @foreach ($availableStatuses as $value => $label)
                                <option value="{{ $value }}" @selected((string) $selectedStatus === (string) $value)>{{ $label }}
                                </option>
                            @endforeach
                        </select>

                        <div class="relative w-full md:w-72">
                            <input type="text" name="q" value="{{ $search }}"
                                class="w-full bg-white dark:!bg-[#262626] border border-neutral-300 dark:border-neutral-700 rounded-lg pl-10 pr-3 py-2 text-sm text-neutral-800 dark:text-white focus:border-[#ff8800] focus:ring-0"
                                placeholder="Buscar tarefa, projeto ou responsável...">

                            <span
                                class="absolute inset-y-0 left-0 pl-3 flex items-center text-neutral-500 dark:text-neutral-400">
                                <i class="fa-solid fa-magnifying-glass"></i>
                            </span>
                        </div>
                    </form>
                </div>

                <div class="filters-bar grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3 px-6 pb-0 pt-4">
                    <form method="GET" action="{{ route('admin.tasks.index') }}" class="w-full">
                        @foreach ($projectHidden as $param => $value)
                            @if (is_array($value))
                                @foreach ($value as $item)
                                    <input type="hidden" name="{{ $param }}[]" value="{{ $item }}">
                                @endforeach
                            @else
                                <input type="hidden" name="{{ $param }}" value="{{ $value }}">
                            @endif
                        @endforeach

                        <select name="project_id"
                            class="w-full bg-white dark:bg-neutral-800 border border-neutral-300 dark:border-neutral-700 text-neutral-800 dark:text-white rounded-lg px-3 py-4 text-sm focus:border-[#ff8800] focus:ring-0">
                            <option value="">Projeto</option>
                            @foreach ($projects as $project)
                                <option value="{{ $project->id }}" @selected((string) $selectedProject === (string) $project->id)>{{ $project->name }}
                                </option>
                            @endforeach
                        </select>
                    </form>

                    <form method="GET" action="{{ route('admin.tasks.index') }}" class="w-full">
                        @foreach ($clientHidden as $param => $value)
                            @if (is_array($value))
                                @foreach ($value as $item)
                                    <input type="hidden" name="{{ $param }}[]" value="{{ $item }}">
                                @endforeach
                            @else
                                <input type="hidden" name="{{ $param }}" value="{{ $value }}">
                            @endif
                        @endforeach

                        <select name="client"
                            class="w-full bg-white dark:bg-neutral-800 border border-neutral-300 dark:border-neutral-700 text-neutral-800 dark:text-white rounded-lg px-3 py-4 text-sm focus:border-[#ff8800] focus:ring-0">
                            <option value="">Cliente</option>
                            @foreach ($clientOptions as $client)
                                <option value="{{ $client->id }}" @selected((string) $selectedClient === (string) $client->id)>{{ $client->name }}
                                </option>
                            @endforeach
                        </select>
                    </form>

                    <form method="GET" action="{{ route('admin.tasks.index') }}" class="w-full">
                        @foreach ($professionalHidden as $param => $value)
                            @if (is_array($value))
                                @foreach ($value as $item)
                                    <input type="hidden" name="{{ $param }}[]" value="{{ $item }}">
                                @endforeach
                            @else
                                <input type="hidden" name="{{ $param }}" value="{{ $value }}">
                            @endif
                        @endforeach

                        <select name="professional"
                            class="w-full bg-white dark:bg-neutral-800 border border-neutral-300 dark:border-neutral-700 text-neutral-800 dark:text-white rounded-lg px-3 py-4 text-sm focus:border-[#ff8800] focus:ring-0">
                            <option value="">Profissional</option>
                            @foreach ($professionalOptions as $professional)
                                <option value="{{ $professional->id }}" @selected((string) $selectedProfessional === (string) $professional->id)>
                                    {{ $professional->name }}</option>
                            @endforeach
                        </select>
                    </form>

                    <form method="GET" action="{{ route('admin.tasks.index') }}" class="w-full">
                        @foreach ($skillHidden as $param => $value)
                            @if (is_array($value))
                                @foreach ($value as $item)
                                    <input type="hidden" name="{{ $param }}[]" value="{{ $item }}">
                                @endforeach
                            @else
                                <input type="hidden" name="{{ $param }}" value="{{ $value }}">
                            @endif
                        @endforeach

                        <select name="skill_id"
                            class="w-full bg-white dark:bg-neutral-800 border border-neutral-300 dark:border-neutral-700 text-neutral-800 dark:text-white rounded-lg px-3 py-4 text-sm focus:border-[#ff8800] focus:ring-0">
                            <option value="">Área (Habilidade)</option>
                            @foreach ($skills as $skill)
                                <option value="{{ $skill->id }}" @selected((string) $selectedSkill === (string) $skill->id)>{{ $skill->name }}
                                </option>
                            @endforeach
                        </select>
                    </form>

                    <form method="GET" action="{{ route('admin.tasks.index') }}" class="w-full">
                        @foreach ($deadlineHidden as $param => $value)
                            @if (is_array($value))
                                @foreach ($value as $item)
                                    <input type="hidden" name="{{ $param }}[]" value="{{ $item }}">
                                @endforeach
                            @else
                                <input type="hidden" name="{{ $param }}" value="{{ $value }}">
                            @endif
                        @endforeach

                        <input type="date" name="deadline" value="{{ $deadlineFilter }}"
                            class="w-full bg-white dark:bg-neutral-800 border border-neutral-300 dark:border-neutral-700 text-neutral-800 dark:text-white rounded-lg px-3 py-4 text-sm focus:border-[#ff8800] focus:ring-0">
                    </form>
                </div>


                <div class="card-body overflow-x-auto">
                    <table id="tasks-table"
                        class="w-full text-sm text-center border border-neutral-300 dark:border-neutral-800 rounded-xl overflow-hidden border-separate border-spacing-0">
                        <thead class="bg-[#ff8800] text-black dark:text-white">
                            <tr>
                                <th
                                    class="py-3 px-4 first:rounded-tl-xl border-b border-neutral-300 dark:border-neutral-800">
                                    Tarefa</th>
                                <th class="py-3 px-4 border-b border-neutral-300 dark:border-neutral-800">Área</th>
                                <th class="py-3 px-4 border-b border-neutral-300 dark:border-neutral-800">Tecnologia
                                </th>
                                <th class="py-3 px-4 border-b border-neutral-300 dark:border-neutral-800">Responsável
                                </th>
                                <th class="py-3 px-4 border-b border-neutral-300 dark:border-neutral-800">Data Limite
                                </th>
                                <th class="py-3 px-4 border-b border-neutral-300 dark:border-neutral-800">Status</th>
                                <th
                                    class="py-3 px-4 last:rounded-tr-xl border-b border-neutral-300 dark:border-neutral-800">
                                    Ação</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-neutral-900">
                            @forelse ($tasks as $task)
                                @php
                                    $badge = $statusBadges[$task->status] ?? [
                                        'label' => ucfirst($task->status),
                                        'classes' => 'bg-gray-100 text-gray-700 border border-gray-200',
                                    ];
                                @endphp
                                <tr class="hover:bg-neutral-100 dark:hover:bg-neutral-800 transition">
                                    <td class="py-3 px-4 border-t border-neutral-300 dark:border-neutral-800">
                                        {{ $task->title }}</td>
                                    <td class="py-3 px-4 border-t border-neutral-300 dark:border-neutral-800">
                                        {{ optional($task->skill)->name ?? 'Sem skill' }}</td>
                                    <td class="py-3 px-4 border-t border-neutral-300 dark:border-neutral-800">
                                        {{ optional($task->project)->name ?? 'Sem projeto' }}</td>
                                    <td class="py-3 px-4 border-t border-neutral-300 dark:border-neutral-800">
                                        {{ optional($task->assignedUser)->name ?? 'Não definido' }}</td>
                                    <td class="py-3 px-4 border-t border-neutral-300 dark:border-neutral-800">
                                        {{ $task->planned_at ? $task->planned_at->format('d/m/Y') : 'Sem prazo' }}</td>
                                    <td class="py-3 px-4 border-t border-neutral-300 dark:border-neutral-800">
                                        <span
                                            class="inline-flex items-center gap-1 px-4 py-2 rounded-full text-xs font-medium {{ $badge['classes'] }}">
                                            {{ $badge['label'] }}
                                        </span>
                                    </td>

                                    <td class="py-3 px-4 border-t border-neutral-300 dark:border-neutral-800">
                                        @if ($task->project)
                                            <a href="{{ route('admin.projects.steps.show', [$task->project, 'tab' => 'development']) }}"
                                                class="tasks-viewdt-btn inline-flex items-center justify-center w-12 h-10 rounded-md">
                                                <i class="fa-duotone fa-arrow-right-to-arc icon-project"></i>
                                            </a>
                                        @else
                                            <span class="text-xs text-neutral-500">Projeto indisponível</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7"
                                        class="py-8 text-center text-sm text-neutral-500 dark:text-neutral-400">Nenhuma
                                        tarefa encontrada.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="px-6 py-4 border-t border-neutral-300 dark:border-neutral-800">
                    {{ $tasks->withQueryString()->links() }}
                </div>
            </div>
        </div>
    </div>
@endsection
