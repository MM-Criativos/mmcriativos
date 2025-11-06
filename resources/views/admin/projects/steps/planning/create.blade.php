@php
    use App\Models\QualitativeTemplate;
    $templates = QualitativeTemplate::where('is_active', true)
        ->orderBy('category')
        ->orderBy('sort_order')
        ->get()
        ->groupBy('category');
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-100 leading-tight">
                Criar Questionário · {{ $project->name }}
            </h2>
            <a href="{{ route('admin.projects.steps.show', $project) }}"
                class="inline-flex items-center gap-2 px-4 py-2 bg-white text-gray-700 rounded border border-gray-300 hover:bg-gray-50">
                <i class="fa-solid fa-arrow-left"></i>
                <span>Voltar</span>
            </a>
        </div>
    </x-slot>

    @if (session('status'))
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-6">
            <div class="bg-green-50 border border-green-200 rounded-md p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <i class="fa-solid fa-check-circle text-green-400"></i>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-green-700">
                            {{ session('status') }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <form action="{{ route('admin.projects.planning.qualitative.save', $project) }}" method="POST">
                    @csrf
                    <div class="p-6">
                        <div class="mb-4">
                            <h3 class="text-lg font-medium text-gray-900">Selecione as Perguntas do Questionário</h3>
                            <p class="mt-1 text-sm text-gray-600">
                                Escolha as perguntas que serão enviadas ao cliente.
                            </p>
                        </div>

                        <div class="space-y-8">
                            @foreach ($templates as $category => $items)
                                <div>
                                    <h4 class="text-sm font-medium text-gray-900 mb-4">{{ $category }}</h4>
                                    <div class="space-y-4">
                                        @foreach ($items as $template)
                                            <label class="flex items-start">
                                                <div class="flex h-6 items-center">
                                                    <input type="checkbox" name="template_ids[]" value="{{ $template->id }}"
                                                        class="h-4 w-4 rounded border-gray-300 text-orange-600 focus:ring-orange-600">
                                                </div>
                                                <div class="ml-3">
                                                    <span
                                                        class="text-sm font-medium text-gray-900">{{ $template->question }}</span>
                                                    @if ($template->placeholder)
                                                        <p class="text-sm text-gray-500">{{ $template->placeholder }}
                                                        </p>
                                                    @endif
                                                </div>
                                            </label>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="px-6 py-4 bg-gray-50 border-t flex justify-end">
                        <button type="submit"
                            class="inline-flex items-center gap-2 px-4 py-2 bg-orange-600 text-white rounded border border-transparent hover:bg-orange-700">
                            <i class="fa-regular fa-floppy-disk"></i>
                            <span>Salvar Questionário</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
