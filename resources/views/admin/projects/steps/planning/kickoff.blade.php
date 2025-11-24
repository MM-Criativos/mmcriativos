@php
    use App\Models\PlanningKickoff;

    $kickoff =
        $project->planning?->kickoff ??
        new PlanningKickoff([
            'project_id' => $project->id,
            'client_id' => $project->client_id,
            'status' => 'agendado',
        ]);

    $timezone = config('app.timezone') ?: date_default_timezone_get();

    $meetingDate = $kickoff->data_reuniao ? $kickoff->data_reuniao->copy()->timezone($timezone)->format('Y-m-d') : null;
    $approvedAtDisplay = $kickoff->approved_at ? $kickoff->approved_at->copy()->timezone($timezone) : null;

    $titulo = old('titulo', $kickoff->titulo);
    $objetivo = old('objetivo', $kickoff->objetivo);
    $resumoAlinhamento = old('resumo_alinhamento', $kickoff->resumo_alinhamento);
    $tarefasIniciais = old('tarefas_iniciais', $kickoff->tarefas_iniciais);
    $responsaveis = old('responsaveis', $kickoff->responsaveis);
    $materiaisApresentados = old('materiais_apresentados', $kickoff->materiais_apresentados);
    $selectedStatus = old('status', $kickoff->status ?? 'agendado');
    $dataReuniaoValue = old('data_reuniao', $meetingDate);
    $approvedAtValue = old('approved_at', optional($approvedAtDisplay)->format('Y-m-d\TH:i'));

    $statusOptions = [
        'agendado' => 'Agendado',
        'realizado' => 'Realizado',
        'aprovado' => 'Aprovado',
    ];
@endphp

