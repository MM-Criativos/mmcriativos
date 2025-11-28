<div id="daily-tasks-section" class="flex flex-col gap-4">
    @if ($dailyTasks->isEmpty())
        <p class="text-sm text-neutral-400 text-center">
            Nenhuma tarefa encontrada para o dia selecionado.
        </p>
    @else
        @foreach ($dailyTasks as $task)
            @php
                $isLate =
                    $task->planned_at &&
                    $task->planned_at->isBefore($selectedDate) &&
                    $task->status !== \App\Models\ProjectTask::STATUS_DONE;
                $borderClass = match ($task->status) {
                    \App\Models\ProjectTask::STATUS_PENDING => 'border-[#ff0000]',
                    \App\Models\ProjectTask::STATUS_IN_PROGRESS => 'border-[#ff8800]',
                    \App\Models\ProjectTask::STATUS_DONE => 'border-[#008800]',
                    default => 'border-neutral-500',
                };
            @endphp
            <div
                class="flex items-center justify-between border-l-4 {{ $borderClass }} pl-4 py-2 bg-[#f5f5f5] dark:bg-[#262626]">
                <div>
                    <h6
                        class="text-base font-semibold {{ $isLate ? 'text-red-400 dark:text-red-400' : 'text-orange-500 dark:text-orange-500' }}">
                        {{ $task->title }}
                    </h6>
                    <p class="text-sm text-neutral-400">{{ $task->skill->name ?? 'Sem área definida' }}</p>
                    <p class="text-xs text-neutral-500">{{ $task->project->name ?? 'Projeto não vinculado' }}</p>
                </div>
                @if ($task->project)
                    <a href="{{ route('admin.projects.steps.show', [$task->project, 'tab' => 'development']) }}"
                        class="tasks-view-btn text-sm text-black dark:text-white px-3 py-1">
                        Ver
                    </a>
                @else
                    <span class="text-sm text-neutral-500">Projeto indisponível</span>
                @endif
            </div>
        @endforeach
    @endif
    <a href="{{ route('admin.tasks.index') }}"
        class="tasks-btn mt-3 text-center text-sm text-black dark:text-white font-semibold rounded-md py-2"
        style="z-index: 1">
        Ver todas as tarefas
    </a>
</div>
