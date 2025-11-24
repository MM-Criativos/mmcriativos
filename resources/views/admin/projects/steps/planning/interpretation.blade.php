@php
    use App\Models\PlanningInterpretacao;

    $interpretation =
        $project->planning?->interpretacao ??
        new PlanningInterpretacao([
            'project_id' => $project->id,
            'client_id' => $project->client_id,
            'status' => 'draft',
        ]);

    $timezone = config('app.timezone') ?: date_default_timezone_get();
    $guidelinesText = '';
    if ($interpretation && $interpretation->diretrizes_visuais) {
        $rawGuidelines = $interpretation->diretrizes_visuais;
        $decoded = json_decode($rawGuidelines, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            $guidelinesText = implode("\n", array_map(fn($value) => trim((string) $value), $decoded));
        } else {
            $guidelinesText = $rawGuidelines;
        }
    }

    $visualGuidelinesText = old('diretrizes_visuais', $guidelinesText);
    $approvedAtDisplay = $interpretation->approved_at
        ? $interpretation->approved_at->copy()->timezone($timezone)
        : null;
    $approvedAtValue = old('approved_at', optional($approvedAtDisplay)->format('Y-m-d\TH:i'));
    $selectedStatus = old('status', $interpretation->status ?? 'draft');
@endphp

<div class="mt-10">
    <div class="mb-4 flex items-center justify-between">
        <h3 class="text-lg font-semibold text-gray-800">Interpretando os dados</h3>
        @if ($interpretation && $interpretation->exists)
            <div class="text-xs text-gray-500">
                Atualizado em {{ optional($interpretation->updated_at)->format('d/m/Y H:i') }}
            </div>
        @endif
    </div>

    <div class="bg-[#f5f5f5] dark:bg-[#262626] rounded-lg shadow-sm border border-gray-100 p-6">
        <div class="mb-6 text-sm text-gray-600">
            Estas informações são de uso interno da equipe. Registre aqui os principais aprendizados do briefing,
            combine diretrizes e organize o escopo antes de iniciar as próximas etapas.
        </div>

        <form method="POST" action="{{ route('admin.projects.planning.interpretation.save', $project) }}"
            class="space-y-6">
            @csrf

            <div class="grid grid-cols-1 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1" for="analise_publico">
                        Análise do público
                    </label>
                    <textarea id="analise_publico" name="analise_publico" rows="4"
                        class="w-full bg-white dark:bg-black border-gray-300 rounded-md text-sm focus:ring-orange-500 focus:border-orange-500"
                        placeholder="Público, tom de voz, jornada e percepções relevantes">{{ old('analise_publico', $interpretation->analise_publico) }}</textarea>
                    @error('analise_publico')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1" for="analise_concorrencia">
                        Análise da concorrência
                    </label>
                    <textarea id="analise_concorrencia" name="analise_concorrencia" rows="4"
                        class="w-full bg-white dark:bg-black border-gray-300 rounded-md text-sm focus:ring-orange-500 focus:border-orange-500"
                        placeholder="Benchmarks, oportunidades, diferenciais e alertas">{{ old('analise_concorrencia', $interpretation->analise_concorrencia) }}</textarea>
                    @error('analise_concorrencia')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="border-t border-gray-200 pt-6">
                <label class="block text-sm font-medium text-gray-700 mb-1" for="diretrizes_visuais">
                    Diretrizes visuais
                </label>
                <p class="text-sm text-gray-500 mb-3">
                    Liste os principais pontos que devem orientar o visual. Utilize uma linha para cada diretriz.
                </p>
                <textarea id="diretrizes_visuais" name="diretrizes_visuais" rows="5"
                    class="w-full bg-white dark:bg-black border-gray-300 rounded-md text-sm focus:ring-orange-500 focus:border-orange-500"
                    placeholder="Ex.: Tom de voz, referências visuais, elementos obrigatórios">{{ $visualGuidelinesText }}</textarea>
                @error('diretrizes_visuais')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="border-t border-gray-200 pt-6 grid grid-cols-1 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1" for="definicao_escopo">
                        Definição do escopo
                    </label>
                    <textarea id="definicao_escopo" name="definicao_escopo" rows="4"
                        class="w-full bg-white dark:bg-black border-gray-300 rounded-md text-sm focus:ring-orange-500 focus:border-orange-500"
                        placeholder="Detalhe entregáveis, responsabilidades e limites do escopo">{{ old('definicao_escopo', $interpretation->definicao_escopo) }}</textarea>
                    @error('definicao_escopo')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1" for="observacoes_tecnicas">
                        Observações técnicas
                    </label>
                    <textarea id="observacoes_tecnicas" name="observacoes_tecnicas" rows="4"
                        class="w-full bg-white dark:bg-black border-gray-300 rounded-md text-sm focus:ring-orange-500 focus:border-orange-500"
                        placeholder="Integrações, restrições, stacks e qualquer alerta operacional">{{ old('observacoes_tecnicas', $interpretation->observacoes_tecnicas) }}</textarea>
                    @error('observacoes_tecnicas')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="border-t border-gray-200 pt-6 grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1" for="status">
                        Status
                    </label>
                    <select id="status" name="status"
                        class="w-full bg-white dark:bg-black border-gray-300 rounded-md text-sm focus:ring-orange-500 focus:border-orange-500 px-2 py-3">
                        <option value="draft" {{ $selectedStatus === 'draft' ? 'selected' : '' }}>Rascunho</option>
                        <option value="review" {{ $selectedStatus === 'review' ? 'selected' : '' }}>Revisando</option>
                        <option value="approved" {{ $selectedStatus === 'approved' ? 'selected' : '' }}>Aprovado
                        </option>
                    </select>
                    @error('status')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1" for="approved_at">
                        Aprovado em
                    </label>
                    <input id="approved_at" type="datetime-local" name="approved_at" value="{{ $approvedAtValue }}"
                        @if ($selectedStatus !== 'approved') disabled @endif
                        class="w-full !bg-white dark:!bg-black border-gray-300 rounded-md text-sm focus:ring-orange-500 focus:border-orange-500 disabled:bg-gray-100 disabled:text-gray-500" />
                    <p class="mt-1 text-xs text-gray-500">
                        Este campo é preenchido automaticamente ao marcar o status como aprovado. Ajuste manualmente se
                        necessário.
                    </p>
                    @error('approved_at')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="pt-4 border-t border-gray-200 flex justify-center">
                <button type="submit" class="btn btn-mmcriativos inline-flex items-center gap-2 px-5 py-3 ">
                    <i class="fa-duotone fa-solid fa-arrow-down-to-arc icon-project"></i>
                    <span>Salvar interpretação</span>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        var statusField = document.getElementById('status');
        var approvedField = document.getElementById('approved_at');

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
            if (statusField.value === 'approved') {
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
