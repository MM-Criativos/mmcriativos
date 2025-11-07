@php
    $statusBadge = [
        \App\Models\ProjectTask::STATUS_DONE => [
            'label' => 'Concluída',
            'classes' => 'bg-green-100 text-green-700 border border-green-200',
        ],
    ];
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">Tarefas concluídas</h2>
                <p class="text-sm text-gray-500">Histórico de entregas finalizadas. Use os filtros para localizar tarefas
                    específicas.</p>
            </div>
            <a href="{{ route('admin.tasks.index') }}"
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
                    <form method="GET" action="{{ route('admin.tasks.completed') }}"
                        class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Buscar</label>
                            <input type="search" name="q" value="{{ $search }}"
                                placeholder="Busque pelo título da tarefa ou subtarefa"
                                class="w-full border-gray-300 rounded-md text-sm focus:border-orange-500 focus:ring-orange-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Projeto</label>
                            <select name="project_id"
                                class="w-full border-gray-300 rounded-md text-sm focus:border-orange-500 focus:ring-orange-500">
                                <option value="">Todos</option>
                                @foreach ($projects as $project)
                                    <option value="{{ $project->id }}" @selected((string) $selectedProject === (string) $project->id)>
                                        {{ $project->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Skill</label>
                            <select name="skill_id"
                                class="w-full border-gray-300 rounded-md text-sm focus:border-orange-500 focus:ring-orange-500">
                                <option value="">Todas</option>
                                @foreach ($skills as $skill)
                                    <option value="{{ $skill->id }}" @selected((string) $selectedSkill === (string) $skill->id)>
                                        {{ $skill->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="md:col-span-4 flex flex-wrap gap-3 justify-end">
                            <a href="{{ route('admin.tasks.completed') }}"
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
                    @php
                        $badge = $statusBadge[\App\Models\ProjectTask::STATUS_DONE];
                    @endphp
                    <div class="bg-white border border-gray-200 rounded-lg p-5 shadow-sm" x-data="{ open: false }">
                        <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-3">
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900">{{ $task->title }}</h3>
                                <p class="text-sm text-gray-500">
                                    Projeto: {{ $task->project?->name ?? 'Sem projeto' }} —
                                    Skill: {{ $task->skill?->name ?? 'Não definida' }}
                                </p>
                                @if ($task->description)
                                    <p class="text-sm text-gray-600 mt-1">
                                        {{ \Illuminate\Support\Str::limit($task->description, 160) }}</p>
                                @endif
                            </div>
                            <div class="flex flex-col items-end gap-2 text-sm text-gray-600">
                                <span
                                    class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold {{ $badge['classes'] }}">
                                    {{ $badge['label'] }}
                                </span>
                                <div class="text-right">
                                    <p class="text-xs uppercase tracking-widest text-gray-400">Concluída em</p>
                                    <p>{{ optional($task->completed_at)->format('d/m/Y H:i') ?? 'Sem registro' }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4 grid grid-cols-1 md:grid-cols-3 gap-4 text-sm text-gray-600">
                            <div>
                                <p class="text-xs uppercase tracking-widest text-gray-400">Responsável</p>
                                <p class="font-medium text-gray-900">{{ $task->assignedUser?->name ?? 'Não definido' }}
                                </p>
                            </div>
                            <div>
                                <p class="text-xs uppercase tracking-widest text-gray-400">Planejada para</p>
                                <p>{{ optional($task->planned_at)->format('d/m/Y') ?? 'Sem previsão' }}</p>
                            </div>
                            <div class="flex items-center gap-2">

                            <div class="flex items-center justify-end">
                                @if ($task->project)
                                    <a href="{{ route('admin.projects.steps.show', [$task->project, 'tab' => 'development']) }}"
                                        class="inline-flex items-center gap-2 px-3 py-1.5 text-sm font-medium border border-orange-600 text-orange-600 rounded-md hover:bg-orange-600 hover:text-white transition">
                                        <i class="fa-solid fa-arrow-up-right-from-square"></i>
                                        Projeto
                                    </a>
                                @else
                                    <span class="text-gray-500">Projeto removido</span>
                                @endif
                            </div>
                        </div>

                        <div class="mt-4 flex justify-end">
                            <button type="button" @click="open = !open"
                                class="inline-flex items-center gap-2 text-sm font-medium text-orange-600 hover:text-orange-700">
                                <i class="fa-solid" :class="open ? 'fa-chevron-up' : 'fa-chevron-down'"></i>
                                <span x-text="open ? 'Ocultar itens' : 'Ver itens'"></span>
                            </button>
                        </div>

                        <div x-show="open" x-collapse class="mt-4 border-t border-gray-100 pt-4 space-y-3">
                            @if ($task->items->isEmpty())
                                <p class="text-sm text-gray-500">Nenhum item cadastrado para esta tarefa.</p>
                            @else
                                @foreach ($task->items as $item)
                                    <div class="border border-gray-200 rounded-md p-3">
                                        <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-2">
                                            <div>
                                                <p class="text-sm font-medium text-gray-900">{{ $item->title }}</p>
                                                <p class="text-xs text-gray-500">
                                                    {{ $item->competency?->competency ?? ($task->competency?->competency ?? 'Competência não definida') }}
                                                    • {{ $item->assignedUser?->name ?? 'Sem responsável' }}
                                                </p>
                                                @if ($item->description)
                                                    <p class="text-sm text-gray-600 mt-1">{{ $item->description }}</p>
                                                @endif
                                            </div>
                                            <div class="flex flex-col items-end text-xs text-gray-500">
                                                <span
                                                    class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold {{ $item->is_done ? 'bg-green-100 text-green-700 border border-green-200' : 'bg-gray-100 text-gray-700 border border-gray-200' }}">
                                                    {{ $item->is_done ? 'Concluído' : 'Pendente' }}
                                                </span>
                                                @if ($item->done_at)
                                                    <span class="mt-1">Finalizado em
                                                        {{ $item->done_at->format('d/m/Y H:i') }}</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="bg-white border border-dashed border-gray-300 rounded-lg p-6 text-center text-gray-500">
                        Nenhuma tarefa concluída encontrada com os filtros atuais.
                    </div>
                @endforelse
            </div>

            {{ $tasks->links() }}
        </div>
    </div>
</x-app-layout>
