<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Serviços</h2>
            <a href="{{ route('admin.services.create') }}"
                class="inline-flex items-center px-6 py-4 bg-orange-600 text-white rounded border border-transparent font-semibold text-xs uppercase tracking-widest hover:bg-white hover:text-orange-600 hover:border-orange-600 hover:border-solid">
                Adicionar Serviço
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
                    @if ($services->isEmpty())
                        <p class="text-gray-600">Nenhum serviço cadastrado ainda.</p>
                    @else
                        <div id="serviceDnd"
                            class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 pb-2 overflow-x-auto sm:overflow-x-visible">
                            @foreach ($services as $service)
                                <div
                                    class="min-w-[260px] sm:min-w-0 border rounded-lg shadow-sm bg-white overflow-hidden flex flex-col transition-transform hover:scale-[1.02] hover:shadow-md">
                                    @if ($service->thumb)
                                        <img src="{{ asset($service->thumb) }}" alt="{{ $service->name }}"
                                            class="w-full h-36 object-cover" draggable="false" draggable="false">
                                    @endif

                                    <div class="p-4 flex-1 flex flex-col justify-between">
                                        <div class="flex items-center gap-2 mb-3">
                                            @if ($service->icon)
                                                <i class="{{ $service->icon }} text-orange-600 text-lg"></i>
                                            @endif
                                            <h3 class="font-semibold text-gray-800 text-base">{{ $service->name }}</h3>
                                        </div>

                                        <div class="flex items-center justify-between mt-auto">
                                            <a href="{{ route('admin.services.edit', $service) }}"
                                                class="inline-flex items-center gap-1 px-5 py-3 bg-orange-600 text-white rounded border border-transparent hover:bg-white hover:text-orange-600 hover:border-orange-600 hover:border-solid text-sm transition-colors duration-200">
                                                <i class="fa-regular fa-pen-to-square"></i>
                                                <span>Editar</span>
                                            </a>
                                            <form method="POST"
                                                action="{{ route('admin.services.destroy', $service) }}"
                                                onsubmit="return confirm('Tem certeza que deseja apagar este serviço?');">
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