<div class="mt-10">
    <div class="mb-4 flex items-center justify-between">
        <h3 class="text-lg font-semibold text-gray-800">Kickoff</h3>
        @if ($kickoff && $kickoff->exists)
            <div class="text-xs text-gray-500">
                Atualizado em {{ optional($kickoff->updated_at)->timezone($timezone)->format('d/m/Y H:i') }}
            </div>
        @endif
    </div>

    <div class="bg-[#f5f5f5] dark:bg-[#262626] rounded-lg shadow-sm border border-gray-100 p-6">
        <div class="mb-6 text-sm text-gray-600">
            Registre os principais combinados da reunião de kickoff para garantir alinhamento entre equipe e cliente.
        </div>

        <form method="POST" action="{{ route('admin.projects.planning.kickoff.save', $project) }}" class="space-y-6">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1" for="titulo">
                        Título
                    </label>
                    <input id="titulo" type="text" name="titulo" value="{{ $titulo }}"
                        class="w-full !bg-white dark:!bg-black border-gray-300 rounded-md text-sm focus:ring-orange-500 focus:border-orange-500"
                        placeholder="Ex.: Kickoff Projeto ACME" />
                    @error('titulo')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 md:col-span-1">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1" for="status">
                            Status
                        </label>
                        <select id="kickoff-status" name="status"
                            class="w-full !bg-white dark:!bg-black border-gray-300 rounded-md text-sm focus:ring-orange-500 focus:border-orange-500">
                            @foreach ($statusOptions as $value => $label)
                                <option value="{{ $value }}" {{ $selectedStatus === $value ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                        @error('status')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1" for="data_reuniao">
                            Data da reunião
                        </label>
                        <input id="data_reuniao" type="date" name="data_reuniao" value="{{ $dataReuniaoValue }}"
                            class="w-full !bg-white dark:!bg-black border-gray-300 rounded-md text-sm focus:ring-orange-500 focus:border-orange-500" />
                        @error('data_reuniao')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1" for="objetivo">
                        Objetivo
                    </label>
                    <textarea id="objetivo" name="objetivo" rows="4"
                        class="w-full !bg-white dark:!bg-black border-gray-300 rounded-md text-sm focus:ring-orange-500 focus:border-orange-500"
                        placeholder="Quais são as metas principais desta reunião?">{{ $objetivo }}</textarea>
                    @error('objetivo')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1" for="resumo_alinhamento">
                        Resumo de alinhamento
                    </label>
                    <textarea id="resumo_alinhamento" name="resumo_alinhamento" rows="4"
                        class="w-full !bg-white dark:!bg-black border-gray-300 rounded-md text-sm focus:ring-orange-500 focus:border-orange-500"
                        placeholder="Resumo dos acordos, definições e próximos passos combinados">{{ $resumoAlinhamento }}</textarea>
                    @error('resumo_alinhamento')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1" for="tarefas_iniciais">
                        Tarefas iniciais
                    </label>
                    <textarea id="tarefas_iniciais" name="tarefas_iniciais" rows="4"
                        class="w-full !bg-white dark:!bg-black border-gray-300 rounded-md text-sm focus:ring-orange-500 focus:border-orange-500"
                        placeholder="Liste as primeiras entregas e responsáveis previstos">{{ $tarefasIniciais }}</textarea>
                    @error('tarefas_iniciais')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1" for="responsaveis">
                        Responsáveis
                    </label>
                    <textarea id="responsaveis" name="responsaveis" rows="4"
                        class="w-full !bg-white dark:!bg-black border-gray-300 rounded-md text-sm focus:ring-orange-500 focus:border-orange-500"
                        placeholder="Quem lidera cada frente após o kickoff?">{{ $responsaveis }}</textarea>
                    @error('responsaveis')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="border-t border-gray-200 pt-6">
                <label class="block text-sm font-medium text-gray-700 mb-1" for="materiais_apresentados">
                    Materiais apresentados
                </label>
                <textarea id="materiais_apresentados" name="materiais_apresentados" rows="4"
                    class="w-full !bg-white dark:!bg-black border-gray-300 rounded-md text-sm focus:ring-orange-500 focus:border-orange-500"
                    placeholder="Slides, protótipos, documentos e referências discutidas">{{ $materiaisApresentados }}</textarea>
                @error('materiais_apresentados')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="border-t border-gray-200 pt-6">
                <label class="block text-sm font-medium text-gray-700 mb-1" for="kickoff-approved-at">
                    Aprovado em
                </label>
                <input id="kickoff-approved-at" type="datetime-local" name="approved_at" value="{{ $approvedAtValue }}"
                    @if ($selectedStatus !== 'aprovado') disabled @endif
                    class="w-full !bg-white dark:!bg-black border-gray-300 rounded-md text-sm focus:ring-orange-500 focus:border-orange-500 disabled:bg-gray-100 disabled:text-gray-500" />
                <p class="mt-1 text-xs text-gray-500">
                    Este campo é preenchido automaticamente ao marcar o status como aprovado. Ajuste manualmente se
                    necessário.
                </p>
                @error('approved_at')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="pt-4 border-t border-gray-200 flex justify-center">
                <button type="submit" class="btn btn-mmcriativos inline-flex items-center gap-2 px-5 py-3 ">
                    <i class="fa-duotone fa-solid fa-arrow-down-to-arc icon-project"></i>
                    <span>Salvar kickoff</span>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        var statusField = document.getElementById('kickoff-status');
        var approvedField = document.getElementById('kickoff-approved-at');

        if (!statusField || !approvedField) {
            return;
        }

        var formatLocalDateTime = function(date) {
            var pad = function(value) {
                return String(value).padStart(2, '0');
            };
            return [
                date.getFullYear(),
                pad(date.getMonth() + 1),
                pad(date.getDate())
            ].join('-') + 'T' + pad(date.getHours()) + ':' + pad(date.getMinutes());
        };

        var syncApprovedField = function() {
            if (statusField.value === 'aprovado') {
                approvedField.disabled = false;
                if (!approvedField.value) {
                    var now = new Date();
                    now.setSeconds(0, 0);
                    approvedField.value = formatLocalDateTime(now);
                }
            } else {
                approvedField.value = '';
                approvedField.disabled = true;
            }
        };

        syncApprovedField();
        statusField.addEventListener('change', syncApprovedField);
    });
</script>
