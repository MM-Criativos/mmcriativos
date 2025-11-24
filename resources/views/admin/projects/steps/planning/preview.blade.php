@extends('layouts.app')

@section('content')
    <div class="w-full grid grid-cols-1 gap-6">
        <div>
            <div class="bg-white dark:bg-[#262626] overflow-hidden shadow-sm sm:rounded-lg">

                @php
                    $groupedQuestions = $questions->groupBy(
                        fn($question) => $question['category'] ?? 'Outras perguntas',
                    );
                @endphp

                @if ($groupedQuestions->isEmpty())
                    <div class="bg-white dark:bg-[#000] shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <p class="text-sm text-gray-600 dark:text-gray-300">
                                Nenhuma pergunta foi selecionada para este questionário qualitativo.
                            </p>
                        </div>
                    </div>
                @else
                    <div class="space-y-6">
                        @foreach ($groupedQuestions as $category => $items)
                            <div class="bg-white dark:bg-[#000] shadow-sm sm:rounded-lg">
                                <div class="p-6 space-y-6">
                                    @if ($category)
                                        <h4
                                            class="text-md font-medium text-gray-800 bg-[#ff8800] max-w-80 rounded-lg px-2 py-3 text-center mb-6">
                                            {{ $category }}</h4>
                                    @endif

                                    <div class="space-y-6">
                                        @foreach ($items as $question)
                                            <div class="space-y-2">
                                                <div class="text-sm font-medium text-gray-800 dark:text-gray-100">
                                                    {{ $question['question'] }}
                                                    @if ($question['is_required'])
                                                        <span class="text-red-500">*</span>
                                                    @endif
                                                </div>

                                                @switch($question['type'])
                                                    @case('textarea')
                                                        <textarea rows="3" disabled
                                                            class="block w-full rounded-md !bg-[#f5f5f5] dark:!bg-[#262626] border-gray-300 dark:border-dark-600 shadow-sm focus:border-orange-500 focus:ring-orange-500 sm:text-sm dark:bg-dark-700 text-gray-500 dark:text-gray-300"
                                                            placeholder="{{ $question['placeholder'] }}"></textarea>
                                                    @break

                                                    @case('choice')
                                                        <div class="space-y-2">
                                                            @forelse ($question['options'] as $option)
                                                                <label
                                                                    class="flex items-center text-sm text-gray-700 dark:text-gray-300">
                                                                    <input type="radio" disabled
                                                                        class="h-4 w-4 border-gray-300 text-[#ff8800] focus:ring-orange-500">
                                                                    <span class="ml-3">{{ $option }}</span>
                                                                </label>
                                                            @empty
                                                                <p class="text-sm text-gray-500 dark:text-gray-400 italic">Nenhuma
                                                                    opção configurada.</p>
                                                            @endforelse
                                                        </div>
                                                    @break

                                                    @case('multi_choice')
                                                        <div class="space-y-2">
                                                            @forelse ($question['options'] as $option)
                                                                <label
                                                                    class="flex items-center text-sm text-gray-700 dark:text-gray-300">
                                                                    <input type="checkbox" disabled
                                                                        class="h-4 w-4 rounded border-gray-300 text-[#ff8800] focus:ring-orange-500">
                                                                    <span class="ml-3">{{ $option }}</span>
                                                                </label>
                                                            @empty
                                                                <p class="text-sm text-gray-500 dark:text-gray-400 italic">Nenhuma
                                                                    opção configurada.</p>
                                                            @endforelse
                                                        </div>
                                                    @break

                                                    @case('file')
                                                        <input type="file" disabled
                                                            class="block w-full text-sm text-gray-500 dark:text-gray-300 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-medium file:bg-[orange-50] file:text-orange-700 hover:file:bg-orange-100" />
                                                    @break

                                                    @default
                                                        <input type="text" disabled
                                                            class="block w-full rounded-md border-gray-300 dark:border-dark-600 shadow-sm focus:border-orange-500 focus:ring-orange-500 sm:text-sm bg-gray-50 dark:bg-dark-700 text-gray-500 dark:text-gray-300"
                                                            placeholder="{{ $question['placeholder'] }}">
                                                @endswitch

                                                @if (!empty($question['placeholder']))
                                                    <p class="text-sm text-gray-500 dark:text-gray-400">
                                                        {{ $question['placeholder'] }}</p>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
                <div class="px-6 py-4 flex justify-center gap-3">
                    <a href="{{ route('admin.projects.steps.show', ['project' => $project, 'tab' => 'planning']) }}"
                        class="btn-mmcriativos inline-flex items-center gap-2 px-5 py-4 rounded-xl">
                        Voltar
                    </a>
                </div>
            </div>


        </div>
    </div>
@endsection
