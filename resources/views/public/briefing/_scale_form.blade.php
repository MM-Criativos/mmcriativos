@php
    use App\Models\PlanningBriefingRegua;
    use App\Models\PlanningBriefingResponse;

    $reguas = PlanningBriefingRegua::orderBy('id')->get();
    $responses = PlanningBriefingResponse::where('project_id', $project->id)->get()->keyBy('briefing_regua_id');
@endphp

@php
    $action = $action ?? secure_url(route('public.briefing.perception.save', $project, false));
@endphp
<form method="POST" action="{{ $action }}" class="space-y-6">
    @csrf

    @foreach ($reguas as $regua)
        @php
            $min = (int) ($regua->min ?? 0);
            $max = (int) ($regua->max ?? 10);
            $step = max(1, (int) ($regua->step ?? 1));
            $default = $regua->default_value ?? intdiv($min + $max, 2);
            $mid = (int) $default;
            $values = [
                max($min, $mid - 2 * $step),
                max($min, $mid - 1 * $step),
                $mid,
                min($max, $mid + 1 * $step),
                min($max, $mid + 2 * $step),
            ];
            $oldVal = old('responses.' . $regua->id . '.value');
            if ($oldVal !== null && $oldVal !== '') {
                $current = (int) $oldVal;
            } else {
                $current = $responses->get($regua->id)?->value;
                if ($current === null || $current === '') {
                    $current = $mid;
                }
            }
            $intValues = array_map('intval', $values);
            $selectedIndex = array_search((int) $current, $intValues, true);
            if ($selectedIndex === false) {
                $selectedIndex = 2;
            }
        @endphp

        <div class="p-4 border rounded-lg bg-white">
            @if ($regua->category)
                <div class="text-xs uppercase tracking-wide text-gray-500 mb-1">{{ $regua->category }}</div>
            @endif

            <div class="font-medium text-gray-800 mb-3">{{ $regua->question }}</div>

            <!-- 🔸 Desktop / Tablet -->
            <div class="hidden sm:flex items-center justify-between gap-4">
                <!-- Esquerda -->
                <div class="flex items-center gap-1 text-sm text-gray-600 flex-shrink-0 w-40">
                    @if ($regua->emoji_left)
                        <span>{{ $regua->emoji_left }}</span>
                    @endif
                    <span>{{ $regua->label_left }}</span>
                </div>

                <!-- Dots -->
                <div class="flex justify-between flex-1 w-full max-w-[720px] lg:max-w-[840px]">
                    @foreach ($values as $idx => $val)
                        @php($id = 'regua-' . $regua->id . '-pos-' . $idx)
                        <label for="{{ $id }}"
                            class="relative flex items-center justify-center cursor-pointer">
                            <input id="{{ $id }}" type="radio" name="responses[{{ $regua->id }}][value]"
                                value="{{ $val }}" class="peer hidden"
                                {{ $idx === $selectedIndex ? 'checked' : '' }}>
                            <span
                                class="w-5 h-5 rounded-full border border-gray-400 peer-checked:border-orange-600 peer-checked:bg-orange-600 transition-all duration-200"></span>
                        </label>
                    @endforeach
                </div>

                <!-- Direita -->
                <div class="flex items-center gap-1 text-sm text-gray-600 flex-shrink-0 w-40 justify-end">
                    <span>{{ $regua->label_right }}</span>
                    @if ($regua->emoji_right)
                        <span>{{ $regua->emoji_right }}</span>
                    @endif
                </div>
            </div>

            <!-- 🔸 Mobile -->
            <div class="flex flex-col sm:hidden">
                <!-- Linha 1: Labels -->
                <div class="flex items-center justify-between text-sm text-gray-600">
                    <div class="flex items-center gap-1">
                        @if ($regua->emoji_left)
                            <span>{{ $regua->emoji_left }}</span>
                        @endif
                        <span>{{ $regua->label_left }}</span>
                    </div>
                    <div class="flex items-center gap-1">
                        <span>{{ $regua->label_right }}</span>
                        @if ($regua->emoji_right)
                            <span>{{ $regua->emoji_right }}</span>
                        @endif
                    </div>
                </div>

                <!-- Linha 2: Dots -->
                <div class="mt-3 flex justify-between w-full">
                    @foreach ($values as $idx => $val)
                        @php($id = 'regua-mobile-' . $regua->id . '-pos-' . $idx)
                        <label for="{{ $id }}"
                            class="relative flex items-center justify-center cursor-pointer flex-1">
                            <input id="{{ $id }}" type="radio"
                                name="responses[{{ $regua->id }}][value]" value="{{ $val }}"
                                class="peer hidden" {{ $idx === $selectedIndex ? 'checked' : '' }}>
                            <span
                                class="w-5 h-5 rounded-full border border-gray-400 peer-checked:border-orange-600 peer-checked:bg-orange-600 transition-all duration-200"></span>
                        </label>
                    @endforeach
                </div>
            </div>

            <!-- 🔸 Campo de comentário -->
            <div class="mt-4">
                <input type="text" name="responses[{{ $regua->id }}][comment]"
                    value="{{ old('responses.' . $regua->id . '.comment', optional($responses->get($regua->id))->comment) }}"
                    placeholder="Comentário opcional" class="w-full border-gray-300 rounded-md text-sm" />
            </div>
        </div>
    @endforeach

    <div class="pt-2">
        <button type="submit"
            class="inline-flex items-center px-5 py-3 bg-orange-600 text-white rounded border border-transparent hover:bg-white hover:text-orange-600 hover:border-orange-600 hover:border-solid text-sm">
            Salvar escala
        </button>
    </div>
</form>

