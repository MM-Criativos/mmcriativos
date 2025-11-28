<style>
    .navbar-header {
        margin-top: -20px !important;
    }
</style>

@php
    $title = 'Habilidades';
    $subTitle = 'Crie uma nova habilidade';
@endphp

@extends('layouts.app')

@section('content')
    <div>
        <div class="grid grid-cols-1">
            <div class="bg-white dark:bg-black overflow-hidden shadow-sm sm:rounded-lg">
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
                                    class="mt-1 block w-full bg-[#f5f5f5] dark:!bg-dark-800 border-gray-300 rounded-md"
                                    required>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Slug</label>
                                <input type="text" name="slug" value="{{ old('slug') }}"
                                    class="mt-1 block w-full bg-[#f5f5f5] dark:!bg-dark-800 border-gray-300 rounded-md"
                                    placeholder="opcional">
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Ícone (classe FA)</label>
                                <input type="text" name="icon" value="{{ old('icon') }}"
                                    class="mt-1 block w-full bg-[#f5f5f5] dark:!bg-dark-800 border-gray-300 rounded-md"
                                    placeholder="ex: fa-light fa-code">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Descrição</label>
                                <textarea name="description" rows="3"
                                    class="mt-1 block w-full bg-[#f5f5f5] dark:!bg-dark-800 border-gray-300 rounded-md">{{ old('description') }}</textarea>
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
                                        class="flex bg-[#f5f5f5] dark:!bg-dark-800 items-center justify-center w-40 h-40 border border-dashed border-gray-300 rounded text-gray-400 text-xs text-center group-hover:bg-orange-50">
                                        <i class="fa-regular fa-image text-base mr-1"></i> Thumb
                                    </div>
                                </div>
                            </div>

                            {{-- Cover (imagem ou vídeo) --}}
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Cover</label>

                                <div class="relative group cursor-pointer w-40 h-40">
                                    <input type="file" name="cover" accept="image/*,video/*"
                                        class="absolute inset-0 opacity-0 cursor-pointer z-10"
                                        onchange="previewCover(event)">

                                    <div id="preview-cover"
                                        class="flex bg-[#f5f5f5] dark:!bg-dark-800 items-center justify-center w-40 h-40 border border-dashed border-gray-300 rounded text-gray-400 text-xs text-center group-hover:bg-orange-50">
                                        <i class="fa-regular fa-file-video text-base mr-1"></i> Cover
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

                        <script>
                            function previewCover(event) {
                                const [file] = event.target.files || [];
                                if (!file) return;
                                const url = URL.createObjectURL(file);
                                const isVideo = /^video\//.test(file.type);
                                const el = document.getElementById('preview-cover');
                                if (isVideo) {
                                    el.outerHTML =
                                        `<video id=\"preview-cover\" src=\"${url}\" class=\"w-40 h-40 object-cover rounded border border-gray-200\" controls muted></video>`;
                                } else {
                                    el.outerHTML =
                                        `<img id=\"preview-cover\" src=\"${url}\" class=\"w-40 h-40 object-cover rounded border border-gray-200\" />`;
                                }
                            }
                        </script>

                        <div class="flex justify-center">
                            <button type="submit"
                                class="inline-flex items-center px-6 py-4 btn-mmcriativos rounded-md">Criar
                                Skill</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
