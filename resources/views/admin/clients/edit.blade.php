<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Editar Cliente</h2>
            <a href="{{ route('admin.clients.index') }}" class="text-gray-600 hover:underline">Voltar</a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="mb-4 p-3 bg-green-100 text-green-800 rounded">{{ session('status') }}</div>
            @endif

            @if ($errors->any())
                <div class="mb-4 p-3 bg-red-100 text-red-800 rounded">
                    <ul class="list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 space-y-10">
                    <form method="POST" action="{{ route('admin.clients.update', $client) }}"
                        enctype="multipart/form-data" class="space-y-6">
                        @csrf
                        @method('PUT')

                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-semibold">Informações Gerais</h3>

                            <a href="{{ route('admin.clients.contacts.index', $client) }}"
                                class="inline-flex items-center gap-2 px-6 py-4 bg-orange-600 text-white text-sm font-medium rounded-md border border-transparent hover:bg-white hover:text-orange-600 hover:border-orange-600 transition-colors duration-200">
                                <i class="fa-regular fa-address-book"></i>
                                Contatos
                            </a>
                        </div>


                        {{-- Linha 1: 3 colunas (logo | nome+slug | website+setor) --}}
                        <div class="grid grid-cols-1 md:grid-cols-12 gap-6 items-start">

                            {{-- Coluna 1: Logo (ocupa menos espaço) --}}
                            <div class="md:col-span-3 lg:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Logo</label>
                                <div class="relative group cursor-pointer w-40 h-40 shrink-0">
                                    <input type="file" name="logo" accept="image/*"
                                        class="absolute inset-0 opacity-0 cursor-pointer z-10"
                                        onchange="previewImage(event, 'client-logo')">

                                    @if ($client->logo)
                                        <img id="preview-client-logo" src="{{ asset('storage/' . $client->logo) }}"
                                            alt="Logo"
                                            class="w-40 h-40 object-cover rounded border border-gray-200 group-hover:opacity-80 transition">
                                    @else
                                        <div id="preview-client-logo"
                                            class="flex items-center justify-center w-40 h-40 border border-dashed border-gray-300 rounded bg-gray-50 text-gray-400 text-xs text-center group-hover:bg-orange-50">
                                            <i class="fa-regular fa-image text-base mr-1"></i> Selecionar
                                        </div>
                                    @endif

                                </div>
                            </div>

                            {{-- Coluna 2: Nome (em cima) e Slug (embaixo) --}}
                            <div class="md:col-span-5 flex flex-col gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Nome</label>
                                    <input type="text" name="name" value="{{ old('name', $client->name) }}"
                                        class="mt-1 block w-full border-gray-300 rounded-md" required>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Slug</label>
                                    <input type="text" name="slug" value="{{ old('slug', $client->slug) }}"
                                        class="mt-1 block w-full border-gray-300 rounded-md" required>
                                </div>
                            </div>

                            {{-- Coluna 3: Website (em cima) e Setor (embaixo) --}}
                            <div class="md:col-span-4 lg:col-span-5 flex flex-col gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Website</label>
                                    <input type="text" name="website" value="{{ old('website', $client->website) }}"
                                        class="mt-1 block w-full border-gray-300 rounded-md" placeholder="https://...">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Setor</label>
                                    <input type="text" name="sector" value="{{ old('sector', $client->sector) }}"
                                        class="mt-1 block w-full border-gray-300 rounded-md"
                                        placeholder="ex: Tecnologia, Saúde">
                                </div>
                            </div>
                        </div>

                        {{-- Linha 2: Descrição (coluna única) --}}
                        <div class="mt-6">
                            <label class="block text-sm font-medium text-gray-700">Descrição</label>
                            <textarea name="description" rows="3" class="mt-1 block w-full border-gray-300 rounded-md">{{ old('description', $client->description) }}</textarea>
                        </div>

                        <script>
                            function previewImage(event, id) {
                                const reader = new FileReader();
                                const preview = document.getElementById(`preview-${id}`);
                                reader.onload = () => {
                                    if (preview.tagName === 'IMG') {
                                        preview.src = reader.result;
                                    } else {
                                        preview.outerHTML =
                                            `<img id="preview-${id}" src="${reader.result}" class="w-28 h-28 object-cover rounded border border-gray-200" />`;
                                    }
                                };
                                reader.readAsDataURL(event.target.files[0]);
                            }
                        </script>


                        <div class="flex justify-center mt-10">
                            <button type="submit"
                                class="inline-flex items-center px-6 py-4 bg-orange-600 text-white rounded border border-transparent hover:bg-white hover:text-orange-600 hover:border-orange-600 hover:border-solid text-sm">Salvar</button>
                        </div>
                    </form>

                    <div class="border-t pt-6">
                        <h3 class="text-lg font-semibold mb-4">Redes Sociais</h3>
                        @php
                            $socialMap = $client->clientSocialMedia->keyBy('social_media_id');
                        @endphp
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            @foreach ($socials as $social)
                                @php
                                    $existing = $socialMap->get($social->id);
                                @endphp
                                <div class="p-4 rounded border border-gray-100 shadow-sm bg-white">
                                    <div class="flex items-center gap-2 mb-3">
                                        <i class="{{ $social->icon }} text-gray-700"></i>
                                        <span class="font-medium text-gray-800">{{ $social->name }}</span>
                                    </div>

                                    <form method="POST"
                                        action="{{ route('admin.clients.socials.upsert', [$client, $social]) }}"
                                        class="space-y-3">
                                        @csrf
                                        @method('PUT')
                                        <label class="block text-sm font-medium text-gray-700">Usuário/URL</label>
                                        <input type="text" name="user"
                                            value="{{ old('user', optional($existing)->user) }}"
                                            class="mt-1 block w-full border-gray-300 rounded-md text-sm"
                                            placeholder="https://..." @if (!$existing) required @endif>
                                        <div class="flex items-center gap-2">
                                            <button type="submit"
                                                class="flex-1 inline-flex items-center justify-center px-3 py-2 bg-orange-600 text-white rounded border border-transparent hover:bg-white hover:text-orange-600 hover:border-orange-600 hover:border-solid text-sm">
                                                <i class="fa-regular fa-floppy-disk mr-1"></i> Salvar
                                            </button>
                                            @if ($existing)
                                                <span class="text-xs text-gray-500">Deixe em branco e salve para
                                                    remover.</span>
                                            @endif
                                        </div>
                                    </form>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
