<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between mb-4">
            <div class="flex items-center gap-3">
                {{-- Título --}}
                <h3 class="text-lg font-semibold">
                    Contatos de {{ $client->name }}
                </h3>



            </div>

            <div class="flex items-center gap-3">
                {{-- Botão Adicionar Contato --}}
                <a href="{{ route('admin.clients.contacts.create', $client) }}"
                    class="inline-flex items-center gap-2 px-6 py-4 bg-orange-600 text-white text-sm font-medium rounded-md border border-transparent hover:bg-white hover:text-orange-600 hover:border-orange-600 transition-colors duration-200">
                    <i class="fa-solid fa-plus"></i>
                </a>
                {{-- Botão Voltar --}}
                <a href="{{ route('admin.clients.index') }}"
                    class="inline-flex items-center gap-1.5 px-6 py-3.5 bg-white text-gray-700 text-sm font-medium rounded-md border border-gray-300 hover:bg-gray-100 transition-colors duration-200">
                    <i class="fa-solid fa-arrow-left text-xs"></i>
                    Voltar
                </a>
            </div>
        </div>

    </x-slot>

    <div class="py-6">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="mb-4 p-3 bg-green-100 text-green-800 rounded">{{ session('status') }}</div>
            @endif
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    @if ($contacts->isEmpty())
                        <p class="text-gray-600">Nenhum contato cadastrado ainda.</p>
                    @else
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                            @foreach ($contacts as $contact)
                                <div class="border rounded-lg shadow-sm bg-white p-4 flex items-start gap-3">
                                    <div class="shrink-0">
                                        @if ($contact->photo)
                                            <img src="{{ asset($contact->photo) }}" alt="{{ $contact->name }}"
                                                style="width:90px;height:90px;border-radius:10px;object-fit:cover;">
                                        @else
                                            <div style="width:90px;height:90px;border-radius:10px;"
                                                class="bg-gray-200 flex items-center justify-center text-xs text-gray-600">
                                                {{ strtoupper(substr($contact->name, 0, 1)) }}</div>
                                        @endif
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <div class="flex flex-col">
                                            {{-- Nome do contato --}}
                                            <div class="flex items-center gap-2">
                                                <h3 class="font-medium text-gray-800 truncate">{{ $contact->name }}</h3>
                                            </div>

                                            {{-- Função + badge --}}
                                            @if ($contact->role)
                                                <div class="flex items-center gap-2 text-sm text-gray-500">
                                                    <p>{{ $contact->role }}</p>

                                                    @if ($contact->is_primary)
                                                        <span
                                                            class="text-xs bg-green-100 text-green-700 px-2 py-0.5 rounded">Principal</span>
                                                    @endif
                                                </div>
                                            @endif
                                        </div>

                                        <div class="mt-3 flex items-center gap-2">
                                            <a href="{{ route('admin.contacts.edit', $contact) }}"
                                                class="inline-flex items-center gap-1 px-5 py-3 bg-orange-600 text-white rounded border border-transparent hover:bg-white hover:text-orange-600 hover:border-orange-600 text-sm">
                                                <i class="fa-regular fa-pen-to-square"></i> Editar
                                            </a>
                                            <form method="POST"
                                                action="{{ route('admin.contacts.destroy', $contact) }}"
                                                onsubmit="return confirm('Remover este contato?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                    class="inline-flex items-center gap-1 px-5 py-3 bg-red-600 text-white rounded hover:bg-red-700 text-sm">
                                                    <i class="fa-regular fa-trash-can"></i> Apagar
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
