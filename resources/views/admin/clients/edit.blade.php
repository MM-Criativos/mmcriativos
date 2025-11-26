<style>
    .navbar-header {
        margin-top: -20px !important;
    }
</style>

@extends('layouts.app')

@php
    $title = 'Clientes';
    $subTitle = 'Editar cliente';
@endphp

@section('content')
    <div class="grid grid-cols-1">
        @if (session('status'))
            <div class="mb-4 p-3 bg-green-100 text-green-800 rounded">{{ session('status') }}</div>
        @endif


        <div class="bg-white dark:bg-black overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <form method="POST" action="{{ route('admin.clients.update', $client) }}" enctype="multipart/form-data"
                    class="">
                    @csrf
                    @method('PUT')

                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold">Informações Gerais</h3>

                        <a href="{{ route('admin.clients.contacts.index', $client) }}"
                            class="inline-flex items-center gap-2 px-6 py-4 rounded-md btn-mmcriativos">
                            <i class="fa-duotone fa-solid fa-address-book icon-project"></i>
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
                                    class="mt-1 block w-full bg-[#f5f5f5] dark:!bg-[#262626] border-gray-300 rounded-md"
                                    required>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Slug</label>
                                <input type="text" name="slug" value="{{ old('slug', $client->slug) }}"
                                    class="mt-1 block w-full bg-[#f5f5f5] dark:!bg-[#262626] border-gray-300 rounded-md"
                                    required>
                            </div>
                        </div>

                        {{-- Coluna 3: Website (em cima) e Setor (embaixo) --}}
                        <div class="md:col-span-4 lg:col-span-5 flex flex-col gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Website</label>
                                <input type="text" name="website" value="{{ old('website', $client->website) }}"
                                    class="mt-1 block w-full bg-[#f5f5f5] dark:!bg-[#262626] border-gray-300 rounded-md"
                                    placeholder="https://...">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Setor</label>
                                <input type="text" name="sector" value="{{ old('sector', $client->sector) }}"
                                    class="mt-1 block w-full bg-[#f5f5f5] dark:!bg-[#262626] border-gray-300 rounded-md"
                                    placeholder="ex: Tecnologia, Saúde">
                            </div>
                        </div>
                    </div>

                    {{-- Linha 2: Descrição (coluna única) --}}
                    <div class="mt-6">
                        <label class="block text-sm font-medium text-gray-700">Descrição</label>
                        <textarea name="description" rows="3"
                            class="mt-1 block w-full bg-[#f5f5f5] dark:!bg-[#262626] border-gray-300 rounded-md">{{ old('description', $client->description) }}</textarea>
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


                    <div class="flex justify-center mt-10 mb-10">
                        <button type="submit"
                            class="inline-flex items-center px-6 py-4 rounded-md btn-mmcriativos">Salvar</button>
                    </div>
                </form>

                <div class="border-t pt-6">
                    <h3 class="text-lg font-semibold mb-4">Endereço e Contato</h3>
                    <form method="POST" action="{{ route('admin.clients.info.update', $client) }}" class="space-y-6">
                        @csrf
                        @method('PUT')
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">CEP</label>
                                <input type="text" name="cep" value="{{ old('cep', optional($client->info)->cep) }}"
                                    class="mt-1 block w-full bg-[#f5f5f5] dark:!bg-[#262626] border-gray-300 rounded-md"
                                    placeholder="00000-000">
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700">Rua</label>
                                <input type="text" name="street"
                                    value="{{ old('street', optional($client->info)->street) }}"
                                    class="mt-1 block w-full bg-[#f5f5f5] dark:!bg-[#262626] border-gray-300 rounded-md">
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Número</label>
                                <input type="text" name="number"
                                    value="{{ old('number', optional($client->info)->number) }}"
                                    class="mt-1 block w-full bg-[#f5f5f5] dark:!bg-[#262626] border-gray-300 rounded-md">
                            </div>
                            <div class="md:col-span-3">
                                <label class="block text-sm font-medium text-gray-700">Complemento</label>
                                <input type="text" name="complement"
                                    value="{{ old('complement', optional($client->info)->complement) }}"
                                    class="mt-1 block w-full bg-[#f5f5f5] dark:!bg-[#262626] border-gray-300 rounded-md">
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Bairro</label>
                                <input type="text" name="neighborhood"
                                    value="{{ old('neighborhood', optional($client->info)->neighborhood) }}"
                                    class="mt-1 block w-full bg-[#f5f5f5] dark:!bg-[#262626] border-gray-300 rounded-md">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Cidade</label>
                                <input type="text" name="city"
                                    value="{{ old('city', optional($client->info)->city) }}"
                                    class="mt-1 block w-full bg-[#f5f5f5] dark:!bg-[#262626] border-gray-300 rounded-md">
                            </div>
                            <div class="grid grid-cols-3 gap-3">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Estado</label>
                                    <input type="text" name="state"
                                        value="{{ old('state', optional($client->info)->state) }}"
                                        class="mt-1 block w-full bg-[#f5f5f5] dark:!bg-[#262626] border-gray-300 rounded-md">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">UF</label>
                                    <input type="text" name="state_code"
                                        value="{{ old('state_code', optional($client->info)->state_code) }}"
                                        class="mt-1 block w-full bg-[#f5f5f5] dark:!bg-[#262626] border-gray-300 rounded-md"
                                        placeholder="SP">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">País</label>
                                    <input type="text" name="country"
                                        value="{{ old('country', optional($client->info)->country ?? 'Brasil') }}"
                                        class="mt-1 block w-full bg-[#f5f5f5] dark:!bg-[#262626] border-gray-300 rounded-md">
                                </div>
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">E-mail Comercial</label>
                                <input type="text" name="email_commercial"
                                    value="{{ old('email_commercial', optional($client->info)->email_commercial) }}"
                                    class="mt-1 block w-full bg-[#f5f5f5] dark:!bg-[#262626] border-gray-300 rounded-md">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Telefone</label>
                                <input type="text" name="phone"
                                    value="{{ old('phone', optional($client->info)->phone) }}"
                                    class="mt-1 block w-full bg-[#f5f5f5] dark:!bg-[#262626] border-gray-300 rounded-md">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Telefone (alt.)</label>
                                <input type="text" name="phone_alt"
                                    value="{{ old('phone_alt', optional($client->info)->phone_alt) }}"
                                    class="mt-1 block w-full bg-[#f5f5f5] dark:!bg-[#262626] border-gray-300 rounded-md">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">WhatsApp</label>
                                <input type="text" name="whatsapp"
                                    value="{{ old('whatsapp', optional($client->info)->whatsapp) }}"
                                    class="mt-1 block w-full bg-[#f5f5f5] dark:!bg-[#262626] border-gray-300 rounded-md"
                                    placeholder="(xx) xxxxx-xxxx">
                            </div>
                        </div>
                        <div class="flex justify-center">
                            <button type="submit"
                                class="inline-flex items-center px-6 py-4 rounded-md btn-mmcriativos mt-5 mb-10">Salvar
                                Endereço/Contato</button>
                        </div>
                    </form>
                </div>

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
                            <div class="p-4 rounded border border-gray-100 shadow-sm bg-white dark:bg-[#262626]">
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
                                        class="mt-1 block w-full bg-white dark:!bg-black border-gray-300 rounded-md text-sm"
                                        placeholder="https://..." @if (!$existing) required @endif>
                                    <div class="flex items-center justify-end gap-2">
                                        <button type="submit"
                                            class="inline-flex items-center px-6 py-4 rounded-md btn-mmcriativos text-sm">
                                            <i
                                                class="fa-duotone fa-solid fa-arrow-rotate-right icon-project mr-2"></i>Salvar
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
@endsection

<script>
    async function buscarEnderecoPorCep(input) {
        const cep = input.value.replace(/\D/g, '');
        if (cep.length !== 8) return;

        try {
            const response = await fetch(`/cep/${cep}`);
            const data = await response.json();

            if (data.error) {
                console.warn(data.error);
                return;
            }

            // Preenche os campos
            document.querySelector('input[name="street"]').value = data.street || '';
            document.querySelector('input[name="neighborhood"]').value = data.neighborhood || '';
            document.querySelector('input[name="city"]').value = data.city || '';
            document.querySelector('input[name="state"]').value = data.state || '';
            document.querySelector('input[name="complement"]').value = data.complement || '';
        } catch (e) {
            console.error('Erro ao buscar o CEP:', e);
        }
    }
</script>
