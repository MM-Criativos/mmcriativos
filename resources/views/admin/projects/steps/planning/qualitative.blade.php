@php
    use App\Models\PlanningBriefingQualitative;

    $qualitatives = PlanningBriefingQualitative::normalizeForProject($project)->load('responses');

    $hasQualitatives = $qualitatives->count() > 0;
    $hasResponses = $qualitatives->flatMap->responses->isNotEmpty();
@endphp

<div class="mt-10">
    <div class="mb-4 flex items-center justify-between">
        <h3 class="text-lg font-semibold text-gray-800">Formulário Qualitativo</h3>
        <div class="flex items-center gap-3">
            {{-- 🟠 CASO 1: Ainda não existe questionário --}}
            @if (!$hasQualitatives)
                <a href="{{ route('admin.projects.planning.qualitative.create', $project) }}"
                    class="btn btn-mmcriativos inline-flex items-center gap-2 px-4 py-2">
                    <i class="fa-duotone fa-circle-plus fa-2x icon-project"></i>
                    <span>Criar Questionário</span>
                </a>

                {{-- 🟠 CASO 2: Questionário criado, mas sem respostas --}}
            @elseif ($hasQualitatives && !$hasResponses)
                <form method="POST" action="{{ route('admin.projects.planning.qualitative.email', $project) }}"
                    class="flex items-center gap-2">
                    @csrf
                    <input type="email" name="email" value="{{ old('email', optional($project->client)->email) }}"
                        placeholder="E-mail do cliente"
                        class="dark:!bg-[#262626] border-gray-300 rounded-md text-sm py-2 px-3 w-56" />
                    <button type="submit" class="btn btn-mmcriativos inline-flex items-center gap-2 px-3 py-2">
                        <i class="fa-duotone fa-solid fa-paper-plane icon-project"></i>
                        <span>Enviar por e-mail</span>
                    </button>
                </form>

                <a href="{{ route('admin.projects.planning.qualitative.edit', $project) }}" title="Editar questionário"
                    class="btn btn-mmcriativos inline-flex items-center justify-center h-11 w-11">
                    <i class="fa-duotone fa-solid fa-pen-to-square icon-project"></i>
                    <span class="sr-only">Editar questionário qualitativo</span>
                </a>

                <a href="{{ route('admin.projects.planning.qualitative.preview', $project) }}" target="_blank"
                    rel="noopener" class="btn btn-mmcriativos inline-flex items-center justify-center h-11 w-11"
                    title="Visualizar
                    questionário">
                    <i class="fa-duotone fa-solid fa-eye icon-project"></i>
                    <span class="sr-only">Visualizar questionário qualitativo</span>
                </a>
            @endif
        </div>
    </div>

    {{-- 🔸 CONTEÚDOS ABAIXO DOS BOTÕES --}}
    @if (!$hasQualitatives)
        {{-- Caso 1: Nenhum questionário criado --}}
        <p class="text-sm text-center text-gray-600 bg-[#f5f5f5] dark:bg-[#262626] rounded-lg shadow-sm p-6 mt-4">
            O questionário qualitativo precisa ser criado usando o banco de questões.
            Clique no botão <strong class="!text-[#ff8800]">"Criar Questionário"</strong> para começar.
        </p>
    @elseif ($hasQualitatives && !$hasResponses)
        {{-- Caso 2: Questionário criado, mas sem respostas --}}
        <div class="bg-[#f5f5f5] dark:bg-[#262626] rounded-lg shadow-sm p-6 mt-4">
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
    @elseif ($hasQualitatives && $hasResponses)
        {{-- Caso 3: Questionário criado e respondido --}}
        <div class="space-y-4">
            @foreach ($qualitatives->groupBy('template.category') as $category => $items)
                @php
                    $hasResponsesInCategory = $items->some(fn($q) => $q->responses->isNotEmpty());
                @endphp

                @if ($hasResponsesInCategory)
                    <div x-data="{ open: false }" class="rounded-lg bg-white hover:text-[#ff8800] overflow-hidden">
                        <button type="button" @click="open = !open"
                            class="w-full px-4 py-3 flex items-center justify-between bg-[#f5f5f5] dark:bg-[#262626]">
                            <h4 class="text-base font-medium text-gray-700">{{ $category }}</h4>
                            <i class="fa-solid" :class="{ 'fa-chevron-down': !open, 'fa-chevron-up': open }"></i>
                        </button>

                        <div x-show="open" x-collapse x-transition>
                            <div class="p-4 space-y-6 bg-[#f5f5f5] dark:bg-[#262626]">
                                @foreach ($items as $qualitative)
                                    @php
                                        // Busca a primeira resposta vinculada ao questionário
                                        $response = $qualitative->responses->first();
                                        if (!$response) {
                                            continue;
                                        }

                                        $answer = $response->answer_value;
                                    @endphp

                                    <div class="border-b pb-4 last:border-b-0 last:pb-0">
                                        {{-- Pergunta --}}
                                        <div class="text-sm text-gray-700 mb-1">
                                            {{ $qualitative->template->question }}
                                        </div>

                                        {{-- Resposta --}}
                                        @if ($qualitative->template->type === 'file' && $response->file_path)
                                            <div class="mt-2">
                                                <a href="{{ asset($response->file_path) }}" target="_blank"
                                                    class="btn btn-mmcriativos inline-flex items-center text-sm">
                                                    <i class="fa-regular fa-file mr-2"></i>
                                                    {{ $answer ?: 'Baixar arquivo enviado' }}
                                                </a>
                                            </div>
                                        @elseif ($qualitative->template->type === 'multi_choice' && is_array($answer))
                                            <div class="flex flex-wrap gap-2 mt-1">
                                                @foreach ($answer as $value)
                                                    <span
                                                        class="inline-flex items-center px-2 py-1 rounded-md text-sm font-medium bg-orange-100 text-orange-800">
                                                        {{ $value }}
                                                    </span>
                                                @endforeach
                                            </div>
                                        @else
                                            <div class="text-gray-900">
                                                {{ is_array($answer) ? implode(', ', $answer) : $answer }}
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
