<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Habilidades</h2>
            <a href="{{ route('admin.skills.create') }}"
               class="inline-flex items-center px-4 py-2 bg-orange-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-orange-700">Adicionar Skill</a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if(session('status'))
                <div class="mb-4 p-3 bg-green-100 text-green-800 rounded">{{ session('status') }}</div>
            @endif
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    @if($skills->isEmpty())
                        <p class="text-gray-600">Nenhuma skill cadastrada ainda.</p>
                    @else
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 pb-2">
                            @foreach($skills as $skill)
                                <div class="border rounded-lg shadow-sm bg-white overflow-hidden flex flex-col transition-transform hover:scale-[1.02] hover:shadow-md">
                                    @if($skill->thumb)
                                        <img src="{{ asset($skill->thumb) }}" alt="{{ $skill->name }}" class="w-full h-36 object-cover">
                                    @endif
                                    <div class="p-4 flex-1 flex flex-col justify-between">
                                        <div class="flex items-center gap-2 mb-3">
                                            @if($skill->icon)
                                                <i class="{{ $skill->icon }} text-orange-600 text-lg"></i>
                                            @endif
                                            <h3 class="font-semibold text-gray-800 text-base">{{ $skill->name }}</h3>
                                        </div>
                                        <div class="flex items-center justify-between mt-auto">
                                            <a href="{{ route('admin.skills.edit', $skill) }}" class="inline-flex items-center gap-1 px-3 py-1.5 bg-orange-600 text-white rounded border border-transparent hover:bg-white hover:text-orange-600 hover:border-orange-600 hover:border-solid text-sm">
                                                <i class="fa-regular fa-pen-to-square"></i>
                                                <span>Editar</span>
                                            </a>
                                            <form method="POST" action="{{ route('admin.skills.destroy', $skill) }}" onsubmit="return confirm('Tem certeza que deseja apagar esta skill?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="inline-flex items-center gap-1 px-3 py-1.5 bg-red-600 text-white rounded hover:bg-red-700 text-sm">
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
