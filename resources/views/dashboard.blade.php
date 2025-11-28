<style>
    /* 🌞 Modo Claro */
    .card {
        background-color: #ffffff !important;
        /* fundo branco padrão */
        color: #000000 !important;
        /* texto principal */
        transition: background-color 0.3s ease, color 0.3s ease !important;
    }

    .card p {
        color: #555555 !important;
        /* texto secundário */
    }

    .card .icon-area {
        border-radius: 50px !important;
        background-color: #f5f5f5 !important;
    }

    /* 🌙 Modo Escuro */
    .dark .card {
        background-color: #000000 !important;
        /* fundo escuro */
        color: #ffffff !important;
    }

    .dark .card p {
        color: #cccccc !important;
        /* texto leve */
    }

    .dark .card .icon-area {
        border-radius: 50px !important;
        background-color: #262626 !important;
    }

    /* Modo claro */
    .icon-project.fa-duotone::before,
    .icon-project.fad::before {
        color: rgb(255 136 0) !important;
        /* Camada primária */
    }

    .icon-project.fa-duotone::after,
    .icon-project.fad::after {
        color: rgb(0 0 0) !important;
        /* Camada secundária */
        opacity: 1 !important;
    }

    /* Modo escuro */
    .dark .icon-project.fa-duotone::before,
    .dark .icon-project.fad::before {
        color: rgb(255 136 0) !important;
        /* Mantém o laranja */
    }

    .dark .icon-project.fa-duotone::after,
    .dark .icon-project.fad::after {
        color: rgb(255 255 255) !important;
        /* Cinza escuro no dark mode */
        opacity: 1 !important;
    }

    .text-orange-fix {
        color: #ff8800 !important;
        mix-blend-mode: normal !important;
        isolation: isolate !important;
        position: relative;
        z-index: 2;
    }

    .welcome-btn {
        background-color: #ff8800;
        border: 2px solid #ff8800;
        width: 200px;
        height: 50px;
        display: flex;
        justify-content: center;
        align-items: center;
        text-align: center;
        margin-left: 0 auto;
        border-radius: 8px;
        box-sizing: border-box;
        /* 👈 mantém o tamanho fixo */
        font-weight: 600;
        transition: background-color 0.3s ease, color 0.3s ease;
    }

    .welcome-outline-btn:hover {
        background-color: transparent !important;
        border: 2px solid #ff8800;
    }

    .welcome-outline-btn {
        background-color: #ff8800;
        border: 2px solid #ff8800;
        width: 200px;
        height: 50px;
        display: flex;
        justify-content: center;
        align-items: center;
        text-align: center;
        margin-left: 0 auto;
        border-radius: 8px;
        box-sizing: border-box;
        /* 👈 mantém o tamanho fixo */
        font-weight: 600;
        transition: background-color 0.3s ease, color 0.3s ease;
    }

    .welcome-btn:hover {
        background-color: transparent !important;
        border: 2px solid #ff8800;
    }

    .tasks-btn {
        background-color: #ff8800;
        border: 2px solid #ff8800;
        color: #fff;
        width: 200px;
        height: 50px;
        display: flex;
        justify-content: center;
        align-items: center;
        text-align: center;
        margin: 0 auto;
        border-radius: 8px;
        box-sizing: border-box;
        /* 👈 mantém o tamanho fixo */
        font-weight: 600;
        transition: background-color 0.3s ease, color 0.3s ease;
    }

    .tasks-btn:hover {
        background-color: transparent;
        color: #ff8800;
    }

    .tasks-view-btn {
        background-color: #ff8800;
        border: 2px solid #ff8800;
        font-weight: 800;
        width: 100px;
        height: 50px;
        display: flex;
        justify-content: center;
        align-items: center;
        /* centraliza verticalmente o texto */
        text-align: center;
        margin-right: 0 auto;
        /* <-- centraliza o botão na horizontal */
        border-radius: 8px;
        /* opcional, só pra suavizar */
        z-index: 1;
    }

    .tasks-view-btn:hover {
        background-color: transparent;
        color: #ff8800;
    }

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
        background-color: transparent;
        border: 2px solid #262626;
    }

    /* 🔸 Hover individual (prioridade máxima) */
    .tasks-viewdt-btn:hover {
        border-color: #ff8800 !important;
    }

    /* 🔸 Hover na linha: borda branca por padrão */
    #tasks-table tbody tr:hover .tasks-viewdt-btn {
        border-color: #000;
    }

    /* 🔸 Hover na linha — modo escuro */
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

    .apexcharts-legend-text {
        color: #262626 !important;
    }

    .dark .apexcharts-legend-text {
        color: #f5f5f5 !important;
    }
