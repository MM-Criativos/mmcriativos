@extends('layouts.app')

@php
    $title = 'Projetos';
    $subTitle = 'Projetos em andamento';
@endphp

@section('content')
    <div class="card h-full p-0 rounded-xl overflow-hidden border-0">
        <div class="card-header border-b border-neutral-200 dark:border-neutral-600 pb-0 pt-0 px-0">
            <div class="px-6 py-6 flex items-center justify-between">
                <div>
                    <h2 class="text-xl font-semibold text-neutral-900 dark:text-white">Em andamento</h2>
                    <p class="text-sm text-neutral-500 dark:text-neutral-400 mt-1">Exiba os projetos ativos e edite ou exclua
                        conforme necessário.</p>
                </div>
                <a href="{{ route('admin.projects.create') }}"
                    class="btn-mmcriativos inline-flex items-center px-6 py-3 rounded">
                    Adicionar Projeto
                </a>
            </div>
        </div>
        <div class="card-body p-6">
            @if ($projects->isEmpty())
                <p class="text-gray-600 dark:text-gray-300">Nenhum projeto em andamento.</p>
            @else
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 2xl:grid-cols-4 gap-6">
                    @foreach ($projects as $project)
                        <div
                            class="hover-scale-img border border-neutral-200 dark:border-neutral-600 rounded-2xl overflow-hidden bg-white dark:bg-neutral-900 flex flex-col transition-transform hover:scale-[1.02]">
                            @php
                                $cover = $project->cover;
                                $isVideo =
                                    $cover &&
                                    \Illuminate\Support\Str::endsWith(\Illuminate\Support\Str::lower($cover), [
                                        '.mp4',
                                        '.webm',
                                        '.ogg',
                                        '.mov',
                                    ]);
                            @endphp
                            @if ($cover)
                                <div class="max-h-[150px] overflow-hidden">
                                    @if ($isVideo)
                                        <video class="w-full h-full object-cover" autoplay muted loop playsinline
                                            preload="auto">
                                            <source src="{{ asset($cover) }}">
                                        </video>
                                    @else
                                        <img src="{{ asset($cover) }}" alt="{{ $project->name }}"
                                            class="hover-scale-img__img w-full h-full object-cover" draggable="false">
                                    @endif
                                </div>
                            @endif

                            <div class="py-4 px-6 flex-1 flex flex-col justify-between">
                                <div class="mb-3">
                                    <h3 class="text-white dark:text-white text-lg font-semibold">{{ $project->name }}</h3>
                                    <p class="text-sm text-gray-300">{{ optional($project->client)->name }}</p>
                                </div>
                                <div class="flex items-center justify-between mt-auto">
                                    <a href="{{ route('admin.projects.steps.show', ['project' => $project, 'tab' => 'delivery']) }}"
                                        class="inline-flex items-center gap-1 px-5 py-3 btn-mmcriativos rounded-md">
                                        <i class="fa-duotone fa-regular fa-pen-to-square icon-project mr-2"></i>
                                        <span>Editar</span>
                                    </a>
                                    <form method="POST" action="{{ route('admin.projects.destroy', $project) }}"
                                        onsubmit="return confirm('Tem certeza que deseja apagar este projeto?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                            class="inline-flex items-center gap-1 px-5 py-4 rounded bg-red-500 text-white border-red-500 hover:bg-[#f5f5f5] dark:hover:bg-[#262626] hover:text-red-500">
                                            <i class="fa-regular fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
@endsection
