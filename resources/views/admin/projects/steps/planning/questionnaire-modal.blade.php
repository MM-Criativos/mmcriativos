@php
    use App\Models\QualitativeTemplate;
    $templates = QualitativeTemplate::where('is_active', true)
        ->orderBy('category')
        ->orderBy('sort_order')
        ->get()
        ->groupBy('category');
@endphp

<div x-data="questionnaireModal()" x-show="isOpen" class="relative z-50" aria-labelledby="modal-title" role="dialog"
    aria-modal="true" style="display: none;">
    <div x-show="show" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200"
        x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
        class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"></div>

    <div class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
            <div x-show="show" x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                class="relative transform overflow-hidden rounded-lg bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-3xl">

                <form id="questionnaire-form" method="POST"
                    action="{{ route('admin.projects.planning.qualitative.save', $project) }}">
                    @csrf
                    <div class="bg-white px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
                        <div class="mb-4">
                            <h3 class="text-lg font-semibold leading-6 text-gray-900">Selecionar Perguntas</h3>
                            <p class="mt-1 text-sm text-gray-500">Selecione as perguntas que serão incluídas no
                                questionário qualitativo.</p>
                        </div>

                        <div class="space-y-6">
                            @foreach ($templates as $category => $items)
                                <div>
                                    <h4 class="text-sm font-medium text-gray-900 mb-2">{{ $category }}</h4>
                                    <div class="space-y-2">
                                        @foreach ($items as $template)
                                            <label class="relative flex items-start">
                                                <div class="flex h-6 items-center">
                                                    <input type="checkbox" name="template_ids[]"
                                                        value="{{ $template->id }}"
                                                        class="h-4 w-4 rounded border-gray-300 text-orange-600 focus:ring-orange-600">
                                                </div>
                                                <div class="ml-3 text-sm leading-6">
                                                    <span class="text-gray-900">{{ $template->question }}</span>
                                                </div>
                                            </label>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="bg-gray-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6">
                        <button type="submit"
                            class="inline-flex w-full justify-center rounded-md bg-orange-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-orange-500 sm:ml-3 sm:w-auto">
                            Salvar Questionário
                        </button>
                        <button type="button" @click="show = false"
                            class="mt-3 inline-flex w-full justify-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 sm:mt-0 sm:w-auto">
                            Cancelar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