</style>

@extends('layouts.app')

@php
    $title = 'Painel principal';
    $subTitle = 'Dashboard';
    $currentUser = auth()->user();
    $userPhoto = $currentUser?->photo ? asset($currentUser->photo) : asset('assets/images/user.png');
    $userName = $currentUser?->name ?? 'Usuário';
    $preservedFilters = request()->except(['page', 'personal_page']);
    $chartFormParams = \Illuminate\Support\Arr::except($preservedFilters, ['chart_year']);
    $tableFormParams = $preservedFilters;
@endphp

@section('content')
    <div class="grid grid-cols-1 2xl:grid-cols-12 gap-6">
        <div class="col-span-12 2xl:col-span-8">
            <div class="grid grid-cols-1 gap-6">
                <div class="nft-promo-card card border-0 rounded-xl overflow-hidden z-1">
                    <div
                        class="nft-promo-card__inner flex 3xl:gap-[80px] 2xl:gap-[48px] xl:gap-[32px] lg:gap-6 gap-4 items-center relative z-[1]">
                        <div class="nft-promo-card__thumb w-full">
                            <img src="{{ $userPhoto }}" alt="{{ $userName }}"
                                class="w-full h-full object-fit-cover rounded-2xl">
                        </div>
                        <div class="flex-grow-1 py-6 3xl:px-[76px] 2xl:px-[56px] xl:px-[40px] lg:px-[28px] px-4">
                            <h3 class="text-3xl font-bold text-black dark:text-white mb-2">
                                E aí, {{ $userName }} 👋
                            </h3>
                            <h4 class="text-xl font-bold mb-3 leading-snug">
                                Pronto pra mais um dia <span class="text-orange-fix">criando, entregando e
                                    evoluindo</span> projetos? ✨
                            </h4>
                            <p class="text-neutral-300 text-base leading-relaxed mb-6">
                                Aqui no painel da MM Criativos você acompanha suas tarefas, avança nos projetos ativos
                                e mantém tudo em ordem com a equipe. Bora fazer o dia render?
                            </p>
                            <div class="flex flex-wrap gap-4">
                                <a href="{{ route('admin.projects.create') }}"
                                    class="welcome-btn px-6 py-2 text-sm text-black dark:text-white font-semibold">
                                    Criar novo projeto
                                </a>
                                <a href="{{ route('admin.tasks.index') }}"
                                    class="welcome-outline-btn px-6 py-2 text-sm text-black dark:text-white ">
                                    Ver tarefas
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach ($personalCards as $card)
                        <div
                            class="card flex items-center justify-between px-6 py-5 rounded-xl border-0 shadow-none transition bg-neutral-900 text-white">
                            <div class="icon-area flex items-center justify-center w-16 h-16">
                                <i class="{{ $card['icon'] }} fa-2x icon-project"></i>
                            </div>
                            <div class="flex flex-col text-right flex-1 ml-6">
                                <h6 class="text-lg font-semibold mb-1">{{ $card['title'] }}</h6>
                                <span class="text-5xl font-bold !text-[#ff8800] leading-none">
                                    {{ $card['value'] }}
                                </span>
                                <p class="text-sm mt-1">{{ $card['meta'] }}</p>
                            </div>
                        </div>
                    @endforeach
                    <div
                        class="card flex items-center justify-between px-6 py-5 rounded-xl border-0 shadow-none transition bg-neutral-900 text-white">
                        <div class="icon-area flex items-center justify-center w-16 h-16">
                            <i class="fa-duotone fa-code fa-xl icon-project"></i>
                        </div>
                        <div class="flex flex-col text-right flex-1 ml-6">
                            <h6 class="text-lg font-semibold mb-0">Especialidade</h6>
                            <span class="text-lg font-bold !text-[#ff8800] leading-tight mt-2 mb-2">
                                {{ $topSkill['skill'] ?? 'Sem skill definida' }}
                            </span>
                            @if (!empty($topCompetencies))
                                <p class="text-sm mt-1">
                                    {{ collect($topCompetencies)->pluck('name')->join(', ') }}
                                </p>
                            @else
                                <p class="text-sm mt-1 text-neutral-400">Sem competências registradas</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-span-12 2xl:col-span-4">
            <div class="card border-0 rounded-xl h-full bg-neutral-900">
                <!-- 🔸 Cabeçalho -->
                <div class="card-header border-b rounded-xl bg-[#ff8800] px-6 py-4">
                    <h6 class="font-bold text-lg text-black">Tarefas do dia</h6>
                </div>

                <!-- 🔸 Corpo -->
                <div class="card-body p-6">
                    <!-- 🔶 Dias da semana -->
                    <div class="grid grid-cols-7 gap-2 mb-6 text-center">

                        @foreach ($calendarDays as $day)
                            {{-- DIA SELECIONADO --}}
                            @if ($day['is_active'])
                                <button type="button" data-day="{{ $day['date'] }}"
                                    class="day-selector flex flex-col items-center justify-center rounded-2xl px-2 py-2
                       bg-[#ff8800] shadow-sm transition">

                                    {{-- Label do dia --}}
                                    <span class="text-xs font-medium leading-tight text-black">
                                        {{ $day['label'] }}
                                    </span>

                                    {{-- Número do dia --}}
                                    <span class="text-base font-semibold text-white">
                                        {{ $day['day'] }}
                                    </span>
                                </button>

                                {{-- DIAS NORMAIS --}}
                            @else
                                <button type="button" data-day="{{ $day['date'] }}"
                                    class="day-selector flex flex-col items-center justify-center rounded-2xl px-2 py-2 transition
                       bg-white dark:bg-black
                       text-neutral-900 dark:text-white">

                                    {{-- Label sempre laranja --}}
                                    <span class="text-xs font-medium leading-tight text-[#ff8800]">
                                        {{ $day['label'] }}
                                    </span>

                                    {{-- Número muda conforme o modo --}}
                                    <span class="text-base font-semibold text-neutral-900 dark:text-white">
                                        {{ $day['day'] }}
                                    </span>
                                </button>
                            @endif
                        @endforeach

                    </div>


                    <!-- 🔶 Conteúdo de tarefas -->
                    <div id="daily-tasks-container">
                        @include('dashboard.partials.daily-tasks', [
                            'dailyTasks' => $dailyTasks,
                            'selectedDate' => $selectedDate,
                        ])
                    </div>
                </div>
            </div>
        </div>

    </div>

    <h1 class="text-lg font-bold mt-10">Painel Resumo</h1>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mt-5">
        @foreach ($highlightCards as $h)
            <div
                class="card flex items-center justify-between px-6 py-5 rounded-xl border-0 shadow-none transition bg-neutral-900">

                <!-- Ícone -->
                <div class="icon-area flex items-center justify-center w-16 h-16">
                    <i class="{{ $h['icon'] }} icon-project fa-2x "></i>
                </div>

                <!-- Textos -->
                <div class="flex flex-col text-right flex-1 ml-6">
                    <h6 class="text-lg font-semibold mb-1">{{ $h['title'] }}</h6>

                    <span class="text-5xl font-bold !text-[#ff8800] leading-none">
                        {{ $h['value'] }}
                    </span>

                    <p class="text-sm mt-1">{{ $h['meta'] }}</p>
                </div>

            </div>
        @endforeach
    </div>


    <div class="grid grid-cols-1 mt-6">
        <div
            class="card bg-white dark:bg-neutral-900 border border-neutral-300 dark:border-neutral-800 rounded-3xl overflow-hidden">

            <!-- HEADER -->
            <div
                class="card-header flex justify-between items-center px-6 py-6 border-b border-neutral-300 dark:border-neutral-800">
                <div>
                    <h6 class="card-title mb-0 text-lg font-semibold text-neutral-800 dark:text-white">
                        Tarefas Pendentes
                    </h6>
                    <p class="text-sm text-neutral-500 dark:text-neutral-400 mt-1">
                        Priorizamos itens em andamento e próximos do prazo planejado.
                    </p>
                </div>

                <!-- STATUS + BUSCA -->
                <form method="GET" action="{{ route('dashboard') }}"
                    class="flex flex-col gap-3 md:flex-row md:items-center">

                    @foreach ($tableFormParams as $param => $value)
                        @if (is_array($value))
                            @foreach ($value as $item)
                                <input type="hidden" name="{{ $param }}[]" value="{{ $item }}">
                            @endforeach
                        @else
                            <input type="hidden" name="{{ $param }}" value="{{ $value }}">
                        @endif
                    @endforeach

                    <select name="status"
                        class="form-select form-select-sm w-full md:w-40 bg-white dark:bg-neutral-800
                       border border-neutral-300 dark:border-neutral-700
                       text-neutral-800 dark:text-white rounded-lg px-3 py-2 text-sm
                       focus:border-[#ff8800] focus:ring-0">
                        <option value="">Todos</option>
                        @foreach ($taskStatuses as $value => $label)
                            <option value="{{ $value }}" @selected($taskStatusFilter === $value)>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>

                    <div class="relative w-full md:w-72">
                        <input type="text" name="q" value="{{ $search }}"
                            class="w-full bg-white dark:bg-neutral-800 border border-neutral-300 dark:border-neutral-700
                               rounded-lg pl-10 pr-3 py-2 text-sm
                               text-neutral-800 dark:text-white
                               focus:border-[#ff8800] focus:ring-0"
                            placeholder="Buscar tarefa, projeto ou responsável...">

                        <span
                            class="absolute inset-y-0 left-0 pl-3 flex items-center text-neutral-500 dark:text-neutral-400">
                            <i class="fa-solid fa-magnifying-glass"></i>
                        </span>
                    </div>
                </form>
            </div>

            <!-- FILTROS ADICIONAIS -->
            <div class="filters-bar grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 px-6 pb-0 pt-4">

                <!-- CLIENTE -->
                <form method="GET" action="{{ route('dashboard') }}" class="w-full">
                    <select name="client"
                        class="w-full bg-white dark:bg-neutral-800 border border-neutral-300 dark:border-neutral-700
                           text-neutral-800 dark:text-white rounded-lg px-3 py-4 text-sm
                           focus:border-[#ff8800] focus:ring-0">
                        <option value="">Cliente</option>
                        @foreach ($clientOptions as $client)
                            <option value="{{ $client->id }}" @selected($clientFilter == $client->id)>
                                {{ $client->name }}
                            </option>
                        @endforeach
                    </select>
                </form>

                <!-- PROFISSIONAL -->
                <form method="GET" action="{{ route('dashboard') }}" class="w-full">
                    <select name="professional"
                        class="w-full bg-white dark:bg-neutral-800 border border-neutral-300 dark:border-neutral-700
                           text-neutral-800 dark:text-white rounded-lg px-3 py-4 text-sm
                           focus:border-[#ff8800] focus:ring-0">
                        <option value="">Profissional</option>
                        @foreach ($professionalOptions as $professional)
                            <option value="{{ $professional->id }}" @selected($professionalFilter == $professional->id)>
                                {{ $professional->name }}
                            </option>
                        @endforeach
                    </select>
                </form>

                <!-- SKILL -->
                <form method="GET" action="{{ route('dashboard') }}" class="w-full">
                    <select name="skill"
                        class="w-full bg-white dark:bg-neutral-800 border border-neutral-300 dark:border-neutral-700
                           text-neutral-800 dark:text-white rounded-lg px-3 py-4 text-sm
                           focus:border-[#ff8800] focus:ring-0">
                        <option value="">Área (Habilidade)</option>
                        @foreach ($skillOptions as $skill)
                            <option value="{{ $skill->id }}" @selected($skillFilter == $skill->id)>
                                {{ $skill->name }}
                            </option>
                        @endforeach
                    </select>
                </form>

                <!-- DATA -->
                <form method="GET" action="{{ route('dashboard') }}" class="w-full">
                    <input type="date" name="deadline" value="{{ $deadlineFilter }}"
                        class="w-full bg-white dark:bg-neutral-800 border border-neutral-300 dark:border-neutral-700
                           text-neutral-800 dark:text-white rounded-lg px-3 py-4 text-sm
                           focus:border-[#ff8800] focus:ring-0">
                </form>
            </div>

            <!-- TABELA -->
            <div class="card-body overflow-x-auto">

                <table
                    class="w-full text-sm text-center border border-neutral-300 dark:border-neutral-800
                       rounded-xl overflow-hidden border-separate border-spacing-0">

                    <!-- THEAD -->
                    <thead class="bg-[#ff8800] text-black dark:text-white">
                        <tr>
                            <th class="py-3 px-4 first:rounded-tl-xl border-b border-neutral-300 dark:border-neutral-800">
                                Tarefa
                            </th>
                            <th class="py-3 px-4 border-b border-neutral-300 dark:border-neutral-800">Área</th>
                            <th class="py-3 px-4 border-b border-neutral-300 dark:border-neutral-800">Tecnologia</th>
                            <th class="py-3 px-4 border-b border-neutral-300 dark:border-neutral-800">Responsável</th>
                            <th class="py-3 px-4 border-b border-neutral-300 dark:border-neutral-800">Data Limite</th>
                            <th class="py-3 px-4 border-b border-neutral-300 dark:border-neutral-800">Status</th>
                            <th class="py-3 px-4 last:rounded-tr-xl border-b border-neutral-300 dark:border-neutral-800">
                                Ação
                            </th>
                        </tr>
                    </thead>

                    <!-- TBODY -->
                    <tbody class="bg-white dark:bg-neutral-900">

                        @foreach ($tasks as $task)
                            @php
                                $badge = $statusBadges[$task->status] ?? ['label' => ucfirst($task->status)];

                                $badgeTheme = match ($task->status) {
                                    \App\Models\ProjectTask::STATUS_PENDING => 'badge-pendent',
                                    \App\Models\ProjectTask::STATUS_IN_PROGRESS => 'badge-inprogress',
                                    \App\Models\ProjectTask::STATUS_DONE => 'badge-completed',
                                    default => 'badge-inprogress',
                                };
                            @endphp

                            <tr class="hover:bg-neutral-100 dark:hover:bg-neutral-800 transition">

                                <td class="py-3 px-4 border-t border-neutral-300 dark:border-neutral-800">
                                    {{ $task->title }}
                                </td>

                                <td class="py-3 px-4 border-t border-neutral-300 dark:border-neutral-800">
                                    {{ optional($task->skill)->name ?? 'Sem skill' }}
                                </td>

                                <td class="py-3 px-4 border-t border-neutral-300 dark:border-neutral-800">
                                    {{ optional($task->project)->name ?? 'Sem projeto' }}
                                </td>

                                <td class="py-3 px-4 border-t border-neutral-300 dark:border-neutral-800">
                                    {{ optional($task->assignedUser)->name ?? 'Não definido' }}
                                </td>

                                <td class="py-3 px-4 border-t border-neutral-300 dark:border-neutral-800">
                                    {{ $task->planned_at ? $task->planned_at->format('d M Y') : 'Sem prazo' }}
                                </td>

                                <td class="py-3 px-4 border-t border-neutral-300 dark:border-neutral-800">
                                    <span
                                        class="inline-flex items-center gap-1 px-4 py-2 rounded-full text-xs font-medium {{ $badgeTheme }}">
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
                        @endforeach

                        @if ($tasks->isEmpty())
                            <tr>
                                <td colspan="7"
                                    class="py-8 text-center text-sm text-neutral-500 dark:text-neutral-400">
                                    Nenhuma tarefa encontrada.
                                </td>
                            </tr>
                        @endif

                    </tbody>
                </table>
            </div>

            <!-- PAGINAÇÃO -->
            <div class="px-6 py-4 border-t  border-neutral-300 dark:border-neutral-800">
                {{ $tasks->withQueryString()->links() }}
            </div>

        </div>
    </div>


    <div class="grid grid-cols-1 xl:grid-cols-2 gap-6 mt-8">
        <div class="card bg-neutral-900 border-0 rounded-3xl overflow-hidden">
            <div class="card-header flex items-center justify-between px-6 py-4 border-b border-neutral-700">
                <h6 class="text-lg font-semibold text-black dark:text-white">Projetos por mês</h6>
                <form method="GET" action="{{ route('dashboard') }}" class="flex items-center gap-3">
                    @foreach ($chartFormParams as $param => $value)
                        @if (is_array($value))
                            @foreach ($value as $item)
                                <input type="hidden" name="{{ $param }}[]" value="{{ $item }}">
                            @endforeach
                        @else
                            <input type="hidden" name="{{ $param }}" value="{{ $value }}">
                        @endif
                    @endforeach
                    <label class="text-sm text-neutral-300" for="chart_year">Ano</label>
                    <select id="chart_year" name="chart_year"
                        class="form-select form-select-sm bg-neutral-800 border border-neutral-700 text-white rounded-lg px-3 py-2 text-sm"
                        onchange="this.form.submit()">
                        @foreach ($availableYears as $year)
                            <option value="{{ $year }}" @selected($year === $chartYear)>{{ $year }}</option>
                        @endforeach
                    </select>
                </form>
            </div>
            <div class="card-body p-6">
                <div id="project-month-chart" class="min-h-[320px]"></div>
            </div>
        </div>
        <div class="card bg-neutral-900 border-0 rounded-3xl overflow-hidden">
            <div class="card-header flex items-center justify-between px-6 py-4 border-b border-neutral-700">
                <h6 class="text-lg font-semibold text-black dark:text-white">Tarefas por status</h6>
                <span class="text-sm text-neutral-400">Reporte Semanal</span>
            </div>
            <div class="card-body p-6">
                <div id="weekly-status-chart" class="min-h-[320px]"></div>
            </div>
        </div>
        <div class="card bg-neutral-900 border-0 rounded-3xl overflow-hidden">
            <div class="card-header px-6 py-4 border-b border-neutral-700">
                <h6 class="text-lg font-semibold text-black dark:text-white">Tarefas por skill</h6>
            </div>
            <div class="card-body p-6 text-center">
                <div id="tasks-skill-donut" class="mx-auto"></div>
            </div>
        </div>
        <div class="card bg-neutral-900 border-0 rounded-3xl overflow-hidden">
            <div class="card-header px-6 py-4 border-b border-neutral-700">
                <h6 class="text-lg font-semibold text-black dark:text-white">Tarefas realizadas na semana</h6>
            </div>
            <div class="card-body p-6">
                <div id="weekly-completed-line" class="min-h-[320px]"></div>
            </div>
        </div>
    </div>
@endsection

@php
    $projectMonthChartJson = json_encode(
        $projectMonthChart,
        JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT,
    );
    $weeklyStatusChartJson = json_encode(
        $weeklyStatusChart,
        JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT,
    );
    $tasksBySkillJson = json_encode(
        $analytics['tasksBySkill'],
        JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT,
    );
    $weeklyCompletedJson = json_encode(
        $analytics['weeklyCompleted'],
        JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT,
    );
    $dayTasksEndpoint = route('dashboard.day-tasks');
    $activeDayString = $selectedDate->toDateString();
    $script = <<<JS
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const projectMonthChartData = $projectMonthChartJson;
            const weeklyStatusChartData = $weeklyStatusChartJson;
            const tasksBySkillData = $tasksBySkillJson;
            const weeklyCompletedData = $weeklyCompletedJson;

            const isDark = document.documentElement.classList.contains('dark') ||
                localStorage.theme === 'dark' ||
                (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches);

            const axisColor = isDark ? '#f3f4f6' : '#374151';
            const mutedColor = isDark ? '#9ca3af' : '#6b7280';
            const gridColor = isDark ? '#1f2937' : '#e5e7eb';
            const legendColor = isDark ? '#f5f5f5' : '#262626';


            new ApexCharts(document.querySelector('#project-month-chart'), {
                chart: {
                    type: 'bar',
                    height: 320,
                    toolbar: {
                        show: false
                    },
                    background: 'transparent',
                },

                theme: {
                    mode: isDark ? 'dark' : 'light',
                    palette: 'palette1',
                    monochrome: {
                        enabled: false
                    }
                    // ❌ foreColor removido
                },

                legend: {
                    labels: {
                        colors: legendColor // agora SEM override
                    }
                },

                series: projectMonthChartData.series,
                colors: ['#487fff', '#ff8800'],

                plotOptions: {
                    bar: {
                        borderRadius: 6,
                        columnWidth: '40%',
                    },
                },

                dataLabels: {
                    enabled: false
                },
                stroke: {
                    show: true,
                    width: 2,
                    colors: ['transparent']
                },

                xaxis: {
                    categories: projectMonthChartData.labels,
                    labels: {
                        style: {
                            colors: axisColor
                        }
                    }
                },

                yaxis: {
                    labels: {
                        style: {
                            colors: mutedColor
                        }
                    }
                },

                grid: {
                    borderColor: gridColor,
                    strokeDashArray: 4,
                },
            }).render();

            const statusColors = weeklyStatusChartData.series.map(series => {
                const name = series.name.toLowerCase();
                if (name.includes('pendente')) return '#ff0000';
                if (name.includes('andamento')) return '#ff8800';
                if (name.includes('conclu')) return '#008800';
                return '#ff8800';
            });

            new ApexCharts(document.querySelector('#weekly-status-chart'), {
                chart: {
                    type: 'bar',
                    height: 320,
                    toolbar: {
                        show: false
                    },
                },
                series: weeklyStatusChartData.series,
                colors: statusColors,
                plotOptions: {
                    bar: {
                        borderRadius: 6,
                        columnWidth: '40%',
                    },
                },
                dataLabels: {
                    enabled: false
                },
                xaxis: {
                    categories: weeklyStatusChartData.labels,
                    labels: {
                        style: {
                            colors: '#f3f4f6'
                        }
                    },
                },
                yaxis: {
                    labels: {
                        style: {
                            colors: '#94a3b8'
                        },
                    },
                },
                grid: {
                    borderColor: '#1f2937',
                    strokeDashArray: 4,
                },
                legend: {
                    labels: {
                        colors: legendColor,
                    },
                },
            }).render();

            new ApexCharts(document.querySelector('#tasks-skill-donut'), {
                chart: {
                    type: 'donut',
                    height: 320,
                },
                series: tasksBySkillData.data,
                labels: tasksBySkillData.labels,
                legend: {
                    position: 'bottom',
                    labels: {
                        colors: legendColor
                    }
                },
                colors: ['#22c55e', '#3b82f6', '#f97316', '#ef4444', '#fde047', '#6366f1'],
                dataLabels: {
                    enabled: false
                },
            }).render();

            new ApexCharts(document.querySelector('#weekly-completed-line'), {
                chart: {
                    type: 'line',
                    height: 320,
                    toolbar: {
                        show: false
                    },
                },
                series: [{
                    name: 'Conclusões',
                    data: weeklyCompletedData.data,
                }],
                stroke: {
                    curve: 'smooth',
                    width: 3,
                },
                markers: {
                    size: 4
                },
                dataLabels: {
                    enabled: true,
                    style: {
                        colors: ['#0f172a']
                    },
                    background: {
                        enabled: true,
                        foreColor: '#f97316',
                        borderRadius: 4,
                        padding: 4,
                    },
                },
                xaxis: {
                    categories: weeklyCompletedData.labels,
                    labels: {
                        style: {
                            colors: '#f3f4f6'
                        }
                    },
                },
                yaxis: {
                    labels: {
                        style: {
                            colors: '#94a3b8'
                        }
                    },
                },
                grid: {
                    borderColor: '#1f2937',
                    strokeDashArray: 4,
                },
                legend: {
                    labels: {
                        colors: legendColor,
                    },
                },
            }).render();

            const dayTasksEndpoint = '$dayTasksEndpoint';
            const dailyTasksContainer = document.getElementById('daily-tasks-container');
            const dayButtons = document.querySelectorAll('[data-day]');
            let activeDay = '$activeDayString';

            const setActiveDay = (day) => {
                dayButtons.forEach((button) => {
                    const isActive = button.dataset.day === day;
                    button.classList.toggle('bg-[#ff8800]', isActive);
                    button.classList.toggle('text-white', isActive);
                    button.classList.toggle('bg-neutral-900', !isActive);
                    button.classList.toggle('text-[#ff8800]', !isActive);
                });
            };

            setActiveDay(activeDay);

            const loadDayTasks = async (day) => {
                try {
                    const response = await fetch(`${dayTasksEndpoint}?day=\${encodeURIComponent(day)}`, {
                        headers: {
                            'Accept': 'application/json'
                        },
                    });
                    if (!response.ok) {
                        throw new Error('Falha ao carregar tarefas');
                    }
                    const payload = await response.json();
                    dailyTasksContainer.innerHTML = payload.html;
                    activeDay = payload.date;
                    setActiveDay(activeDay);
                } catch (error) {
                    console.error(error);
                }
            };

            dayButtons.forEach((button) => {
                button.addEventListener('click', () => {
                    const day = button.dataset.day;
                    if (!day || day === activeDay) {
                        return;
                    }
                    loadDayTasks(day);
                });
            });
        });
    </script>
    JS;
@endphp
