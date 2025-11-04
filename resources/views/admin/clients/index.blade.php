<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Clientes</h2>
            <a href="{{ route('admin.clients.create') }}"
                class="inline-flex items-center px-6 py-4 bg-orange-600 text-white rounded border border-transparent font-semibold text-xs uppercase tracking-widest hover:bg-white hover:text-orange-600 hover:border-orange-600 hover:border-solid">Adicionar
                Cliente</a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @include('admin.commercial._tabs')
            @if (session('status'))
                <div class="mb-4 p-3 bg-green-100 text-green-800 rounded">{{ session('status') }}</div>
            @endif
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    @if ($clients->isEmpty())
                        <p class="text-gray-600">Nenhum cliente cadastrado ainda.</p>
                    @else
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 pb-2">
                            @foreach ($clients as $client)
                                <div
                                    class="border rounded-lg shadow-sm bg-white overflow-hidden flex flex-col transition-transform hover:scale-[1.02] hover:shadow-md">

                                    {{-- Linha com logo e nome --}}
                                    <div class="flex items-center gap-3 p-4 border-b border-gray-100">
                                        @if ($client->logo)
                                            <img src="{{ asset('storage/' . $client->logo) }}" alt="{{ $client->name }}"
                                                class="w-20 h-20 object-cover rounded border border-gray-200">
                                        @else
                                            <div
                                                class="w-20 h-20 flex items-center justify-center bg-gray-100 text-gray-400 text-xs rounded border border-gray-200">
                                                <i class="fa-regular fa-image text-base"></i>
                                            </div>
                                        @endif

                                        <h3 class="font-semibold text-gray-800 text-base truncate">{{ $client->name }}
                                        </h3>
                                    </div>

                                    {{-- Corpo do card --}}
                                    <div class="p-4 flex-1 flex flex-col justify-between">
                                        <div class="flex items-center justify-between mt-auto">
                                            <a href="{{ route('admin.clients.edit', $client) }}"
                                                class="inline-flex items-center gap-1 px-5 py-3 bg-orange-600 text-white rounded border border-transparent hover:bg-white hover:text-orange-600 hover:border-orange-600 hover:border-solid text-sm">
                                                <i class="fa-regular fa-pen-to-square"></i>
                                                <span>Editar</span>
                                            </a>

                                            <form method="POST" action="{{ route('admin.clients.destroy', $client) }}"
                                                onsubmit="return confirm('Tem certeza que deseja apagar este cliente?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                    class="inline-flex items-center gap-1 px-5 py-3 bg-red-600 text-white rounded hover:bg-red-700 text-sm">
                                                    <i class="fa-regular fa-trash"></i>
                                                    <span>Apagar</span>
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
