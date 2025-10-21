<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Novo Contato — {{ $client->name }}</h2>
            <div class="flex items-center gap-2">
                <a href="{{ route('admin.clients.contacts.index', $client) }}"
                    class="inline-flex items-center gap-1.5 px-6 py-3.5 bg-white text-gray-700 text-sm font-medium rounded-md border border-gray-300 hover:bg-gray-100 transition-colors duration-200">Voltar</a>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    @if ($errors->any())
                        <div class="mb-4 p-3 bg-red-100 text-red-800 rounded">
                            <ul class="list-disc list-inside">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('admin.clients.contacts.store', $client) }}"
                        enctype="multipart/form-data" class="space-y-6">
                        @csrf

                        {{-- Linha da foto + contato principal --}}
                        <div class="flex items-start justify-between gap-6">
                            {{-- Foto com preview interativo --}}
                            <div>

                                <div class="relative group cursor-pointer w-40 h-40">
                                    <input type="file" name="photo" accept="image/*"
                                        class="absolute inset-0 opacity-0 cursor-pointer z-10"
                                        onchange="previewImage(event, 'contact-photo-new')">

                                    <div id="preview-contact-photo-new"
                                        class="flex items-center justify-center w-40 h-40 border border-dashed border-gray-300 rounded-full bg-gray-50 text-gray-400 text-xs text-center group-hover:bg-orange-50">
                                        <i class="fa-regular fa-image text-base mr-1"></i> Foto
                                    </div>
                                </div>
                            </div>

                            {{-- Checkbox Contato principal --}}
                            <div class="mt-6">
                                <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                                    <input type="checkbox" name="is_primary" value="1"
                                        class="rounded border-gray-300">
                                    Contato principal
                                </label>
                            </div>
                        </div>

                        {{-- Nome e Cargo --}}
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Nome</label>
                                <input type="text" name="name" value="{{ old('name') }}"
                                    class="mt-1 block w-full border-gray-300 rounded-md" required>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Cargo</label>
                                <input type="text" name="role" value="{{ old('role') }}"
                                    class="mt-1 block w-full border-gray-300 rounded-md"
                                    placeholder="ex: Diretor de Marketing">
                            </div>
                        </div>

                        {{-- Email e Telefone --}}
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Email</label>
                                <input type="text" name="email" value="{{ old('email') }}"
                                    class="mt-1 block w-full border-gray-300 rounded-md">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Telefone</label>
                                <input type="text" name="phone" value="{{ old('phone') }}"
                                    class="mt-1 block w-full border-gray-300 rounded-md">
                            </div>
                        </div>

                        {{-- LinkedIn e Website --}}
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">LinkedIn</label>
                                <input type="text" name="linkedin" value="{{ old('linkedin') }}"
                                    class="mt-1 block w-full border-gray-300 rounded-md"
                                    placeholder="https://linkedin.com/in/...">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Website</label>
                                <input type="text" name="website" value="{{ old('website') }}"
                                    class="mt-1 block w-full border-gray-300 rounded-md" placeholder="https://...">
                            </div>
                        </div>

                        {{-- Botão de envio --}}
                        <div class="flex justify-center">
                            <button type="submit"
                                class="inline-flex items-center px-6 py-4 bg-orange-600 text-white rounded border border-transparent font-semibold text-xs uppercase tracking-widest hover:bg-white hover:text-orange-600 hover:border-orange-600 hover:border-solid">
                                Criar Contato
                            </button>
                        </div>
                    </form>

                    {{-- Script do preview --}}
                    <script>
                        function previewImage(event, id) {
                            const reader = new FileReader();
                            const preview = document.getElementById(`preview-${id}`);
                            reader.onload = () => {
                                if (preview.tagName === 'IMG') {
                                    preview.src = reader.result;
                                } else {
                                    preview.outerHTML = `
                <img id="preview-${id}"
                     src="${reader.result}"
                     class="w-40 h-40 object-cover rounded-full border border-gray-200" />`;
                                }
                            };
                            reader.readAsDataURL(event.target.files[0]);
                        }
                    </script>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
