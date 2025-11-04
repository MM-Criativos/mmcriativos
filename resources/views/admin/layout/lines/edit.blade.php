<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-100 leading-tight">Editar Linhas</h2>
            <a href="{{ route('admin.layout.index') }}"
                class="inline-flex items-center gap-1.5 px-4 py-2 bg-white dark:bg-dark-800 text-gray-700 dark:text-gray-200 rounded-md border hover:bg-gray-50 dark:hover:bg-dark-700">Voltar</a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="mb-4 p-3 bg-green-100 text-green-800 rounded">{{ session('status') }}</div>
            @endif
            <div class="bg-white dark:bg-dark-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <form method="POST" action="{{ route('admin.layout.lines.update') }}" class="space-y-4">
                        @csrf
                        @method('PUT')

                        <p class="text-sm text-gray-600">Um campo por frase. Você pode adicionar, editar ou deixar em branco para remover.</p>

                        <div id="linesFields" class="space-y-3">
                            @forelse ($lines as $line)
                                <input type="text" name="lines[]" value="{{ $line->text }}" class="w-full border-gray-300 rounded-md" />
                            @empty
                                @for ($i = 0; $i < 8; $i++)
                                    <input type="text" name="lines[]" class="w-full border-gray-300 rounded-md" />
                                @endfor
                            @endforelse
                        </div>

                        <div class="flex items-center gap-3">
                            <button type="button" onclick="addLineField()" class="px-3 py-2 bg-gray-100 rounded hover:bg-gray-200">+ Adicionar linha</button>
                            <button type="submit" class="px-5 py-2 bg-orange-600 text-white rounded hover:bg-orange-700">Salvar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        function addLineField(){
            const wrap = document.getElementById('linesFields');
            const input = document.createElement('input');
            input.type = 'text';
            input.name = 'lines[]';
            input.className = 'w-full border-gray-300 rounded-md';
            wrap.appendChild(input);
        }
    </script>
</x-app-layout>
