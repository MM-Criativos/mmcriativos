<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-100 leading-tight">
                Etapas do Projeto · {{ $project->name }}
            </h2>
            <a href="{{ route('admin.projects.steps.show', ['project' => $project, 'tab' => 'delivery']) }}"
               class="inline-flex items-center px-6 py-3 bg-orange-600 text-white rounded border border-transparent font-semibold text-xs uppercase tracking-widest hover:bg-white hover:text-orange-600 hover:border-orange-600 hover:border-solid">
                Editar Projeto
            </a>
        </div>
    </x-slot>

    @php
        $tabs = [
            'planning' => 'Planejamento',
            'creation' => 'Criação',
            'development' => 'Desenvolvimento',
            'delivery' => 'Entrega',
        ];
        $active = $tab ?? request('tab', 'planning');
        if (!array_key_exists($active, $tabs)) {
            $active = 'planning';
        }
    @endphp

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="mb-4 p-3 bg-green-100 text-green-800 rounded">{{ session('status') }}</div>
            @endif
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="px-6 pt-6">
                    <nav class="-mb-px flex space-x-6" aria-label="Tabs">
                        @foreach ($tabs as $key => $label)
                            @php $isActive = $active === $key; @endphp
                            <a href="{{ route('admin.projects.steps.show', [$project, 'tab' => $key]) }}"
                                class="whitespace-nowrap border-b-2 px-1 pb-3 text-sm font-medium {{ $isActive ? 'border-orange-600 text-orange-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                                {{ $label }}
                            </a>
                        @endforeach
                    </nav>
                </div>

                <div class="p-6 text-gray-900">
                    @if ($active === 'planning')
                        @includeIf('admin.projects.steps.planning.status', ['project' => $project])
                        @includeIf('admin.projects.steps.planning.scale', ['project' => $project])
                        @includeIf('admin.projects.steps.planning.qualitative', ['project' => $project])
                        @includeIf('admin.projects.steps.planning.interpretation', ['project' => $project])
                        @includeIf('admin.projects.steps.planning.kickoff', ['project' => $project])
                    @elseif ($active === 'creation')
                        @includeIf('admin.projects.steps.creation.project-summary', ['project' => $project])
                        @includeIf('admin.projects.steps.creation.project-challenges', [
                            'project' => $project,
                        ])
                        @includeIf('admin.projects.steps.creation.project-solutions', [
                            'project' => $project,
                        ])
                        @includeIf('admin.projects.steps.creation.project-pages', ['project' => $project])
                        @includeIf('admin.projects.steps.creation.processes', ['project' => $project])
                        @includeIf('admin.projects.steps.creation.wireframe', ['project' => $project])
                        @includeIf('admin.projects.steps.creation.content', ['project' => $project])
                        @includeIf('admin.projects.steps.creation.moodboard', ['project' => $project])
                        @includeIf('admin.projects.steps.creation.prototype', ['project' => $project])
                    @elseif ($active === 'development')
                        @includeIf('admin.projects.steps.develepoment.tasks', [
                            'project' => $project,
                            'teamMembers' => $teamMembers ?? collect(),
                            'skillOptions' => $skillOptions ?? collect(),
                        ])
                        @includeIf('admin.projects.steps.develepoment.frontend', ['project' => $project])
                        @includeIf('admin.projects.steps.develepoment.backend', ['project' => $project])
                        @includeIf('admin.projects.steps.develepoment.automations', ['project' => $project])
                    @elseif ($active === 'tests')
                        <div class="text-gray-600">Seção de testes em preparação.</div>
                    @elseif ($active === 'delivery')
                        @includeIf('admin.projects.steps.delivery.training', ['project' => $project])
                        @includeIf('admin.projects.steps.delivery.presentation', [
                            'project' => $project,
                            'clients' => $clients ?? collect(),
                            'services' => $services ?? collect(),
                        ])
                        @includeIf('admin.projects.steps.delivery.post_launch', ['project' => $project])
                    @endif
                </div>
            </div>
        </div>
    </div>

</x-app-layout>
