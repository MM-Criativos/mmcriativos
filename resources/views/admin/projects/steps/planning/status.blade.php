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

<div class="mb-6 p-4 rounded-lg bg-[#f5f5f5] dark:bg-[#2c2c2c]">
    <div class="flex items-center justify-between">
        <div>
            <div class="text-sm text-gray-600 dark:text-gray-300">Status do planejamento</div>

            @php
                $planning = $project->planning;
                $timezone = config('app.timezone') ?: date_default_timezone_get();

                $responses = collect();
                if ($planning && $planning->relationLoaded('briefingResponses')) {
                    $responses = $planning->briefingResponses->where('client_id', $project->client_id);
                }

                $kickoff = $planning?->kickoff;

                $status = 'not_started';
                $startedAt = optional($project->created_at)->timezone($timezone);
                $completedAt = null;

                if ($responses->count() > 0) {
                    $status = 'in_progress';
                    $firstResponse = $responses->sortBy('created_at')->first();
                    $startedAt = optional($firstResponse?->created_at)->timezone($timezone) ?? $startedAt;
                }

                if ($kickoff) {
                    $status = 'completed';
                    $completedAt = optional($kickoff->approved_at ?? $kickoff->updated_at)->timezone($timezone);
                    if (!$startedAt && $kickoff->created_at) {
                        $startedAt = $kickoff->created_at->timezone($timezone);
                    }
                }

                $map = [
                    'not_started' => [
                        'label' => 'Não iniciado',
                        'classes' => 'badge-pendent',
                    ],
                    'in_progress' => [
                        'label' => 'Em progresso',
                        'classes' => 'badge-inprogress',
                    ],
                    'completed' => [
                        'label' => 'Finalizado',
                        'classes' => 'badge-completed',
                    ],
                ];
                $label = $map[$status]['label'] ?? ucfirst($status);
                $classes = $map[$status]['classes'] ?? 'bg-gray-100 text-gray-800 border border-gray-200';
            @endphp

            <span
                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium mt-2 {{ $classes }}">
                {{ $label }}
            </span>
        </div>

        <div class="text-right text-sm text-gray-600 dark:text-gray-300 leading-relaxed">
            <div>Início: {{ optional($startedAt)->format('d/m/Y') ?? '—' }}</div>
            <div>Conclusão: {{ optional($completedAt)->format('d/m/Y') ?? '—' }}</div>
        </div>
    </div>
</div>
