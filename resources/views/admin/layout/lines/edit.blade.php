@php
    $title = 'Linhas';
    $subTitle = 'Ajustar o texto das linhas deslizantes';
@endphp

@extends('layouts.app')

@section('content')
    <div>
        <div class="grid grid-cols-1">
            @if (session('status'))
                <div class="mb-4 p-3 bg-green-100 text-green-800 rounded">{{ session('status') }}</div>
            @endif
            <div class="bg-white dark:bg-black overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <form method="POST" action="{{ route('admin.layout.lines.update') }}" class="space-y-4">
                        @csrf
                        @method('PUT')

                        <p class="text-sm text-gray-600">Um campo por frase. Você pode adicionar, editar ou deixar em branco
                            para remover.</p>

                        <div id="linesFields" class="space-y-3">
                            @forelse ($lines as $line)
                                <input type="text" name="lines[]" value="{{ $line->text }}"
                                    class="w-full bg-[#f5f5f5] dark:!bg-[#262626] border-gray-300 rounded-md" />
                            @empty
                                @for ($i = 0; $i < 8; $i++)
                                    <input type="text" name="lines[]" class="w-full border-gray-300 rounded-md" />
                                @endfor
                            @endforelse
                        </div>

                        <div class="flex items-center gap-3">
                            <button type="button" onclick="addLineField()" class="px-3 py-2 btn-mmcriativos rounded-md"><i
                                    class="fa-duotone fa-solid fa-circle-plus icon-project mr-2"></i>
                                Adicionar linha</button>
                            <button type="submit" class="px-5 py-2 btn-mmcriativos rounded-md">Salvar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        function addLineField() {
            const wrap = document.getElementById('linesFields');
            const input = document.createElement('input');
            input.type = 'text';
            input.name = 'lines[]';
            input.className = 'w-full border-gray-300 rounded-md';
            wrap.appendChild(input);
        }
    </script>
@endsection
