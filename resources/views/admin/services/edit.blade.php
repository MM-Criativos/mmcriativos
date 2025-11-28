@php
    $title = 'Serviços';
    $subTitle = 'Edite os detalhes do serviço';
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
                    <form method="POST" action="{{ route('admin.services.update', $service) }}" enctype="multipart/form-data"
                        class="space-y-6">
                        @csrf
                        @method('PUT')

                        <h3 class="text-lg font-semibold mb-2">Informações Gerais</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Nome</label>
                                <input type="text" name="name" value="{{ old('name', $service->name) }}"
                                    class="mt-1 block w-full bg-[#f5f5f5] dark:!bg-dark-800 border-gray-300 rounded-md"
                                    required>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Slug</label>
                                <input type="text" name="slug" value="{{ old('slug', $service->slug) }}"
                                    class="mt-1 block w-full bg-[#f5f5f5] dark:!bg-dark-800 border-gray-300 rounded-md"
                                    required>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Ícone (classe FA)</label>
                                <input type="text" name="icon" value="{{ old('icon', $service->icon) }}"
                                    class="mt-1 block w-full bg-[#f5f5f5] dark:!bg-dark-800 border-gray-300 rounded-md">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Descrição</label>
                                <textarea name="description" rows="3"
                                    class="mt-1 block w-full bg-[#f5f5f5] dark:!bg-dark-800 border-gray-300 rounded-md">{{ old('description', $service->description) }}</textarea>
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

                                    @if ($service->thumb)
                                        <img id="preview-thumb" src="{{ asset($service->thumb) }}" alt="Thumb"
                                            class="w-40 h-40 object-cover rounded border border-gray-200 group-hover:opacity-80 transition">
                                    @else
                                        <div id="preview-thumb"
                                            class="flex items-center justify-center w-40 h-40 border border-dashed border-gray-300 rounded bg-gray-50 text-gray-400 text-xs text-center group-hover:bg-orange-50">
                                            <i class="fa-regular fa-image text-base mr-1"></i> Thumb
                                        </div>
                                    @endif
                                </div>
                            </div>

                            {{-- Cover (vídeo) --}}
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Cover</label>
                                <div class="relative group cursor-pointer w-40 h-40">
                                    <input type="file" name="cover" accept="video/*"
                                        class="absolute inset-0 opacity-0 cursor-pointer z-10"
                                        onchange="previewCover(event)">

                                    @php
                                        $cover = $service->cover;
                                        $isVideo =
                                            $cover &&
                                            \Illuminate\Support\Str::endsWith(\Illuminate\Support\Str::lower($cover), [
                                                '.mp4',
                                                '.webm',
                                                '.ogg',
                                                '.mov',
                                            ]);
                                    @endphp
                                    @if ($service->cover)
                                        @if ($isVideo)
                                            <video id="preview-cover" src="{{ asset($service->cover) }}"
                                                class="w-40 h-40 object-cover rounded border border-gray-200 group-hover:opacity-80 transition"
                                                controls muted></video>
                                        @else
                                            <img id="preview-cover" src="{{ asset($service->cover) }}" alt="Cover"
                                                class="w-40 h-40 object-cover rounded border border-gray-200 group-hover:opacity-80 transition">
                                        @endif
                                    @else
                                        <div id="preview-cover"
                                            class="flex items-center justify-center w-40 h-40 border border-dashed border-gray-300 rounded bg-gray-50 text-gray-400 text-xs text-center group-hover:bg-orange-50">
                                            <i class="fa-regular fa-file-video text-base mr-1"></i> Vídeo
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
                                const el = document.getElementById('preview-cover');
                                el.outerHTML =
                                    `
                    <video id="preview-cover" src="${url}" class="w-40 h-40 object-cover rounded border border-gray-200" controls muted></video>`;
                            }
                        </script>

                        <script>
                            async function bulkUpdateProcesses(url) {
                                try {
                                    const root = document.getElementById('processesDnd');
                                    if (!root) return;
                                    const fd = new FormData();
                                    fd.append('_token', '{{ csrf_token() }}');
                                    root.querySelectorAll('.dnd-item').forEach((wrap) => {
                                        const id = wrap.getAttribute('data-id');
                                        const title = wrap.querySelector('input[name="title"]')?.value || '';
                                        const order = wrap.querySelector('input[name="order"]')?.value || '';
                                        const desc = wrap.querySelector('textarea[name="description"]')?.value || '';
                                        const file = wrap.querySelector('input[type="file"][name="image"]')?.files?.[0];
                                        fd.append(`processes[${id}][title]`, title);
                                        fd.append(`processes[${id}][order]`, order);
                                        fd.append(`processes[${id}][description]`, desc);
                                        if (file) fd.append(`processes[${id}][image]`, file);
                                    });
                                    const btn = document.getElementById('bulkUpdateProcessesBtn');
                                    if (btn) {
                                        btn.disabled = true;
                                        btn.classList.add('opacity-60');
                                    }
                                    const res = await fetch(url, {
                                        method: 'POST',
                                        body: fd
                                    });
                                    if (!res.ok) throw new Error('bulk update failed');
                                    location.reload();
                                } catch (e) {
                                    alert('Não foi possível atualizar os processos.');
                                    console.warn(e);
                                } finally {
                                    const btn = document.getElementById('bulkUpdateProcessesBtn');
                                    if (btn) {
                                        btn.disabled = false;
                                        btn.classList.remove('opacity-60');
                                    }
                                }
                            }
                        </script>

                        <div>
                            <div class="flex justify-center mt-10">
                                <button type="submit"
                                    class="inline-flex items-center px-6 py-4 btn-mmcriativos rounded-md mb-5">
                                    Salvar Alterações
                                </button>
                            </div>
                        </div>
                    </form>

                    <hr class="my-8">

                    <h3 class="text-lg font-semibold mb-2">Informações do Serviço</h3>
                    <form method="POST" action="{{ route('admin.services.info.update', $service) }}" class="space-y-4">
                        @csrf
                        @method('PUT')
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Subtítulo</label>
                                <input type="text" name="subtitle"
                                    value="{{ old('subtitle', optional($service->info)->subtitle) }}"
                                    class="mt-1 block w-full bg-[#f5f5f5] dark:!bg-dark-800 border-gray-300 rounded-md">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Título</label>
                                <input type="text" name="title"
                                    value="{{ old('title', optional($service->info)->title) }}"
                                    class="mt-1 block w-full bg-[#f5f5f5] dark:!bg-dark-800 border-gray-300 rounded-md"
                                    required>
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Descrição</label>
                            <textarea name="description" rows="4"
                                class="mt-1 block w-full bg-[#f5f5f5] dark:!bg-dark-800 border-gray-300 rounded-md">{{ old('description', optional($service->info)->description) }}</textarea>
                        </div>
                        <div>
                            <div class="flex justify-center mt-10"><button
                                    class="inline-flex items-center px-6 py-4 btn-mmcriativos rounded-md">Salvar
                                    Informações</button></div>
                        </div>
                    </form>

                    <hr class="my-8">
                    <h3 class="text-lg font-semibold mb-2">Benefícios</h3>
                    <div id="benefitsDnd" class="space-y-3">
                        @foreach ($service->benefits as $benefit)
                            <div class="dnd-item" draggable="true" data-id="{{ $benefit->id }}">
                                <form method="POST" action="{{ route('admin.benefits.update', $benefit) }}"
                                    class="grid grid-cols-12 gap-3 items-end bg-[#f5f5f5] dark:bg-dark-800 p-4 rounded shadow-sm border border-gray-100">
                                    @csrf @method('PUT')

                                    <div class="col-span-3">
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Título</label>
                                        <input type="text" name="title" value="{{ $benefit->title }}"
                                            class="w-full bg-white dark:!bg-black border-gray-300 rounded-md text-sm focus:ring-orange-500 focus:border-orange-500"
                                            placeholder="Título">
                                    </div>

                                    <div class="col-span-6">
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Descrição</label>
                                        <input type="text" name="subtitle" value="{{ $benefit->subtitle }}"
                                            class="w-full bg-white dark:!bg-black border-gray-300 rounded-md text-sm focus:ring-orange-500 focus:border-orange-500"
                                            placeholder="Descrição">
                                    </div>

                                    <div class="col-span-1">
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Ordem</label>
                                        <input type="number" name="order" value="{{ $benefit->order }}"
                                            class="w-full bg-white dark:!bg-black border-gray-300 rounded-md text-sm focus:ring-orange-500 focus:border-orange-500"
                                            placeholder="#">
                                    </div>

                                    <div class="col-span-2 flex gap-2 justify-end">
                                        <button
                                            class="flex-1 inline-flex items-center justify-center px-4 py-4 btn-mmcriativos rounded-md">
                                            <i class="fa-duotone fa-solid fa-rotate-right icon-project"></i>
                                            <span></span>
                                        </button>
                                        <form method="POST" action="{{ route('admin.benefits.destroy', $benefit) }}"
                                            onsubmit="return confirm('Remover benefício?');" class="inline-block">
                                            @csrf @method('DELETE')
                                            <button
                                                class="flex-1 inline-flex items-center justify-center px-4 py-4 rounded bg-red-500 text-white border-red-500 hover:bg-white dark:hover:bg-black hover:text-red-500 hover:border-red-500">
                                                <i class="fa-regular fa-trash-can"></i>
                                                <span></span>
                                            </button>
                                        </form>
                                    </div>
                                </form>
                            </div>
                        @endforeach

                        <h3 class="text-md font-semibold mb-2">Adicionar novo benefício</h3>

                        <form method="POST" action="{{ route('admin.services.benefits.store', $service) }}"
                            class="bg-white p-4 rounded shadow-sm border border-gray-100 mt-4">
                            @csrf
                            <h4 class="font-medium text-gray-800 mb-3">Adicionar novo benefício</h4>

                            <div class="grid grid-cols-12 gap-3 items-end">
                                <div class="col-span-5">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Título</label>
                                    <input type="text" name="title"
                                        class="w-full bg-white dark:!bg-black border-gray-300 rounded-md text-sm focus:ring-orange-500 focus:border-orange-500"
                                        placeholder="Novo Título" required>
                                </div>

                                <div class="col-span-6">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Descrição</label>
                                    <input type="text" name="subtitle"
                                        class="w-full bg-white dark:!bg-black border-gray-300 rounded-md text-sm focus:ring-orange-500 focus:border-orange-500"
                                        placeholder="Novo Subtítulo">
                                </div>

                                <div class="col-span-1 flex items-end">
                                    <button type="submit"
                                        class="flex-1 inline-flex items-center justify-center px-4 py-4 btn-mmcriativos rounded-md">
                                        <i class="fa-duotone fa-solid fa-plus-circle icon-project"></i>
                                        <span></span>
                                    </button>
                                </div>
                            </div>
                        </form>

                    </div>

                    <hr class="my-8">
                    <h3 class="text-lg font-semibold mb-2">Características (máx. 5)</h3>
                    <div id="featuresDnd" class="space-y-3">
                        @foreach ($service->features as $feature)
                            <div class="dnd-item" draggable="true" data-id="{{ $feature->id }}">
                                <form method="POST" action="{{ route('admin.features.update', $feature) }}"
                                    class="grid grid-cols-12 gap-3 items-end bg-white p-4 rounded shadow-sm border border-gray-100">
                                    @csrf @method('PUT')

                                    <div class="col-span-3">
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Título</label>
                                        <input type="text" name="title" value="{{ $feature->title }}"
                                            class="w-full bg-white dark:!bg-black border-gray-300 rounded-md text-sm focus:ring-orange-500 focus:border-orange-500"
                                            placeholder="Título">
                                    </div>

                                    <div class="col-span-6">
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Descrição</label>
                                        <input type="text" name="subtitle" value="{{ $feature->subtitle }}"
                                            class="w-full bg-white dark:!bg-black border-gray-300 rounded-md text-sm focus:ring-orange-500 focus:border-orange-500"
                                            placeholder="Descrição">
                                    </div>

                                    <div class="col-span-1">
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Ordem</label>
                                        <input type="number" name="order" value="{{ $feature->order }}"
                                            class="w-full bg-white dark:!bg-black border-gray-300 rounded-md text-sm focus:ring-orange-500 focus:border-orange-500"
                                            placeholder="#">
                                    </div>

                                    <div class="col-span-2 flex gap-2 justify-end">
                                        <button
                                            class="flex-1 inline-flex items-center justify-center px-4 py-4 btn-mmcriativos rounded-md">
                                            <i class="fa-duotone fa-solid fa-rotate-right icon-project"></i>
                                            <span></span>
                                        </button>

                                        <form method="POST" action="{{ route('admin.features.destroy', $feature) }}"
                                            onsubmit="return confirm('Remover característica?');" class="inline-block">
                                            @csrf @method('DELETE')
                                            <button
                                                class="flex-1 inline-flex items-center justify-center px-4 py-4 rounded bg-red-500 text-white border-red-500 hover:bg-white dark:hover:bg-black hover:text-red-500 hover:border-red-500">
                                                <i class="fa-regular fa-trash-can"></i>
                                                <span></span>
                                            </button>
                                        </form>
                                    </div>
                                </form>
                            </div>
                        @endforeach

                        {{-- Form para adicionar nova característica --}}
                        @if ($service->features->count() < 5)
                            <form method="POST" action="{{ route('admin.services.features.store', $service) }}"
                                class="bg-white p-4 rounded shadow-sm border border-gray-100 mt-4">
                                @csrf
                                <h4 class="font-medium text-gray-800 mb-3">Adicionar nova característica</h4>

                                <div class="grid grid-cols-12 gap-3 items-end">
                                    <div class="col-span-5">
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Título</label>
                                        <input type="text" name="title"
                                            class="w-full bg-white dark:!bg-black border-gray-300 rounded-md text-sm focus:ring-orange-500 focus:border-orange-500"
                                            placeholder="Novo Título" required>
                                    </div>

                                    <div class="col-span-6">
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Descrição</label>
                                        <input type="text" name="subtitle"
                                            class="w-full bg-white dark:!bg-black border-gray-300 rounded-md text-sm focus:ring-orange-500 focus:border-orange-500"
                                            placeholder="Novo Subtítulo">
                                    </div>

                                    <div class="col-span-1 flex items-end">
                                        <button type="submit"
                                            class="w-full flex items-center justify-center gap-2 px-3 py-2 btn-mmcriativos rounded-md">
                                            <i class="fa-solid fa-plus"></i>
                                            <span>Adicionar</span>
                                        </button>
                                    </div>
                                </div>
                            </form>
                        @endif
                    </div>

                    <hr class="my-8">
                    <h3 class="text-lg font-semibold mb-2">Processos (mín. 3)</h3>
                    <div id="processesDnd" class="space-y-3">
                        @foreach ($service->processes as $process)
                            <div class="dnd-item" draggable="true" data-id="{{ $process->id }}">
                                <form method="POST" action="{{ route('admin.processes.update', $process) }}"
                                    enctype="multipart/form-data"
                                    class="bg-white p-4 rounded shadow-sm border border-gray-300">
                                    @csrf @method('PUT')

                                    <div class="grid grid-cols-12 gap-4 items-start">
                                        {{-- Coluna 1: Imagem --}}
                                        <div class="col-span-3">
                                            <label class="block text-sm font-medium text-gray-700 mb-1">Imagem</label>
                                            <div class="relative group cursor-pointer w-[188px] h-[186px]">
                                                <input type="file" name="image" accept="image/*"
                                                    class="absolute inset-0 opacity-0 cursor-pointer z-10"
                                                    onchange="previewImage(event, '{{ $process->id }}')">

                                                @if ($process->image)
                                                    <img id="preview-{{ $process->id }}"
                                                        src="{{ asset($process->image) }}" alt="Imagem"
                                                        class="w-[188px] h-[186px] object-cover rounded border border-gray-200 group-hover:opacity-80 transition">
                                                @else
                                                    <div id="preview-{{ $process->id }}"
                                                        class="flex items-center justify-center w-[188px] h-[186px] border border-dashed border-gray-300 rounded bg-gray-50 text-gray-400 text-sm group-hover:bg-orange-50">
                                                        <i class="fa-regular fa-image text-lg mr-2"></i> Selecionar
                                                    </div>
                                                @endif
                                            </div>
                                        </div>

                                        {{-- Coluna 2: Título, Ordem e Descrição --}}
                                        <div class="col-span-8">
                                            {{-- Linha 1: Título e Ordem --}}
                                            <div class="grid grid-cols-12 gap-3 mb-3">
                                                <div class="col-span-10">
                                                    <label
                                                        class="block text-sm font-medium text-gray-700 mb-1">Título</label>
                                                    <input type="text" name="title" value="{{ $process->title }}"
                                                        class="w-full bg-white dark:!bg-black border-gray-300 rounded-md text-sm focus:ring-orange-500 focus:border-orange-500"
                                                        placeholder="Título" required>
                                                </div>
                                                <div class="col-span-2">
                                                    <label
                                                        class="block text-sm font-medium text-gray-700 mb-1">Ordem</label>
                                                    <input type="number" name="order" value="{{ $process->order }}"
                                                        class="w-full bg-white dark:!bg-black border-gray-300 rounded-md text-sm focus:ring-orange-500 focus:border-orange-500"
                                                        placeholder="#">
                                                </div>
                                            </div>

                                            {{-- Linha 2: Descrição --}}
                                            <div>
                                                <label
                                                    class="block text-sm font-medium text-gray-700 mb-1">Descrição</label>
                                                <textarea name="description" rows="3"
                                                    class="w-full bg-white dark:!bg-black border-gray-300 rounded-md text-sm focus:ring-orange-500 focus:border-orange-500 resize-none"
                                                    placeholder="Descrição detalhada do processo">{{ $process->description }}</textarea>
                                            </div>
                                        </div>

                                        {{-- Coluna 3: Botões --}}
                                        <div class="col-span-1 flex flex-col gap-2 justify-start items-end">


                                            <button type="submit" form="delete-process-{{ $process->id }}"
                                                formnovalidate
                                                class="inline-flex items-center justify-center px-3 py-2 bg-red-500 text-white border-red-500 hover:bg-white dark:hover:bg-black hover:text-red-500 hover:border-red-500 rounded"
                                                title="Apagar" onclick="return confirm('Remover processo?');">
                                                <i class="fa-regular fa-trash-can"></i>
                                            </button>
                                        </div>
                                    </div>
                                </form>
                                <!-- Hidden delete form (outside to avoid nested forms) -->
                                <form id="delete-process-{{ $process->id }}" method="POST"
                                    action="{{ route('admin.processes.destroy', $process) }}" style="display:none">
                                    @csrf
                                    @method('DELETE')
                                </form>
                            </div>
                        @endforeach


                        <div class="flex items-center justify-end mb-2">
                            <button type="button" id="bulkUpdateProcessesBtn"
                                class="inline-flex items-center gap-2 px-4 py-2 btn-mmcriativos rounded-md"
                                onclick="bulkUpdateProcesses('{{ route('admin.services.processes.bulk', $service) }}')">
                                <i class="fa-duotone fa-solid fa-rotate-right icon-project"></i>
                                <span>Atualizar todos</span>
                            </button>
                        </div>

                        <h3 class="text-md font-semibold mb-2">Adicionar novo processo</h3>

                        {{-- Novo processo --}}
                        <form method="POST" action="{{ route('admin.services.processes.store', $service) }}"
                            enctype="multipart/form-data" class="bg-white p-4 rounded shadow-sm border border-gray-300">
                            @csrf

                            <div class="grid grid-cols-12 gap-4 items-start">
                                {{-- Coluna 1: Imagem --}}
                                <div class="col-span-3">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Imagem</label>
                                    <div class="relative group cursor-pointer w-[188px] h-[186px]">
                                        <input type="file" name="image" accept="image/*"
                                            class="absolute inset-0 opacity-0 cursor-pointer z-10"
                                            onchange="previewImage(event, 'new')">

                                        <div id="preview-new"
                                            class="flex items-center justify-center w-[188px] h-[186px] border border-dashed border-gray-300 rounded bg-gray-50 text-gray-400 text-sm group-hover:bg-orange-50">
                                            <i class="fa-regular fa-image text-lg mr-2"></i> Selecionar
                                        </div>
                                    </div>
                                </div>

                                {{-- Coluna 2: Campos --}}
                                <div class="col-span-8">
                                    <div class="grid grid-cols-12 gap-3 mb-3">
                                        <div class="col-span-10">
                                            <label class="block text-sm font-medium text-gray-700 mb-1">Título</label>
                                            <input type="text" name="title"
                                                class="w-full bg-white dark:!bg-black border-gray-300 rounded-md text-sm focus:ring-orange-500 focus:border-orange-500"
                                                placeholder="Novo Título" required>
                                        </div>
                                        <div class="col-span-2">
                                            <label class="block text-sm font-medium text-gray-700 mb-1">Ordem</label>
                                            <input type="number" name="order"
                                                class="w-full bg-white dark:!bg-black border-gray-300 rounded-md text-sm focus:ring-orange-500 focus:border-orange-500"
                                                placeholder="#">
                                        </div>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Descrição</label>
                                        <textarea name="description" rows="3"
                                            class="w-full bg-white dark:!bg-black border-gray-300 rounded-md text-sm focus:ring-orange-500 focus:border-orange-500 resize-none"
                                            placeholder="Nova descrição"></textarea>
                                    </div>
                                </div>

                                {{-- Coluna 3: Botão Adicionar --}}
                                <div class="col-span-1 flex flex-col gap-2 justify-start items-end">
                                    <button type="submit"
                                        class="inline-flex items-center justify-center px-3 py-2 btn-mmcriativos rounded-md"
                                        title="Adicionar">
                                        <i class="fa-duotone fa-solid fa-plus-circle icon-project"></i>
                                    </button>
                                </div>
                            </div>
                        </form>
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
                                        `<img id="preview-${id}" src="${reader.result}" class="w-[188px] h-[186px] object-cover rounded border border-gray-200" />`;
                                }
                            };
                            reader.readAsDataURL(event.target.files[0]);
                        }
                    </script>

                    <hr class="my-8">
                    <h3 class="text-lg font-semibold mb-2">CTAs</h3>
                    <div class="space-y-3">
                        @foreach ($service->ctas as $cta)
                            <div class="dnd-item" draggable="true" data-id="{{ $cta->id }}">
                                <form method="POST" action="{{ route('admin.ctas.update', $cta) }}"
                                    enctype="multipart/form-data"
                                    class="bg-white p-4 rounded shadow-sm border border-gray-300">
                                    @csrf @method('PUT')

                                    <div class="grid grid-cols-12 gap-4 items-start">
                                        {{-- Coluna 1: Imagem --}}
                                        <div class="col-span-3">
                                            <label class="block text-sm font-medium text-gray-700 mb-1">Imagem</label>
                                            <div class="relative group cursor-pointer w-[188px] h-[186px]">
                                                <input type="file" name="image" accept="image/*"
                                                    class="absolute inset-0 opacity-0 cursor-pointer z-10"
                                                    onchange="previewImage(event, 'cta-{{ $cta->id }}')">

                                                @if ($cta->image)
                                                    <img id="preview-cta-{{ $cta->id }}"
                                                        src="{{ asset($cta->image) }}" alt="Imagem"
                                                        class="w-[188px] h-[186px] object-cover rounded border border-gray-200 group-hover:opacity-80 transition">
                                                @else
                                                    <div id="preview-cta-{{ $cta->id }}"
                                                        class="flex items-center justify-center w-[188px] h-[186px] border border-dashed border-gray-300 rounded bg-gray-50 text-gray-400 text-sm group-hover:bg-orange-50">
                                                        <i class="fa-regular fa-image text-lg mr-2"></i> Selecionar
                                                    </div>
                                                @endif
                                            </div>
                                        </div>

                                        {{-- Coluna 2: Título e Telefone --}}
                                        <div class="col-span-8">
                                            <div class="grid grid-cols-12 gap-3">
                                                <div class="col-span-7">
                                                    <label
                                                        class="block text-sm font-medium text-gray-700 mb-1">Título</label>
                                                    <input type="text" name="title" value="{{ $cta->title }}"
                                                        class="w-full bg-white dark:!bg-black border-gray-300 rounded-md text-sm focus:ring-orange-500 focus:border-orange-500"
                                                        placeholder="Título" required>
                                                </div>

                                                <div class="col-span-5">
                                                    <label
                                                        class="block text-sm font-medium text-gray-700 mb-1">Telefone</label>
                                                    <input type="text" name="phone" value="{{ $cta->phone }}"
                                                        class="w-full bg-white dark:!bg-black border-gray-300 rounded-md text-sm focus:ring-orange-500 focus:border-orange-500"
                                                        placeholder="(00) 00000-0000">
                                                </div>
                                            </div>
                                        </div>

                                        {{-- Coluna 3: Botões --}}
                                        <div class="col-span-1 flex flex-col gap-2 justify-start items-end">
                                            <button type="submit"
                                                class="inline-flex items-center justify-center px-3 py-2 btn-mmcriativos rounded-md"
                                                title="Atualizar">
                                                <i class="fa-duotone fa-solid fa-rotate-right icon-project"></i>
                                            </button>

                                            <form method="POST" action="{{ route('admin.ctas.destroy', $cta) }}"
                                                onsubmit="return confirm('Remover CTA?');">
                                                @csrf @method('DELETE')
                                                <button
                                                    class="inline-flex items-center justify-center px-3.5 py-2 bg-red-500 text-white border-red-500 hover:bg-white dark:hover:bg-black hover:text-red-500 hover:border-red-500 rounded"
                                                    title="Apagar">
                                                    <i class="fa-regular fa-trash-can"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        @endforeach

                        {{-- Adicionar novo CTA --}}
                        <h3 class="text-md font-semibold mb-2">Adicionar novo CTA</h3>

                        <form method="POST" action="{{ route('admin.services.ctas.store', $service) }}"
                            enctype="multipart/form-data" class="bg-white p-4 rounded shadow-sm border border-gray-300">
                            @csrf

                            <div class="grid grid-cols-12 gap-4 items-start">
                                {{-- Coluna 1: Imagem --}}
                                <div class="col-span-3">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Imagem</label>
                                    <div class="relative group cursor-pointer w-[188px] h-[186px]">
                                        <input type="file" name="image" accept="image/*"
                                            class="absolute inset-0 opacity-0 cursor-pointer z-10"
                                            onchange="previewImage(event, 'cta-new')">

                                        <div id="preview-cta-new"
                                            class="flex items-center justify-center w-[188px] h-[186px] border border-dashed border-gray-300 rounded bg-gray-50 text-gray-400 text-sm group-hover:bg-orange-50">
                                            <i class="fa-regular fa-image text-lg mr-2"></i> Selecionar
                                        </div>
                                    </div>
                                </div>

                                {{-- Coluna 2: Campos --}}
                                <div class="col-span-8 grid grid-cols-12 gap-3">
                                    <div class="col-span-7">
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Título</label>
                                        <input type="text" name="title"
                                            class="w-full bg-white dark:!bg-black border-gray-300 rounded-md text-sm focus:ring-orange-500 focus:border-orange-500"
                                            placeholder="Novo Título" required>
                                    </div>

                                    <div class="col-span-5">
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Telefone</label>
                                        <input type="text" name="phone"
                                            class="w-full bg-white dark:!bg-black border-gray-300 rounded-md text-sm focus:ring-orange-500 focus:border-orange-500"
                                            placeholder="(00) 00000-0000">
                                    </div>
                                </div>

                                {{-- Coluna 3: Botão Adicionar --}}
                                <div class="col-span-1 flex flex-col gap-2 justify-start items-end">
                                    <button type="submit"
                                        class="inline-flex items-center justify-center px-3 py-2 btn-mmcriativos rounded-md"
                                        title="Adicionar">
                                        <i class="fa-duotone fa-solid fa-plus-circle icon-project"></i>
                                    </button>
                                </div>
                            </div>
                        </form>
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
                                        `<img id="preview-${id}" src="${reader.result}" class="w-[188px] h-[186px] object-cover rounded border border-gray-200" />`;
                                }
                            };
                            reader.readAsDataURL(event.target.files[0]);
                        }
                    </script>

                </div>
            </div>
        </div>
    </div>

    <script>
        function previewImg(e, sel) {
            const img = document.querySelector(sel);
            const [file] = e.target.files || [];
            if (file) {
                img.src = URL.createObjectURL(file);
                img.classList.remove('hidden');
            }
        }
    </script>
@endsection

<script>
    (function() {
        function enableDnd(containerId, url) {
            const cont = document.getElementById(containerId);
            if (!cont) return;
            let dragEl = null;
            cont.querySelectorAll('.dnd-item').forEach(it => {
                it.addEventListener('dragstart', (e) => {
                    dragEl = it;
                    it.classList.add('opacity-50');
                });
                it.addEventListener('dragend', () => {
                    if (dragEl) {
                        dragEl.classList.remove('opacity-50');
                        dragEl = null;
                    }
                });
                it.addEventListener('dragover', (e) => {
                    e.preventDefault();
                    const t = e.currentTarget;
                    if (dragEl && t !== dragEl) {
                        const rect = t.getBoundingClientRect();
                        const before = (e.clientY - rect.top) < rect.height / 2;
                        cont.insertBefore(dragEl, before ? t : t.nextSibling);
                    }
                });
            });
            async function sync() {
                const order = Array.from(cont.querySelectorAll('.dnd-item')).map(el => parseInt(el.dataset.id));
                try {
                    await fetch(url, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            order
                        })
                    });
                } catch (e) {
                    console.warn('reorder failed', e);
                }
            }
            cont.addEventListener('drop', sync);
        }
    })();
</script>
