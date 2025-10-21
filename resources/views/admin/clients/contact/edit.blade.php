<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Editar Contato: {{ $contact->name }}</h2>
            <div class="flex items-center gap-2">
                <a href="{{ route('admin.clients.contacts.index', $client) }}"
                    class="inline-flex items-center gap-1.5 px-6 py-3.5 bg-white text-gray-700 text-sm font-medium rounded-md border border-gray-300 hover:bg-gray-100 transition-colors duration-200">Voltar</a>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="mb-4 p-3 bg-green-100 text-green-800 rounded">{{ session('status') }}</div>
            @endif
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

                    <form method="POST" action="{{ route('admin.contacts.update', $contact) }}"
                        enctype="multipart/form-data" class="space-y-6">
                        @csrf
                        @method('PUT')

                        <div class="flex items-start justify-between gap-6">

                            {{-- Foto --}}
                            <div>

                                <div class="relative group cursor-pointer w-40 h-40">
                                    {{-- Input invisível que cobre o preview --}}
                                    <input type="file" name="photo" accept="image/*"
                                        class="absolute inset-0 opacity-0 cursor-pointer z-10"
                                        onchange="previewImage(event, 'contact-photo-{{ $contact->id }}')">

                                    {{-- Preview existente ou placeholder --}}
                                    @if ($contact->photo)
                                        <img id="preview-contact-photo-{{ $contact->id }}"
                                            src="{{ asset($contact->photo) }}" alt="Foto"
                                            class="w-40 h-40 object-cover rounded-full border border-gray-200 group-hover:opacity-80 transition">
                                    @else
                                        <div id="preview-contact-photo-{{ $contact->id }}"
                                            class="flex items-center justify-center w-40 h-40 border border-dashed border-gray-300 rounded-full bg-gray-50 text-gray-400 text-xs text-center group-hover:bg-orange-50">
                                            <i class="fa-regular fa-image text-base mr-1"></i> Foto
                                        </div>
                                    @endif
                                </div>
                            </div>

                            {{-- Checkbox "Contato principal" alinhado à direita --}}
                            <div class="mt-6">
                                <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                                    <input type="checkbox" name="is_primary" value="1"
                                        class="rounded border-gray-300"
                                        {{ old('is_primary', $contact->is_primary) ? 'checked' : '' }}>
                                    Contato principal
                                </label>
                            </div>

                        </div>

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


                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Nome</label>
                                <input type="text" name="name" value="{{ old('name', $contact->name) }}"
                                    class="mt-1 block w-full border-gray-300 rounded-md" required>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Cargo</label>
                                <input type="text" name="role" value="{{ old('role', $contact->role) }}"
                                    class="mt-1 block w-full border-gray-300 rounded-md"
                                    placeholder="ex: Diretor de Marketing">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Email</label>
                                <input type="text" name="email" value="{{ old('email', $contact->email) }}"
                                    class="mt-1 block w-full border-gray-300 rounded-md">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Telefone</label>
                                <input type="text" name="phone" value="{{ old('phone', $contact->phone) }}"
                                    class="mt-1 block w-full border-gray-300 rounded-md">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">LinkedIn</label>
                                <input type="text" name="linkedin" value="{{ old('linkedin', $contact->linkedin) }}"
                                    class="mt-1 block w-full border-gray-300 rounded-md"
                                    placeholder="https://linkedin.com/in/...">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Website</label>
                                <input type="text" name="website" value="{{ old('website', $contact->website) }}"
                                    class="mt-1 block w-full border-gray-300 rounded-md" placeholder="https://...">
                            </div>
                        </div>



                        <div class="flex justify-center">
                            <button type="submit"
                                class="inline-flex items-center px-6 py-4 bg-orange-600 text-white rounded border border-transparent hover:bg-white hover:text-orange-600 hover:border-orange-600 hover:border-solid text-sm">Salvar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
