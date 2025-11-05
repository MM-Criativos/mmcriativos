<div class="mb-6 p-4 border rounded-lg bg-white dark:bg-dark-800">
    <div class="flex items-center justify-between">
        <div>
            <div class="text-sm text-gray-600 dark:text-gray-300">Status do planejamento</div>

            @php
                $planning = $project->planning;
                $status = $planning?->status ?? 'not_started';
                $map = [
                    'not_started' => [
                        'label' => 'Não iniciado',
                        'classes' => 'bg-red-100 text-red-800 border border-red-200',
                    ],
                    'in_progress' => [
                        'label' => 'Em progresso',
                        'classes' => 'bg-yellow-100 text-yellow-800 border border-yellow-200',
                    ],
                    'completed' => [
                        'label' => 'Finalizado',
                        'classes' => 'bg-green-100 text-green-800 border border-green-200',
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
            <div>Início: {{ optional($planning?->started_at ?? $project->created_at)->format('d/m/Y H:i') }}</div>
            <div>Conclusão: {{ optional($planning?->completed_at)->format('d/m/Y H:i') ?? '—' }}</div>
        </div>
    </div>
</div>
