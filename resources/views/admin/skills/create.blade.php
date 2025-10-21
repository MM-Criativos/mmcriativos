<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Nova Habilidade</h2>
            <a href="{{ route('admin.skills.index') }}"
                class="inline-flex items-center gap-1.5 px-6 py-3.5 bg-white text-gray-700 text-sm font-medium rounded-md border border-gray-300 hover:bg-gray-100 transition-colors duration-200">Voltar</a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
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

                    <form method="POST" action="{{ route('admin.skills.store') }}" enctype="multipart/form-data"
                        class="space-y-6">
                        @csrf
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Nome</label>
                                <input type="text" name="name" value="{{ old('name') }}"
                                    class="mt-1 block w-full border-gray-300 rounded-md" required>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Slug</label>
                                <input type="text" name="slug" value="{{ old('slug') }}"
                                    class="mt-1 block w-full border-gray-300 rounded-md" placeholder="opcional">
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Ícone (classe FA)</label>
                                <input type="text" name="icon" value="{{ old('icon') }}"
                                    class="mt-1 block w-full border-gray-300 rounded-md"
                                    placeholder="ex: fa-light fa-code">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Descrição</label>
                                <textarea name="description" rows="3" class="mt-1 block w-full border-gray-300 rounded-md">{{ old('description') }}</textarea>
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                            {{-- Thumb --}}
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Thumb</label>

                                <div class="relative group cursor-pointer w-40 h-40">
                                    <input type="file" name="thumb" accept="image/*"
                                        class="absolute inset-0 opacity-0 cursor-pointer z-10"
                                        onchange="previewImage(event, 'thumb')">

                                    <div id="preview-thumb"
                                        class="flex items-center justify-center w-40 h-40 border border-dashed border-gray-300 rounded bg-gray-50 text-gray-400 text-xs text-center group-hover:bg-orange-50">
                                        <i class="fa-regular fa-image text-base mr-1"></i> Thumb
                                    </div>
                                </div>
                            </div>

                            {{-- Cover --}}
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Cover</label>

                                <div class="relative group cursor-pointer w-40 h-40">
                                    <input type="file" name="cover" accept="image/*"
                                        class="absolute inset-0 opacity-0 cursor-pointer z-10"
                                        onchange="previewImage(event, 'cover')">

                                    <div id="preview-cover"
                                        class="flex items-center justify-center w-40 h-40 border border-dashed border-gray-300 rounded bg-gray-50 text-gray-400 text-xs text-center group-hover:bg-orange-50">
                                        <i class="fa-regular fa-image text-base mr-1"></i> Cover
                                    </div>
                                </div>
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
                     class="w-40 h-40 object-cover rounded border border-gray-200" />`;
                                    }
                                };
                                reader.readAsDataURL(event.target.files[0]);
                            }
                        </script>

                        <div class="flex justify-center">
                            <button type="submit"
                                class="inline-flex items-center px-6 py-4 bg-orange-600 text-white rounded border border-transparent font-semibold text-xs uppercase tracking-widest hover:bg-white hover:text-orange-600 hover:border-orange-600 hover:border-solid">Criar
                                Skill</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
