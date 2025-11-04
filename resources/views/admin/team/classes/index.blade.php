<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Classes</h2>
            <a href="{{ route('admin.team.index') }}"
               class="inline-flex items-center gap-1.5 px-6 py-3.5 bg-white text-gray-700 text-sm font-medium rounded-md border border-gray-300 hover:bg-gray-100 transition-colors duration-200">Voltar</a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="mb-4 p-3 bg-green-100 text-green-800 rounded">{{ session('status') }}</div>
            @endif
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead>
                                <tr>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">#</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Classe</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Hierarquia</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ações</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                @foreach ($classes as $c)
                                    <tr>
                                        <td class="px-4 py-3">{{ $c->id }}</td>
                                        <td class="px-4 py-3">{{ $c->classe }}</td>
                                        <td class="px-4 py-3">{{ $c->hierarquia }}</td>
                                        <td class="px-4 py-3">
                                            <a href="{{ route('admin.classes.edit', $c) }}"
                                               class="inline-flex items-center gap-1 px-3 py-2 bg-white text-gray-700 rounded text-xs border border-gray-300 hover:bg-gray-100">
                                                <i class="fa-regular fa-pen-to-square"></i> Editar
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

