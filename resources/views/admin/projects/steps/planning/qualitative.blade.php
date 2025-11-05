@php
    use App\Models\PlanningBriefingQualitative;
    use App\Models\PlanningBriefingQualitativeResponse;

    $qualitatives = PlanningBriefingQualitative::with(['template', 'responses'])
        ->where('project_id', $project->id)
        ->get();

    $hasQualitatives = $qualitatives->count() > 0;
    $hasResponses = $qualitatives->flatMap->responses->isNotEmpty();
@endphp

<div class="mt-6">
    <div class="mb-4 flex items-center justify-between">
        <h3 class="text-lg font-semibold text-gray-800">Formulário Qualitativo</h3>
        <div class="flex items-center gap-4">
            @if ($hasQualitatives && !$hasResponses)
                {{-- Se tem perguntas mas não tem respostas, mostrar botão de email --}}
                <form method="POST" action="{{ route('admin.projects.planning.qualitative.email', $project) }}"
                    class="flex items-center gap-2">
                    @csrf
                    <input type="email" name="email" value="{{ old('email', optional($project->client)->email) }}"
                        placeholder="e-mail do cliente" class="border-gray-300 rounded-md text-sm py-1 px-2 w-56" />
                    <button type="submit"
                        class="inline-flex items-center gap-2 px-3 py-2 bg-white text-gray-700 rounded border border-gray-300 hover:bg-gray-50">
                        <i class="fa-regular fa-envelope"></i>
                        <span>Enviar por e-mail</span>
                    </button>
                </form>
                <button onclick="editQuestionnaire()"
                    class="inline-flex items-center gap-2 px-3 py-2 bg-white text-gray-700 rounded border border-gray-300 hover:bg-gray-50">
                    <i class="fa-regular fa-pen-to-square"></i>
                    <span>Editar Questionário</span>
                </button>
            @elseif(!$hasQualitatives)
                {{-- Se não tem perguntas, mostrar botão de criar --}}
                <button onclick="createQuestionnaire()"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-orange-600 text-white rounded border border-transparent hover:bg-orange-700">
                    <i class="fa-regular fa-plus"></i>
                    <span>Criar Questionário</span>
                </button>
            @endif
        </div>
    </div>

    @if (!$hasQualitatives)
        <p class="text-sm text-gray-600">
            Selecione as perguntas para criar o questionário qualitativo que será enviado ao cliente.
        </p>
    @elseif ($hasResponses)
        {{-- Exibição das respostas em accordions --}}
        <div class="space-y-4">
            @foreach ($qualitatives->groupBy('template.category') as $category => $items)
                @php
                    $hasResponsesInCategory = $items->some(fn($q) => $q->responses->isNotEmpty());
                @endphp

                @if ($hasResponsesInCategory)
                    <div x-data="{ open: true }" class="border rounded-lg bg-white overflow-hidden">
                        <button @click="open = !open"
                            class="w-full px-4 py-3 flex items-center justify-between bg-gray-50 hover:bg-gray-100">
                            <h4 class="text-base font-medium text-gray-700">{{ $category }}</h4>
                            <i class="fa-solid" :class="{ 'fa-chevron-down': !open, 'fa-chevron-up': open }"></i>
                        </button>

                        <div x-show="open" x-collapse>
                            <div class="p-4 space-y-6">
                                @foreach ($items as $qualitative)
                                    @php
                                        $response = $qualitative->responses->first();
                                        if (!$response) {
                                            continue;
                                        }
                                    @endphp

                                    <div class="border-b pb-4 last:border-b-0 last:pb-0">
                                        <div class="text-sm text-gray-500 mb-1">{{ $qualitative->template->question }}
                                        </div>

                                        @if ($qualitative->template->type === 'multi_choice')
                                            <div class="flex flex-wrap gap-2 mt-1">
                                                @foreach ($response->response as $value)
                                                    <span
                                                        class="inline-flex items-center px-2 py-1 rounded-md text-sm font-medium bg-orange-100 text-orange-800">
                                                        {{ $value }}
                                                    </span>
                                                @endforeach
                                            </div>
                                        @else
                                            <div class="text-gray-900">
                                                {{ is_array($response->response) ? $response->response[0] : $response->response }}
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif
            @endforeach
        </div>
    @else
        {{-- Questionário criado mas sem respostas ainda --}}
        <div class="bg-white rounded-lg shadow-sm p-6">
            <div class="text-center">
                <div class="text-gray-500 mb-2">
                    <i class="fa-regular fa-clock text-2xl"></i>
                </div>
                <h4 class="text-lg font-medium text-gray-900 mb-2">Aguardando respostas do cliente</h4>
                <p class="text-sm text-gray-600">
                    O questionário foi criado mas ainda não foi respondido. Use o botão "Enviar por e-mail" para
                    solicitar as respostas ao cliente.
                </p>
            </div>
        </div>
    @endif
