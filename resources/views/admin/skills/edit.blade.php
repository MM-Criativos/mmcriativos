<style>
    .navbar-header {
        margin-top: -20px !important;
    }
</style>

@php
    $title = 'Habilidades';
    $subTitle = 'Edite os detalhes da habilidade';
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
                    <form method="POST" action="{{ route('admin.skills.update', $skill) }}" enctype="multipart/form-data"
                        class="space-y-6">
                        @csrf
                        @method('PUT')

                        <h3 class="text-lg font-semibold mb-2">Informações Gerais</h3>

                        {{-- Nome e Slug --}}
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Nome</label>
                                <input type="text" name="name" value="{{ old('name', $skill->name) }}"
                                    class="mt-1 block w-full bg-[#f5f5f5] dark:!bg-dark-800 border-gray-300 rounded-md"
                                    required>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Slug</label>
                                <input type="text" name="slug" value="{{ old('slug', $skill->slug) }}"
                                    class="mt-1 block w-full bg-[#f5f5f5] dark:!bg-dark-800 border-gray-300 rounded-md"
                                    required>
                            </div>
                        </div>

                        {{-- Ícone e Descrição --}}
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Ícone (classe FA)</label>
                                <input type="text" name="icon" value="{{ old('icon', $skill->icon) }}"
                                    class="mt-1 block w-full bg-[#f5f5f5] dark:!bg-dark-800 border-gray-300 rounded-md">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Descrição</label>
                                <textarea name="description" rows="3"
                                    class="mt-1 block w-full bg-[#f5f5f5] dark:!bg-dark-800 border-gray-300 rounded-md">{{ old('description', $skill->description) }}</textarea>
                            </div>
                        </div>

                        {{-- Thumb e Cover --}}
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                            {{-- Thumb --}}
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Thumb</label>

                                <div class="relative group cursor-pointer w-40 h-40">
                                    <input type="file" name="thumb" accept="image/*"
                                        class="absolute inset-0 opacity-0 cursor-pointer z-10"
                                        onchange="previewImage(event, 'thumb')">

                                    @if ($skill->thumb)
                                        <img id="preview-thumb" src="{{ asset($skill->thumb) }}" alt="Thumb"
                                            class="w-40 h-40 object-cover rounded border border-gray-200 group-hover:opacity-80 transition">
                                    @else
                                        <div id="preview-thumb"
                                            class="flex items-center justify-center w-40 h-40 border border-dashed border-gray-300 rounded bg-gray-50 text-gray-400 text-xs text-center group-hover:bg-orange-50">
                                            <i class="fa-regular fa-image text-base mr-1"></i> Thumb
                                        </div>
                                    @endif
                                </div>
                            </div>

                            {{-- Cover (imagem ou vídeo) --}}
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Cover</label>

                                <div class="relative group cursor-pointer w-40 h-40">
                                    <input type="file" name="cover" accept="image/*,video/*"
                                        class="absolute inset-0 opacity-0 cursor-pointer z-10"
                                        onchange="previewCover(event)">

                                    @php
                                        $cover = $skill->cover;
                                        $isVideo =
                                            $cover &&
                                            \Illuminate\Support\Str::endsWith(\Illuminate\Support\Str::lower($cover), [
                                                '.mp4',
                                                '.webm',
                                                '.ogg',
                                                '.mov',
                                            ]);
                                    @endphp
                                    @if ($skill->cover)
                                        @if ($isVideo)
                                            <video id="preview-cover" src="{{ asset($skill->cover) }}"
                                                class="w-40 h-40 object-cover rounded border border-gray-200 group-hover:opacity-80 transition"
                                                controls muted></video>
                                        @else
                                            <img id="preview-cover" src="{{ asset($skill->cover) }}" alt="Cover"
                                                class="w-40 h-40 object-cover rounded border border-gray-200 group-hover:opacity-80 transition">
                                        @endif
                                    @else
                                        <div id="preview-cover"
                                            class="flex items-center justify-center w-40 h-40 border border-dashed border-gray-300 rounded bg-gray-50 text-gray-400 text-xs text-center group-hover:bg-orange-50">
                                            <i class="fa-regular fa-file-video text-base mr-1"></i> Cover
                                        </div>
                                    @endif
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
                                        `<video id="preview-cover" src="${url}" class="w-40 h-40 object-cover rounded border border-gray-200" controls muted></video>`;
                                } else {
                                    el.outerHTML =
                                        `<img id="preview-cover" src="${url}" class="w-40 h-40 object-cover rounded border border-gray-200" />`;
                                }
                            }
                        </script>


                        {{-- Botão --}}
                        <div class="flex justify-center">
                            <button type="submit" class="inline-flex items-center px-6 py-4 btn-mmcriativos rounded-md">
                                Salvar Alterações
                            </button>
                        </div>
                    </form>

                    <hr class="my-8">
                    <h3 class="text-lg font-semibold mb-2">Informações da Skill</h3>
                    <form method="POST" action="{{ route('admin.skills.info.update', $skill) }}"
                        enctype="multipart/form-data" class="space-y-4">
                        @csrf
                        @method('PUT')
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Imagem</label>
                                <div class="relative group cursor-pointer w-40 h-40">
                                    <input type="file" name="image" accept="image/*"
                                        class="absolute inset-0 opacity-0 cursor-pointer z-10"
                                        onchange="previewImg(event, '#preview-skillinfo-image')">
                                    @if (optional($skill->info)->image)
                                        <img id="preview-skillinfo-image" src="{{ asset($skill->info->image) }}"
                                            class="w-40 h-40 object-cover rounded border border-gray-200 group-hover:opacity-80 transition"
                                            alt="Imagem">
                                    @else
                                        <div id="preview-skillinfo-image"
                                            class="flex items-center justify-center w-40 h-40 border border-dashed border-gray-300 rounded bg-gray-50 text-gray-400 text-xs text-center group-hover:bg-orange-50">
                                            <i class="fa-regular fa-image text-base mr-1"></i> Imagem
                                        </div>
                                    @endif
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Subtítulo</label>
                                <input type="text" name="subtitle"
                                    value="{{ old('subtitle', optional($skill->info)->subtitle) }}"
                                    class="mt-1 block w-full bg-[#f5f5f5] dark:!bg-dark-800 border-gray-300 rounded-md">
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Título</label>
                                <input type="text" name="title"
                                    value="{{ old('title', optional($skill->info)->title) }}"
                                    class="mt-1 block w-full bg-[#f5f5f5] dark:!bg-dark-800 border-gray-300 rounded-md"
                                    required>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Descrição</label>
                                <textarea name="description" rows="3"
                                    class="mt-1 block w-full bg-[#f5f5f5] dark:!bg-dark-800 border-gray-300 rounded-md">{{ old('description', optional($skill->info)->description) }}</textarea>
                            </div>
                        </div>

                        <div class="flex justify-center mt-10">
                            <button class="inline-flex items-center px-6 py-4 btn-mmcriativos rounded-md">Salvar
                                Informações</button>
                        </div>
                    </form>

                    <script>
                        function previewImg(event, selector) {
                            const reader = new FileReader();
                            reader.onload = function() {
                                const img = document.querySelector(selector);
                                img.src = reader.result;
                                img.classList.remove('hidden');
                            };
                            reader.readAsDataURL(event.target.files[0]);
                        }
                    </script>


                    <hr class="my-8">

                    <h3 class="text-lg font-semibold mb-2">Competências</h3>

                    <div id="competenciesDnd" class="space-y-3">
                        {{-- Competências existentes --}}
                        @foreach ($skill->competencies as $comp)
                            <div class="dnd-item" draggable="true" data-id="{{ $comp->id }}">
                                <form method="POST" action="{{ route('admin.competencies.update', $comp) }}"
                                    class="grid grid-cols-12 gap-3 items-end bg-white p-4 rounded shadow-sm border border-gray-100">
                                    @csrf @method('PUT')

                                    <div class="col-span-10">
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Competência</label>
                                        <input type="text" name="competency" value="{{ $comp->competency }}"
                                            class="w-full bg-white dark:!bg-black border-gray-300 rounded-md text-sm focus:ring-orange-500 focus:border-orange-500"
                                            placeholder="Competência" required>
                                    </div>

                                    <div class="col-span-2 flex gap-2 justify-end">
                                        <button
                                            class="flex-1 inline-flex items-center justify-center px-4 py-4 btn-mmcriativos rounded-md">
                                            <i class="fa-duotone fa-solid fa-rotate-right icon-project"></i>
                                        </button>

                                        <form method="POST" action="{{ route('admin.competencies.destroy', $comp) }}"
                                            onsubmit="return confirm('Remover competência?');" class="inline-block">
                                            @csrf @method('DELETE')
                                            <button
                                                class="flex-1 inline-flex items-center justify-center px-4 py-4 rounded bg-red-500 text-white border-red-500 hover:bg-white dark:hover:bg-black hover:text-red-500 hover:border-red-500">
                                                <i class="fa-regular fa-trash-can"></i>
                                            </button>
                                        </form>
                                    </div>
                                </form>
                            </div>
                        @endforeach

                        {{-- Adicionar nova competência --}}
                        <h3 class="text-md font-semibold mb-2">Adicionar nova competência</h3>

                        <form method="POST" action="{{ route('admin.skills.competencies.store', $skill) }}"
                            class="bg-white p-4 rounded shadow-sm border border-gray-100 mt-4">
                            @csrf
                            <h4 class="font-medium text-gray-800 mb-3">Adicionar nova competência</h4>

                            <div class="grid grid-cols-12 gap-3 items-end">
                                <div class="col-span-10">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Competência</label>
                                    <input type="text" name="competency"
                                        class="w-full bg-white dark:!bg-black border-gray-300 rounded-md text-sm focus:ring-orange-500 focus:border-orange-500"
                                        placeholder="Nova competência" required>
                                </div>

                                <div class="col-span-2 flex items-end">
                                    <button type="submit"
                                        class="flex-1 inline-flex items-center justify-center px-4 py-4 btn-mmcriativos rounded-md">
                                        <i class="fa-solid fa-plus"></i>
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>


                </div>
            </div>
        </div>
    </div>
@endsection
