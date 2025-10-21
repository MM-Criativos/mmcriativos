<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Depoimentos</h2>
            <a href="{{ route('admin.testimonials.create') }}"
                class="inline-flex items-center px-6 py-4 bg-orange-600 text-white rounded border border-transparent font-semibold text-xs uppercase tracking-widest hover:bg-white hover:text-orange-600 hover:border-orange-600 hover:border-solid">
                Novo Depoimento
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="mb-4 p-3 bg-green-100 text-green-800 rounded">{{ session('status') }}</div>
            @endif
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    @if ($testimonials->isEmpty())
                        <p class="text-gray-600">Nenhum depoimento cadastrado ainda.</p>
                    @else
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                            @foreach ($testimonials as $testimonial)
                                <div class="border rounded-lg shadow-sm bg-white p-4 flex items-start gap-3">
                                    <div class="shrink-0">
                                        <img src="{{ $testimonial->photo_url }}"
                                            alt="{{ $testimonial->contact?->name ?? 'Contato' }}"
                                            style="width:90px;height:90px;border-radius:20px;object-fit:cover;">
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <h3 class="font-medium text-gray-800 truncate">
                                            {{ $testimonial->contact?->name ?? 'Contato' }}</h3>
                                        <p class="text-sm text-gray-500 truncate">
                                            {{ $testimonial->author_position ?? $testimonial->contact?->role }}
                                            @if ($testimonial->client)
                                                — {{ $testimonial->client->name }}
                                            @endif
                                        </p>
                                        @if ($testimonial->title)
                                            <p class="mt-1 text-sm text-gray-600 truncate">“{{ $testimonial->title }}”
                                            </p>
                                        @endif
                                        <div class="mt-3 flex items-center gap-2">
                                            <a href="{{ route('admin.testimonials.edit', $testimonial) }}"
                                                class="inline-flex items-center gap-1 px-5 py-3 bg-orange-600 text-white rounded border border-transparent hover:bg-white hover:text-orange-600 hover:border-orange-600 text-sm">
                                                <i class="fa-regular fa-pen-to-square"></i> Editar
                                            </a>
                                            <form method="POST"
                                                action="{{ route('admin.testimonials.destroy', $testimonial) }}"
                                                onsubmit="return confirm('Remover este depoimento?');">
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