</div>

<script>
    function createQuestionnaire() {
        window.dispatchEvent(new CustomEvent('open-questionnaire-modal'));
    }

    function editQuestionnaire() {
        window.dispatchEvent(new CustomEvent('open-questionnaire-modal'));
    }
</script>

@include('admin.projects.steps.planning.questionnaire-modal')

{{-- Modal para seleção de perguntas --}}
<div x-data="questionnaireModal()" x-show="isOpen" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;"
    x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-200"
    x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">

    <div class="flex items-center justify-center min-h-screen px-4">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" x-show="isOpen" @click="close"></div>

        <div class="relative bg-white rounded-lg shadow-xl max-w-4xl w-full" x-show="isOpen"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 transform scale-95"
            x-transition:enter-end="opacity-100 transform scale-100"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 transform scale-100"
            x-transition:leave-end="opacity-0 transform scale-95">

            <div class="px-6 py-4 border-b">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-900">
                        Selecione as Perguntas do Questionário
                    </h3>
                    <button @click="close" class="text-gray-400 hover:text-gray-500">
                        <i class="fa-solid fa-times"></i>
                    </button>
                </div>
            </div>

            <div class="px-6 py-4">
                {{-- Tabs de categorias aqui --}}
                <div class="border-b">
                    <nav class="-mb-px flex space-x-8" aria-label="Tabs">
                        <template x-for="category in categories" :key="category">
                            <button @click="activeCategory = category"
                                :class="['whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm',
                                    activeCategory === category ?
                                    'border-orange-500 text-orange-600' :
                                    'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
                                ]"
                                x-text="category">
                            </button>
                        </template>
                    </nav>
                </div>

                {{-- Lista de perguntas da categoria ativa --}}
                <div class="mt-4 space-y-4">
                    <template x-for="question in filteredQuestions" :key="question.id">
                        <div class="flex items-start space-x-3 py-2">
                            <input type="checkbox" :id="'question-' + question.id" :value="question.id"
                                x-model="selectedQuestions"
                                class="mt-1 h-4 w-4 rounded border-gray-300 text-orange-600 focus:ring-orange-600">
                            <div>
                                <label :for="'question-' + question.id" class="text-sm font-medium text-gray-900"
                                    x-text="question.question">
                                </label>
                                <p class="text-sm text-gray-500" x-text="question.placeholder || ''"></p>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            <div class="px-6 py-4 bg-gray-50 rounded-b-lg flex justify-end space-x-3">
                <button @click="close"
                    class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50">
                    Cancelar
                </button>
                <button @click="save"
                    class="px-4 py-2 text-sm font-medium text-white bg-orange-600 border border-transparent rounded-md hover:bg-orange-700">
                    Salvar Questionário
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
    <script>
        function questionnaireModal() {
            return {
                isOpen: false,
                categories: [],
                questions: [],
                selectedQuestions: [],
                activeCategory: null,

                init() {
                    // Será chamado quando o componente for inicializado
                },

                get filteredQuestions() {
                    return this.questions.filter(q => q.category === this.activeCategory);
                },

                async open() {
                    this.isOpen = true;
                    // Carregar categorias e questões do backend
                    try {
                        const response = await fetch(
                            `/admin/projects/{{ $project->id }}/planning/qualitative/templates`);
                        const data = await response.json();
                        this.questions = data.templates;
                        this.categories = [...new Set(data.templates.map(t => t.category))];
                        this.activeCategory = this.categories[0];
                        this.selectedQuestions = @json($qualitatives->pluck('template_id'));
                    } catch (error) {
                        console.error('Erro ao carregar templates:', error);
                    }
                },

                close() {
                    this.isOpen = false;
                },

                async save() {
                    try {
                        const response = await fetch(`/admin/projects/{{ $project->id }}/planning/qualitative/save`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            },
                            body: JSON.stringify({
                                template_ids: this.selectedQuestions
                            })
                        });

                        if (response.ok) {
                            window.location.reload();
                        } else {
                            throw new Error('Erro ao salvar questionário');
                        }
                    } catch (error) {
                        console.error('Erro ao salvar:', error);
                    }
                }
            }
        }

        function createQuestionnaire() {
            const modal = document.querySelector('[x-data="questionnaireModal()"]').__x.$data;
            modal.open();
        }

        function editQuestionnaire() {
            const modal = document.querySelector('[x-data="questionnaireModal()"]').__x.$data;
            modal.open();
        }
    </script>
@endpush
