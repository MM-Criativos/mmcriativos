<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-100 leading-tight">Editar Sobre nós</h2>
            <a href="{{ route('admin.layout.index') }}"
               class="inline-flex items-center gap-1.5 px-4 py-2 bg-white dark:bg-dark-800 text-gray-700 dark:text-gray-200 rounded-md border hover:bg-gray-50 dark:hover:bg-dark-700">Voltar</a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="mb-4 p-3 bg-green-100 text-green-800 rounded">{{ session('status') }}</div>
            @endif
            <div class="bg-white dark:bg-dark-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <form method="POST" action="{{ route('admin.layout.aboutus.update') }}" enctype="multipart/form-data" class="space-y-6">
                        @csrf
                        @method('PUT')

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Título</label>
                                <input type="text" name="title" value="{{ old('title', $about->title) }}" class="mt-1 block w-full border-gray-300 rounded-md">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Subtítulo</label>
                                <input type="text" name="subtitle" value="{{ old('subtitle', $about->subtitle) }}" class="mt-1 block w-full border-gray-300 rounded-md">
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Descrição</label>
                            <textarea name="description" rows="6" class="mt-1 block w-full border-gray-300 rounded-md" placeholder="Conte a história da empresa">{{ old('description', $about->description) }}</textarea>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-start">
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Cover (cabeçalho)</label>
                                <div class="relative group cursor-pointer w-full h-40">
                                    <input type="file" name="cover" accept="image/*" class="absolute inset-0 opacity-0 cursor-pointer z-10" onchange="previewCover(event)">
                                    @if ($about->cover)
                                        <img id="preview-cover" src="{{ asset($about->cover) }}" class="w-full h-40 object-cover rounded border border-gray-200 group-hover:opacity-80 transition" alt="Cover">
                                    @else
                                        <div id="preview-cover" class="flex items-center justify-center w-full h-40 border border-dashed border-gray-300 rounded bg-gray-50 text-gray-400 text-xs text-center group-hover:bg-orange-50">
                                            <i class="fa-regular fa-image text-base mr-1"></i> Cover
                                        </div>
                                    @endif
                                </div>
                                @if ($about->cover)
                                    <p class="text-xs text-gray-500 mt-2">Atual: <span class="underline">{{ basename($about->cover) }}</span></p>
                                @endif
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Imagem</label>
                                <div class="relative group cursor-pointer w-full h-40">
                                    <input type="file" name="photo" accept="image/*" class="absolute inset-0 opacity-0 cursor-pointer z-10" onchange="previewAboutImage(event)">
                                    @if ($about->photo)
                                        <img id="preview-about" src="{{ asset($about->photo) }}" class="w-full h-40 object-cover rounded border border-gray-200 group-hover:opacity-80 transition" alt="Foto">
                                    @else
                                        <div id="preview-about" class="flex items-center justify-center w-full h-40 border border-dashed border-gray-300 rounded bg-gray-50 text-gray-400 text-xs text-center group-hover:bg-orange-50">
                                            <i class="fa-regular fa-image text-base mr-1"></i> Imagem
                                        </div>
                                    @endif
                                </div>
                                @if ($about->photo)
                                    <p class="text-xs text-gray-500 mt-2">Atual: <span class="underline">{{ basename($about->photo) }}</span></p>
                                @endif
                            </div>
                        </div>

                        <div class="flex justify-end">
                            <button type="submit" class="inline-flex items-center px-6 py-3 bg-orange-600 text-white rounded hover:bg-orange-700">Salvar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        function previewAboutImage(evt){
            const file = evt.target.files?.[0];
            if(!file) return;
            const url = URL.createObjectURL(file);
            const el = document.getElementById('preview-about');
            el.outerHTML = `<img id=\"preview-about\" src=\"${url}\" class=\"w-full h-40 object-cover rounded border border-gray-200\" />`;
        }
        function previewCover(evt){
            const file = evt.target.files?.[0];
            if(!file) return;
            const url = URL.createObjectURL(file);
            const el = document.getElementById('preview-cover');
            el.outerHTML = `<img id=\"preview-cover\" src=\"${url}\" class=\"w-full h-40 object-cover rounded border border-gray-200\" />`;
        }
    </script>
</x-app-layout>
