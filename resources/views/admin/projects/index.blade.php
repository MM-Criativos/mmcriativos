<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Projetos</h2>
            <a href="{{ route('admin.projects.create') }}"
                class="inline-flex items-center px-6 py-4 bg-orange-600 text-white rounded border border-transparent font-semibold text-xs uppercase tracking-widest hover:bg-white hover:text-orange-600 hover:border-orange-600 hover:border-solid">
                Adicionar Projeto
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="mb-4 p-3 bg-green-100 text-green-800 rounded">{{ session('status') }}</div>
            @endif
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                        <a href="{{ route('admin.projects.progress.index') }}"
                           class="block bg-white dark:bg-dark-800 border rounded-lg p-6 shadow-sm hover:bg-gray-50 dark:hover:bg-dark-700">
                            <div class="text-lg font-semibold">Em andamento</div>
                            <p class="text-sm text-gray-600">Projetos ativos em produção.</p>
                        </a>
                        <a href="{{ route('admin.projects.completed.index') }}"
                           class="block bg-white dark:bg-dark-800 border rounded-lg p-6 shadow-sm hover:bg-gray-50 dark:hover:bg-dark-700">
                            <div class="text-lg font-semibold">Concluídos</div>
                            <p class="text-sm text-gray-600">Projetos finalizados e publicados.</p>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

</x-app-layout>
