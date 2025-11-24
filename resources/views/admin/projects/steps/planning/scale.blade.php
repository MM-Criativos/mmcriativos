@php
    use App\Models\PlanningBriefingRegua;
    use App\Models\PlanningBriefingResponse;
    use App\Models\ProjectPlanning;

    $responses = PlanningBriefingResponse::where('project_id', $project->id)->get();
    $reguas = PlanningBriefingRegua::orderBy('category')->get();
    $hasResponses = $responses->count() > 0;

    // Se tiver respostas, atualiza o status do planejamento para in_progress
    if ($hasResponses) {
        $planning = ProjectPlanning::where('project_id', $project->id)->first();
        if ($planning && $planning->status !== 'in_progress') {
            $planning->update(['status' => 'in_progress']);
        }
    }
@endphp

<div class="mt-6">
    <div class="mb-4">
        <h3 class="text-lg font-semibold text-gray-800">Régua perceptiva</h3>
    </div>

    @if (!$hasResponses)
        <div class="mb-4 flex items-center justify-between">
            <p class="text-sm text-gray-600 flex-1">
                Este formulário é enviado ao cliente. Use o botão ao lado para enviar o link seguro por e-mail.
            </p>

            <form method="POST" action="{{ route('admin.projects.planning.scale.email', $project, false) }}"
                class="flex items-center gap-2">
                @csrf
                <input type="email" name="email" value="{{ old('email', optional($project->client)->email) }}"
                    placeholder="E-mail do cliente"
                    class="dark:!bg-[#262626] border-gray-300 rounded-md text-sm py-2 px-3 w-56" />

                <button type="submit" class="btn btn-mmcriativos inline-flex items-center ml-2 px-3 py-2">
                    <i class="fa-duotone fa-solid fa-paper-plane icon-project mr-2"></i>
                    <span>Enviar por e-mail</span>
                </button>
            </form>
        </div>
    @else
        @php
            $responsesByCategory = $reguas->groupBy('category');

            function getIntensityLabel($value, $leftLabel, $rightLabel)
            {
                if ($value == 0) {
                    return null;
                }

                $baseLabel = $value < 0 ? $leftLabel : $rightLabel;
                $prefix = abs($value) == 2 ? 'Muito ' : '';

                return $prefix . $baseLabel;
            }
        @endphp

        <div class="space-y-4">
            @foreach ($responsesByCategory as $category => $categoryReguas)
                @if ($category)
                    @php
                        $hasActiveResponses = false;
                        foreach ($categoryReguas as $regua) {
                            $response = $responses->firstWhere('briefing_regua_id', $regua->id);
                            if ($response && $response->value != 0) {
                                $hasActiveResponses = true;
                                break;
                            }
                        }
                    @endphp

                    @if ($hasActiveResponses)
                        <div x-data="{ open: false }" class="rounded-lg bg-white hover:text-[#ff8800] overflow-hidden">
                            <button type="button" @click="open = !open"
                                class="w-full px-4 py-3 flex items-center justify-between bg-[#f5f5f5] dark:bg-[#262626]">
                                <h4 class="text-base font-medium text-gray-700">{{ $category }}
                                </h4>
                                <i class="fa-solid" :class="{ 'fa-chevron-down': !open, 'fa-chevron-up': open }"></i>
                            </button>

                            <div x-show="open" x-collapse>
                                <div
                                    class="p-4 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 bg-[#f5f5f5] dark:bg-[#262626]">
                                    @foreach ($categoryReguas as $regua)
                                        @php
                                            $response = $responses->firstWhere('briefing_regua_id', $regua->id);
                                            if (!$response || $response->value == 0) {
                                                continue;
                                            }

                                            $label = getIntensityLabel(
                                                $response->value,
                                                $regua->label_left,
                                                $regua->label_right,
                                            );

                                            if (!$label) {
                                                continue;
                                            }

                                            $emoji = $response->value < 0 ? $regua->emoji_left : $regua->emoji_right;
                                        @endphp

                                        <div class="bg-[#fff] dark:bg-[#000] rounded-lg p-4">
                                            <div class="flex items-start gap-3">
                                                @if ($emoji)
                                                    <span class="text-2xl">{{ $emoji }}</span>
                                                @endif
                                                <div>
                                                    <h5 class="font-medium text-gray-900">{{ $label }}</h5>
                                                    @if ($response->comment)
                                                        <p class="mt-1 text-sm text-gray-600">{{ $response->comment }}
                                                        </p>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endif
                @endif
            @endforeach
        </div>
    @endif
</div>
