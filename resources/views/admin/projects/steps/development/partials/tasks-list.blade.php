<div class="space-y-4" data-tasks-list>
    <div>
        <h4 class="text-base font-semibold text-gray-800">Tarefas por skill</h4>
        <p class="text-sm text-gray-500">Use os accordions para alternar entre as áreas e os status.</p>
    </div>

    @foreach ($taskGroups as $skillKey => $tasks)
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
                                <span class="ml-1 text-xs text-gray-400"
                                    data-task-status-count="{{ $skillKey }}::{{ $statusKey }}">{{ $statusCount }}</span>
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
                            <div class="space-y-4 py-4" data-task-status-container="{{ $skillKey }}::{{ $statusKey }}">
                                @forelse ($statusTasks as $task)
                                    @include('admin.projects.steps.development.partials.task-card', [
                                        'task' => $task,
                                        'teamMembers' => $teamMembers,
                                        'skillOptions' => $skillOptions,
                                        'statusBadges' => $statusBadges,
                                    ])
                                @empty
                                    <div class="border border-dashed border-gray-200 rounded-md p-4 text-sm text-gray-500"
                                        data-empty-state>
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
    @endforeach
    @if ($taskGroups->isEmpty())
        <div class="border border-dashed border-gray-300 rounded-lg p-6 text-center text-gray-500">
            Ainda não existem tarefas cadastradas para este projeto.
        </div>
    @endif
</div>
