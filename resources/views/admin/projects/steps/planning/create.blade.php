@php
    use App\Models\QualitativeTemplate;
    $title = 'Criar Questionário';
    $subTitle = 'Crie o questionário qualitativo';
    $templates = QualitativeTemplate::where('is_active', true)
        ->orderBy('category')
        ->orderBy('sort_order')
        ->get()
        ->groupBy('category');
@endphp


@extends('layouts.app')

@section('content')
    <div class="w-full grid grid-cols-1 gap-6">
        @if (session('status'))
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
        @endif

        <div>
            <div class="bg-white dark:bg-[#000] overflow-hidden shadow-sm sm:rounded-lg">
                <form action="{{ route('admin.projects.planning.qualitative.save', $project) }}" method="POST">
                    @csrf
                    <div class="p-6">
                        <div class="mb-6">
                            <h3 class="text-lg font-medium text-gray-900">Selecione as Perguntas do Questionário</h3>
                            <p class="mt-1 text-sm text-gray-600">
                                Escolha as perguntas que serão enviadas ao cliente.
                            </p>
                        </div>

                        <div class="space-y-8">
                            @foreach ($templates as $category => $items)
                                <div>
                                    <h4
                                        class="text-md font-medium text-gray-800 bg-[#ff8800] max-w-80 rounded-lg px-2 py-3 text-center mb-6">
                                        {{ $category }}</h4>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                        @foreach ($items as $template)
                                            <label class="flex items-start">
                                                <div class="flex h-6 items-center">
                                                    <input type="checkbox" name="template_ids[]" value="{{ $template->id }}"
                                                        class="h-4 w-4 rounded border-gray-300 text-[#ff8800] focus:ring-[#ff8800]">
                                                </div>
                                                <div class="ml-3">
                                                    <span
                                                        class="text-sm font-medium text-gray-900">{{ $template->question }}</span>
                                                    @if ($template->placeholder)
                                                        <p class="text-sm text-gray-500">{{ $template->placeholder }}
                                                        </p>
                                                    @endif
                                                </div>
                                            </label>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="px-6 py-4 flex justify-center">
                        <button type="submit" class="btn btn-mmcriativos inline-flex items-center gap-2 px-4 py-2">
                            <i class="fa-duotone fa-solid fa-arrow-down-to-arc icon-project"></i>
                            <span>Salvar Questionário</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
