<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-100 leading-tight">Projetos · Concluídos</h2>
            <a href="{{ route('admin.projects.create') }}"
               class="inline-flex items-center px-6 py-4 bg-orange-600 text-white rounded border border-transparent font-semibold text-xs uppercase tracking-widest hover:bg-white hover:text-orange-600 hover:border-orange-600 hover:border-solid">
                Adicionar Projeto
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="mb-4 p-3 bg-green-100 text-green-800 rounded">{{ session('status') }}</div>
            @endif
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    @if ($projects->isEmpty())
                        <p class="text-gray-600">Nenhum projeto concluído ainda.</p>
                    @else
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 pb-2 overflow-x-auto sm:overflow-x-visible">
                            @foreach ($projects as $project)
                                <div class="min-w-[260px] sm:min-w-0 border rounded-lg shadow-sm bg-white overflow-hidden flex flex-col transition-transform hover:scale-[1.02] hover:shadow-md">
                                    @php
                                        $cover = $project->cover;
                                        $isVideo = $cover && \Illuminate\Support\Str::endsWith(\Illuminate\Support\Str::lower($cover), ['.mp4','.webm','.ogg','.mov']);
                                    @endphp
                                    @if ($cover)
                                        @if ($isVideo)
                                            <video class="w-full h-36 object-cover" autoplay muted loop playsinline preload="auto">
                                                <source src="{{ asset($cover) }}">
                                            </video>
                                        @else
                                            <img src="{{ asset($cover) }}" alt="{{ $project->name }}" class="w-full h-36 object-cover" draggable="false">
                                        @endif
                                    @endif

                                    <div class="p-4 flex-1 flex flex-col justify-between">
                                        <div class="mb-3">
                                            <h3 class="font-semibold text-gray-800 text-base">{{ $project->name }}</h3>
                                            <p class="text-sm text-gray-600">{{ optional($project->client)->name }}</p>
                                        </div>

                                        <div class="flex items-center justify-between mt-auto">
                                            <a href="{{ route('admin.projects.steps.show', ['project' => $project, 'tab' => 'delivery']) }}"
                                               class="inline-flex items-center gap-1 px-5 py-3 bg-orange-600 text-white rounded border border-transparent hover:bg-white hover:text-orange-600 hover:border-orange-600 hover:border-solid text-sm transition-colors duration-200">
                                                <i class="fa-regular fa-pen-to-square"></i>
                                                <span>Editar</span>
                                            </a>
                                            <form method="POST" action="{{ route('admin.projects.destroy', $project) }}" onsubmit="return confirm('Tem certeza que deseja apagar este projeto?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="inline-flex items-center gap-1 px-5 py-3 bg-red-600 text-white rounded hover:bg-red-700 text-sm">
                                                    <i class="fa-regular fa-trash"></i>
                                                    <span>Apagar</span>
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
        </div>
    </div>

</x-app-layout>
