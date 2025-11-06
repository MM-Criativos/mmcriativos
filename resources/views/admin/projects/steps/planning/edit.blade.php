<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-100 leading-tight">
                Editar Questionário · {{ $project->name }}
            </h2>
            <a href="{{ route('admin.projects.steps.show', ['project' => $project, 'tab' => 'planning']) }}"
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

                    <div class="p-6 space-y-6">
                        <div>
                            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Atualizar perguntas do questionário</h3>
                            <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">
                                Marque as perguntas que deseja manter no formulário. Desmarcar remove a pergunta do questionário quando ele for salvo.
                            </p>
                        </div>

                        @if ($templates->isEmpty())
                            <div class="rounded-md border border-dashed border-gray-300 bg-gray-50 p-6 text-center text-sm text-gray-600">
                                Nenhuma pergunta ativa encontrada. Cadastre templates qualitativos para configurar este questionário.
                            </div>
                        @else
                            <div class="space-y-8">
                                @foreach ($templates as $category => $items)
                                    <div>
                                        <h4 class="text-sm font-semibold text-gray-900 dark:text-gray-100 mb-4">
                                            {{ $category ?? 'Perguntas' }}
                                        </h4>

                                        <div class="space-y-3">
                                            @foreach ($items as $template)
                                                <label class="flex items-start gap-3">
                                                    <input type="checkbox" name="template_ids[]" value="{{ $template->id }}"
                                                        class="mt-1 h-4 w-4 rounded border-gray-300 text-orange-600 focus:ring-orange-600"
                                                        @checked(in_array($template->id, $selectedTemplateIds, true))>
                                                    <div>
                                                        <span class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                                            {{ $template->question }}
                                                            @if ($template->is_required)
                                                                <span class="text-red-500">*</span>
                                                            @endif
                                                        </span>
                                                        @if ($template->placeholder)
                                                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                                                {{ $template->placeholder }}
                                                            </p>
                                                        @endif
                                                    </div>
                                                </label>
                                            @endforeach
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>

                    <div class="px-6 py-4 bg-gray-50 border-t flex justify-end gap-3">
                        <a href="{{ route('admin.projects.steps.show', ['project' => $project, 'tab' => 'planning']) }}"
                            class="inline-flex items-center gap-2 px-4 py-2 border border-gray-300 rounded text-gray-700 hover:bg-gray-100">
                            Cancelar
                        </a>
                        <button type="submit"
                            class="inline-flex items-center gap-2 px-4 py-2 bg-orange-600 text-white rounded border border-transparent hover:bg-orange-700">
                            <i class="fa-regular fa-floppy-disk"></i>
                            <span>Salvar alterações</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
