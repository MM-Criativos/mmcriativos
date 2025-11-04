<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Templates de E-mail</h2>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @include('admin.commercial._tabs')
            @if (session('status'))
                <div class="mb-4 p-3 bg-green-100 text-green-800 rounded">{{ session('status') }}</div>
            @endif

            <div class="bg-white dark:bg-dark-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <div class="mb-4">
                        <a href="{{ route('admin.commercial.email-templates.create') }}" class="inline-flex items-center px-5 py-3 bg-orange-600 text-white rounded border border-transparent hover:bg-orange-700">Novo Template</a>
                    </div>
                    @if ($templates->isEmpty())
                        <p class="text-gray-600">Nenhum template encontrado.</p>
                    @else
                        <div class="overflow-x-auto">
                            <table class="min-w-full">
                                <thead>
                                <tr class="text-left text-gray-600 text-sm border-b">
                                    <th class="py-2 pr-4">Key</th>
                                    <th class="py-2 pr-4">Nome</th>
                                    <th class="py-2 pr-4">Ativo</th>
                                    <th class="py-2 pr-4">Ações</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach ($templates as $t)
                                    <tr class="border-b">
                                        <td class="py-3 pr-4">{{ $t->key }}</td>
                                        <td class="py-3 pr-4">{{ $t->name }}</td>
                                        <td class="py-3 pr-4">{{ $t->is_active ? 'Sim' : 'Não' }}</td>
                                        <td class="py-3 pr-4 text-sm flex gap-3">
                                            <a href="{{ route('admin.commercial.email-templates.edit', $t) }}" class="inline-flex items-center px-3 py-2 bg-orange-600 text-white rounded hover:bg-orange-700">Editar</a>
                                            <a href="{{ route('admin.commercial.email-templates.preview', $t) }}" target="_blank" class="inline-flex items-center px-3 py-2 bg-gray-500 text-white rounded hover:bg-gray-600 dark:bg-gray-500 dark:hover:bg-gray-400">Preview</a>
                                            <form method="POST" action="{{ route('admin.commercial.email-templates.destroy', $t) }}" onsubmit="return confirm('Remover template?');">
                                                @csrf
                                                @method('DELETE')
                                                <button class="inline-flex items-center px-3 py-2 bg-red-600 text-white rounded hover:bg-red-700">Excluir</button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-4">{{ $templates->links() }}</div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
