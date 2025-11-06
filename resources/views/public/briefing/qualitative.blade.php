<x-guest-layout>
    <div class="min-h-screen bg-gray-50 py-12">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-8">
                <h2 class="text-3xl font-bold tracking-tight text-gray-900">
                    Briefing Qualitativo
                </h2>
                <p class="mt-4 text-lg text-gray-600">
                    Agora vamos conhecer melhor suas necessidades com algumas perguntas específicas.
                </p>
            </div>

            <form method="POST" action="{{ route('public.briefing.qualitative.save', $project) }}" class="space-y-8"
                enctype="multipart/form-data">
                @csrf

                @foreach ($qualitatives->groupBy('template.category') as $category => $questions)
                    <div class="bg-white shadow rounded-lg p-6">
                        @if ($category)
                            <h3 class="text-lg font-medium text-gray-900 mb-4">{{ $category }}</h3>
                        @endif

                        <div class="space-y-6">
                            @foreach ($questions as $qualitative)
                                @php
                                    $template = $qualitative->template;
                                    $inputName = "responses[{$qualitative->id}]";
                                @endphp

                                <div class="space-y-2">
                                    <label for="q{{ $qualitative->id }}"
                                        class="block text-sm font-medium text-gray-900">
                                        {{ $template->question }}
                                        @if ($template->is_required)
                                            <span class="text-red-500">*</span>
                                        @endif
                                    </label>

                                    @switch($template->type)
                                        @case('textarea')
                                            <textarea id="q{{ $qualitative->id }}" name="{{ $inputName }}" rows="3"
                                                class="block w-full rounded-md border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500 sm:text-sm"
                                                placeholder="{{ $template->placeholder }}" @if ($template->is_required) required @endif>{{ old($inputName) }}</textarea>
                                        @break

                                        @case('choice')
                                            <div class="space-y-2">
                                                @foreach ($template->options as $option)
                                                    <div class="flex items-center">
                                                        <input type="radio" id="q{{ $qualitative->id }}_{{ $loop->index }}"
                                                            name="{{ $inputName }}" value="{{ $option }}"
                                                            @if (old($inputName) === $option) checked @endif
                                                            @if ($template->is_required) required @endif
                                                            class="h-4 w-4 border-gray-300 text-orange-600 focus:ring-orange-500">
                                                        <label for="q{{ $qualitative->id }}_{{ $loop->index }}"
                                                            class="ml-3 block text-sm text-gray-700">
                                                            {{ $option }}
                                                        </label>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @break

                                        @case('multi_choice')
                                            <div class="space-y-2">
                                                @foreach ($template->options as $option)
                                                    <div class="flex items-center">
                                                        <input type="checkbox"
                                                            id="q{{ $qualitative->id }}_{{ $loop->index }}"
                                                            name="{{ $inputName }}[]" value="{{ $option }}"
                                                            @if (is_array(old($inputName)) && in_array($option, old($inputName))) checked @endif
                                                            class="h-4 w-4 rounded border-gray-300 text-orange-600 focus:ring-orange-500">
                                                        <label for="q{{ $qualitative->id }}_{{ $loop->index }}"
                                                            class="ml-3 block text-sm text-gray-700">
                                                            {{ $option }}
                                                        </label>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @break

                                        @case('file')
                                            <input type="file" id="q{{ $qualitative->id }}" name="{{ $inputName }}"
                                                @if ($template->is_required) required @endif
                                                class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-medium file:bg-orange-50 file:text-orange-700 hover:file:bg-orange-100">
                                        @break

                                        @default
                                            <input type="text" id="q{{ $qualitative->id }}" name="{{ $inputName }}"
                                                value="{{ old($inputName) }}"
                                                @if ($template->is_required) required @endif
                                                class="block w-full rounded-md border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500 sm:text-sm"
                                                placeholder="{{ $template->placeholder }}">
                                    @endswitch

                                    @if ($template->placeholder)
                                        <p class="mt-1 text-sm text-gray-500">{{ $template->placeholder }}</p>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach

                <div class="flex justify-end">
                    <button type="submit"
                        class="inline-flex justify-center rounded-md border border-transparent bg-orange-600 py-2 px-4 text-sm font-medium text-white shadow-sm hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2">
                        Enviar Respostas
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-guest-layout>
