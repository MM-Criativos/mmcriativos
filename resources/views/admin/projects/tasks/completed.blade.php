@php
    $statusBadge = [
        \App\Models\ProjectTask::STATUS_DONE => 'bg-green-100 text-green-700 border border-green-200',
        \App\Models\ProjectTask::STATUS_IN_PROGRESS => 'bg-yellow-100 text-yellow-700 border border-yellow-200',
        \App\Models\ProjectTask::STATUS_PENDING => 'bg-gray-100 text-gray-700 border border-gray-200',
    ];
    $backUrl = $projectId
        ? route('admin.projects.steps.show', ['project' => $projectId, 'tab' => 'development'])
        : route('admin.projects.progress.index');
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">Tarefas concluídas</h2>
                <p class="text-sm text-gray-500">Consulte todas as entregas finalizadas, filtre por skill ou busque por título.</p>
            </div>
            <a href="{{ $backUrl }}"
               class="inline-flex items-center gap-2 px-4 py-2 border border-gray-300 rounded-md text-sm text-gray-700 hover:bg-gray-50">
                <i class="fa-solid fa-arrow-left"></i>
                Voltar
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <form method="GET" action="{{ route('admin.project-tasks.completed') }}"
                        class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        @if ($projectId)
                            <input type="hidden" name="project_id" value="{{ $projectId }}">
                        @endif
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Buscar</label>
                            <input type="search" name="q" value="{{ $search }}"
                                placeholder="Busque pelo título da tarefa ou de uma subtarefa"
                                class="w-full border-gray-300 rounded-md text-sm focus:border-orange-500 focus:ring-orange-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Filtrar por skill</label>
                            <select name="skill_id"
                                class="w-full border-gray-300 rounded-md text-sm focus:border-orange-500 focus:ring-orange-500">
                                <option value="">Todas as skills</option>
                                @foreach ($skills as $skill)
                                    <option value="{{ $skill->id }}" @selected((string) $selectedSkill === (string) $skill->id)>
                                        {{ $skill->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="md:col-span-3 flex flex-wrap gap-3 justify-end">
                            <a href="{{ route('admin.project-tasks.completed', array_filter(['project_id' => $projectId])) }}"
                                class="inline-flex items-center gap-2 px-4 py-2 rounded-md border text-sm text-gray-600 hover:bg-gray-50">
                                Limpar filtros
                            </a>
                            <button type="submit"
                                class="inline-flex items-center gap-2 px-5 py-2.5 bg-orange-600 text-white rounded-md text-sm font-medium hover:bg-orange-700">
                                <i class="fa-solid fa-filter"></i>
                                Filtrar
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="space-y-4">
                @forelse ($tasks as $task)
                    <div x-data="{ open: false }" class="bg-white border border-gray-200 rounded-lg shadow-sm overflow-hidden">
                        <button type="button" @click="open = !open"
                            class="w-full px-4 py-3 flex flex-col gap-2 md:flex-row md:items-center md:justify-between text-left">
                            <div>
                                <h3 class="text-base font-semibold text-gray-900">{{ $task->title }}</h3>
                                <p class="text-sm text-gray-500">
                                    Projeto: {{ $task->project?->name ?? 'Sem projeto' }} &mdash;
                                    Skill: {{ $task->skill?->name ?? 'Sem skill' }}
                                </p>
                            </div>
                            <div class="flex items-center gap-4 text-sm text-gray-600">
                                <div>
                                    <p class="text-xs uppercase tracking-widest text-gray-400">Concluída em</p>
                                    <p>{{ optional($task->completed_at)->format('d/m/Y H:i') ?? 'Sem data' }}</p>
                                </div>
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium {{ $statusBadge[$task->status] ?? 'bg-gray-100 text-gray-700 border border-gray-200' }}">
                                    {{ \App\Models\ProjectTask::STATUSES[$task->status] ?? ucfirst($task->status) }}
                                </span>
                                <i class="fa-solid text-gray-500" :class="{ 'fa-chevron-up': open, 'fa-chevron-down': !open }"></i>
                            </div>
                        </button>

                        <div x-show="open" x-transition class="border-t border-gray-100">
                            <div class="p-4 space-y-4 text-sm text-gray-700">
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                    <div>
                                        <p class="text-xs uppercase tracking-widest text-gray-400">Responsável</p>
                                        <p class="font-medium text-gray-900">
                                            {{ $task->assignedUser?->name ?? 'Não atribuído' }}
                                        </p>
                                    </div>
                                    <div>
                                        <p class="text-xs uppercase tracking-widest text-gray-400">Planejada para</p>
                                        <p>{{ optional($task->planned_at)->format('d/m/Y') ?? 'Sem previsão' }}</p>
                                    </div>
                                    <div>
                                        <p class="text-xs uppercase tracking-widest text-gray-400">Competência principal</p>
                                        <p>{{ $task->competency?->competency ?? 'Não definida' }}</p>
                                    </div>
                                </div>

                                @if ($task->description)
                                    <div>
                                        <p class="text-xs uppercase tracking-widest text-gray-400 mb-1">Descrição</p>
                                        <p class="text-gray-600">{{ $task->description }}</p>
                                    </div>
                                @endif

                                <div class="space-y-2">
                                    <p class="text-xs uppercase tracking-widest text-gray-400">Subtarefas</p>
                                    @if ($task->items->isEmpty())
                                        <div class="border border-dashed border-gray-200 rounded-md p-4 text-sm text-gray-500">
                                            Nenhuma subtarefa cadastrada para esta tarefa.
                                        </div>
                                    @else
                                        <div class="space-y-2">
                                            @foreach ($task->items as $item)
                                                @php
                                                    $itemCompetency = $item->competency?->competency ?? $task->competency?->competency ?? 'Competência não definida';
                                                    $inherited = ! $item->competency && $task->competency;
                                                @endphp
                                                <div class="border border-gray-200 rounded-lg p-3">
                                                    <div class="flex flex-col gap-2 md:flex-row md:items-start md:justify-between">
                                                        <div class="space-y-1">
                                                            <p class="text-sm font-medium text-gray-900">{{ $item->title }}</p>
                                                            <p class="text-xs text-gray-500">
                                                                {{ $itemCompetency }}
                                                                @if ($inherited)
                                                                    <span class="ml-1 uppercase tracking-widest text-[10px] text-gray-400">(herdada)</span>
                                                                @endif
                                                                &bull; {{ $item->assignedUser?->name ?? 'Sem responsável' }}
                                                            </p>
                                                            @if ($item->description)
                                                                <p class="text-sm text-gray-600">{{ $item->description }}</p>
                                                            @endif
                                                        </div>
                                                        <div class="flex flex-col items-end gap-1">
                                                            <span
                                                                class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium {{ $item->is_done ? 'bg-green-100 text-green-700 border border-green-200' : 'bg-gray-100 text-gray-700 border border-gray-200' }}">
                                                                {{ $item->is_done ? 'Concluída' : 'Pendente' }}
                                                            </span>
                                                            @if ($item->done_at)
                                                                <p class="text-xs text-gray-500">
                                                                    Finalizada em {{ $item->done_at->format('d/m/Y H:i') }}
                                                                </p>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="bg-white border border-dashed border-gray-300 rounded-lg p-6 text-center text-gray-500">
                        Nenhuma tarefa concluída encontrada com os filtros selecionados.
                    </div>
                @endforelse
            </div>

            {{ $tasks->links() }}
        </div>
    </div>
</x-app-layout>
