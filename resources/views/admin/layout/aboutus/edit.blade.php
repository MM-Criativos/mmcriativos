@php
    $title = 'Layout';
    $subTitle = 'Ajuste aspectos do site';
@endphp

@extends('layouts.app')

@section('content')
    <div>
        <div class="grid grid-cols-1">
            @if (session('status'))
                <div class="mb-4 p-3 bg-green-100 text-green-800 rounded">{{ session('status') }}</div>
            @endif
            <div class="bg-white dark:bg-black overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <form method="POST" action="{{ route('admin.layout.aboutus.update') }}" enctype="multipart/form-data"
                        class="space-y-6">
                        @csrf
                        @method('PUT')

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Título</label>
                                <input type="text" name="title" value="{{ old('title', $about->title) }}"
                                    class="mt-1 block w-full bg-[#f5f5f5] dark:!bg-dark-800 border-gray-300 rounded-md">
                            </div>
                            <div>
                                <label
                                    class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Subtítulo</label>
                                <input type="text" name="subtitle" value="{{ old('subtitle', $about->subtitle) }}"
                                    class="mt-1 block w-full bg-[#f5f5f5] dark:!bg-dark-800 border-gray-300 rounded-md">
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Descrição</label>
                            <textarea name="description" rows="6"
                                class="mt-1 block w-full bg-[#f5f5f5] dark:bg-dark-800 border-gray-300 rounded-md"
                                placeholder="Conte a história da empresa">{{ old('description', $about->description) }}</textarea>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-start">
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Cover
                                    (cabeçalho)</label>
                                <div class="relative group cursor-pointer w-full bg-[#f5f5f5] dark:bg-dark-800 h-40">
                                    <input type="file" name="cover" accept="image/*"
                                        class="absolute inset-0 opacity-0 cursor-pointer z-10"
                                        onchange="previewCover(event)">
                                    @if ($about->cover)
                                        <img id="preview-cover" src="{{ asset($about->cover) }}"
                                            class="w-full bg-[#f5f5f5] dark:bg-dark-800 h-40 object-cover rounded border border-gray-200 group-hover:opacity-80 transition"
                                            alt="Cover">
                                    @else
                                        <div id="preview-cover"
                                            class="flex items-center justify-center w-full bg-[#f5f5f5] dark:bg-dark-800 h-40 border border-dashed border-gray-300 rounded bg-gray-50 text-gray-400 text-xs text-center group-hover:bg-orange-50">
                                            <i class="fa-regular fa-image text-base mr-1"></i> Cover
                                        </div>
                                    @endif
                                </div>
                                @if ($about->cover)
                                    <p class="text-xs text-gray-500 mt-2">Atual: <span
                                            class="underline">{{ basename($about->cover) }}</span></p>
                                @endif
                            </div>

                            <div>
                                <label
                                    class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Imagem</label>
                                <div class="relative group cursor-pointer w-full bg-[#f5f5f5] dark:bg-dark-800 h-40">
                                    <input type="file" name="photo" accept="image/*"
                                        class="absolute inset-0 opacity-0 cursor-pointer z-10"
                                        onchange="previewAboutImage(event)">
                                    @if ($about->photo)
                                        <img id="preview-about" src="{{ asset($about->photo) }}"
                                            class="w-full bg-[#f5f5f5] dark:bg-dark-800 h-40 object-cover rounded border border-gray-200 group-hover:opacity-80 transition"
                                            alt="Foto">
                                    @else
                                        <div id="preview-about"
                                            class="flex items-center justify-center w-full
                                            bg-[#f5f5f5] dark:bg-dark-800
                                            min-h-[200px] max-h-[200px] h-[200px]
                                            border border-dashed border-gray-300
                                            rounded text-gray-400 text-xs text-center
                                             group-hover:bg-orange-50">
                                            <i class="fa-regular fa-image text-base mr-1"></i> Imagem
                                        </div>
                                    @endif
                                </div>
                                @if ($about->photo)
                                    <p class="text-xs text-gray-500 mt-2">Atual: <span
                                            class="underline">{{ basename($about->photo) }}</span></p>
                                @endif
                            </div>
                        </div>

                        <div class="flex justify-center">
                            <button type="submit"
                                class="inline-flex items-center px-6 py-3 btn-mmcriativos">Salvar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        function previewAboutImage(evt) {
            const file = evt.target.files?.[0];
            if (!file) return;
            const url = URL.createObjectURL(file);
            const el = document.getElementById('preview-about');
            el.outerHTML =
                `<img id=\"preview-about\" src=\"${url}\" class=\"w-full h-40 object-cover rounded border border-gray-200\" />`;
        }

        function previewCover(evt) {
            const file = evt.target.files?.[0];
            if (!file) return;
            const url = URL.createObjectURL(file);
            const el = document.getElementById('preview-cover');
            el.outerHTML =
                `<img id=\"preview-cover\" src=\"${url}\" class=\"w-full h-40 object-cover rounded border border-gray-200\" />`;
        }
    </script>
@endsection
