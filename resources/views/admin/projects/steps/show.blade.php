<style>
    /* Wizard – esconder todos os steps */
    .wizard-fieldset {
        display: none;
        transition: opacity .2s ease;
        opacity: 0;
    }

    /* Wizard – mostrar o step ativo */
    .wizard-fieldset.show {
        display: block;
        opacity: 1;
    }
</style>

@php
    $stepsMeta = [
        'planning' => [
            'label' => 'Planejamento',
            'title' => 'Planejamento',
            'subtitle' => 'Levante as informações com o cliente',
            'number' => 1,
        ],
        'creation' => [
            'label' => 'Criação',
            'title' => 'Criação',
            'subtitle' => 'Planeje como será o layout do projeto',
            'number' => 2,
        ],
        'development' => [
            'label' => 'Desenvolvimento',
            'title' => 'Desenvolvimento',
            'subtitle' => 'Execute seu projeto com tarefas',
            'number' => 3,
        ],
        'delivery' => [
            'label' => 'Entrega',
            'title' => 'Entrega',
            'subtitle' => 'Finalize a apresentação do projeto',
            'number' => 4,
        ],
    ];
    $initialStep = array_key_first($stepsMeta);
    $title = $stepsMeta[$initialStep]['title'];
    $subTitle = $stepsMeta[$initialStep]['subtitle'];
@endphp

@extends('layouts.app')

@section('content')
    <div class="w-full grid grid-cols-1 gap-6">
        <div class="card border-0 col-span-1 w-full">
            <div class="card-body">
                <h6 class="mb-1.5 text-xl">Elaboração de projetos</h6>
                <p class="text-neutral-400">Acompanhe o passo a passo do projeto que estamos desenvolvendo</p>

                <div class="form-wizard">
                    <form action="#" method="post">
                        <div class="form-wizard-header overflow-x-auto scroll-sm pb-2 mt-8 mb-8">
                            <ul class="list-unstyled form-wizard-list style-two" id="project-steps-list">
                                @foreach ($stepsMeta as $stepKey => $meta)
                                    <li class="form-wizard-list__item {{ $loop->first ? 'active' : '' }}"
                                        data-step="{{ $stepKey }}">
                                        <div class="form-wizard-list__line">
                                            <span class="count">{{ $meta['number'] }}</span>
                                        </div>
                                        <span class="text text-xs font-semibold">{{ $meta['label'] }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        </div>

                        <div class="space-y-1">
                            <h4 class="text-lg font-semibold text-gray-900" id="step-meta-title">
                                {{ $stepsMeta[$initialStep]['title'] }}
                            </h4>
                            <p class="text-sm text-gray-500" id="step-meta-subtitle">
                                {{ $stepsMeta[$initialStep]['subtitle'] }}
                            </p>
                        </div>

                        <fieldset class="wizard-fieldset show" data-step-content="planning">
                            @include ('admin.projects.steps.planning.status', ['project' => $project])
                            @include ('admin.projects.steps.planning.scale', ['project' => $project])
                            @include ('admin.projects.steps.planning.qualitative', ['project' => $project])
                            @include ('admin.projects.steps.planning.interpretation', [
                                'project' => $project,
                            ])
                            @include ('admin.projects.steps.planning.kickoff', ['project' => $project])
                        </fieldset>

                        <fieldset class="wizard-fieldset" data-step-content="creation">
                            @include ('admin.projects.steps.creation.project-summary', [
                                'project' => $project,
                            ])
                            @include ('admin.projects.steps.creation.project-challenges', [
                                'project' => $project,
                            ])
                            @include ('admin.projects.steps.creation.project-solutions', [
                                'project' => $project,
                            ])
                            @include ('admin.projects.steps.creation.project-pages', [
                                'project' => $project,
                            ])
                            @include ('admin.projects.steps.creation.processes', ['project' => $project])
                        </fieldset>

                        <fieldset class="wizard-fieldset" data-step-content="development">
                            @include ('admin.projects.steps.development.tasks', [
                                'project' => $project,
                                'teamMembers' => $teamMembers ?? collect(),
                                'skillOptions' => $skillOptions ?? collect(),
                            ])
                        </fieldset>

                        <fieldset class="wizard-fieldset" data-step-content="delivery">
                            @include ('admin.projects.steps.delivery.training', ['project' => $project])
                            @include ('admin.projects.steps.delivery.presentation', [
                                'project' => $project,
                                'clients' => $clients ?? collect(),
                                'services' => $services ?? collect(),
                            ])
                            @include ('admin.projects.steps.delivery.post_launch', ['project' => $project])
                        </fieldset>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            const stepsMeta = @json($stepsMeta);
            const updateMeta = (key) => {
                if (!stepsMeta[key]) {
                    return;
                }
                $('#step-meta-title').text(stepsMeta[key].title);
                $('#step-meta-subtitle').text(stepsMeta[key].subtitle);
                const $navTitle = $('.navbar-header h1');
                const $navSubtitle = $('.navbar-header p');
                if ($navTitle.length) {
                    $navTitle.text(stepsMeta[key].title);
                }
                if ($navSubtitle.length) {
                    $navSubtitle.text(stepsMeta[key].subtitle);
                }
            };

            const showStep = (stepKey) => {
                const $items = $('.form-wizard-list__item');
                $items.removeClass('active');
                $items.filter('[data-step="' + stepKey + '"]').addClass('active');
                $('.wizard-fieldset').removeClass('show');
                $('.wizard-fieldset[data-step-content="' + stepKey + '"]').addClass('show');
                updateMeta(stepKey);
            };

            $('#project-steps-list').on('click', '.form-wizard-list__item', function() {
                const stepKey = $(this).data('step');
                showStep(stepKey);
            });

            showStep('{{ $initialStep }}');
        });
    </script>
@endpush
