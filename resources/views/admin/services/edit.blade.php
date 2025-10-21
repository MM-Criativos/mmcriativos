<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Editar Serviço</h2>
            <a href="{{ route('admin.services.index') }}" class="text-gray-600 hover:underline">Voltar</a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="mb-4 p-3 bg-green-100 text-green-800 rounded">{{ session('status') }}</div>
            @endif
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <form method="POST" action="{{ route('admin.services.update', $service) }}"
                        enctype="multipart/form-data" class="space-y-6">
                        @csrf
                        @method('PUT')

                        <h3 class="text-lg font-semibold mb-2">Informações Gerais</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Nome</label>
                                <input type="text" name="name" value="{{ old('name', $service->name) }}"
                                    class="mt-1 block w-full border-gray-300 rounded-md" required>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Slug</label>
                                <input type="text" name="slug" value="{{ old('slug', $service->slug) }}"
                                    class="mt-1 block w-full border-gray-300 rounded-md" required>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Ícone (classe FA)</label>
                                <input type="text" name="icon" value="{{ old('icon', $service->icon) }}"
                                    class="mt-1 block w-full border-gray-300 rounded-md">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Descrição</label>
                                <textarea name="description" rows="3" class="mt-1 block w-full border-gray-300 rounded-md">{{ old('description', $service->description) }}</textarea>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Thumb</label>
                                <input type="file" name="thumb" accept="image/*"
                                    class="mt-1 block w-full border-gray-300 rounded-md"
                                    onchange="previewImg(event, '#thumbPreview')">
                                @if ($service->thumb)
                                    <img id="thumbPreview" src="{{ asset($service->thumb) }}"
                                        class="mt-2 h-32 rounded object-cover" alt="thumb">
                                @else
                                    <img id="thumbPreview" class="mt-2 h-32 rounded object-cover hidden" alt="thumb">
                                @endif
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Cover</label>
                                <input type="file" name="cover" accept="image/*"
                                    class="mt-1 block w-full border-gray-300 rounded-md"
                                    onchange="previewImg(event, '#coverPreview')">
                                @if ($service->cover)
                                    <img id="coverPreview" src="{{ asset($service->cover) }}"
                                        class="mt-2 h-32 rounded object-cover" alt="cover">
                                @else
                                    <img id="coverPreview" class="mt-2 h-32 rounded object-cover hidden" alt="cover">
                                @endif
                            </div>
                        </div>

                        <div>
                            <div class="flex justify-center mt-10">
                                <button type="submit"
                                    class="inline-flex items-center px-6 py-4 bg-orange-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-orange-700">
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
                                    class="mt-1 block w-full border-gray-300 rounded-md">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Título</label>
                                <input type="text" name="title"
                                    value="{{ old('title', optional($service->info)->title) }}"
                                    class="mt-1 block w-full border-gray-300 rounded-md" required>
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Descrição</label>
                            <textarea name="description" rows="4" class="mt-1 block w-full border-gray-300 rounded-md">{{ old('description', optional($service->info)->description) }}</textarea>
                        </div>
                        <div>
                            <div class="flex justify-center mt-10"><button
                                    class="inline-flex items-center px-6 py-4 bg-orange-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-orange-700">Salvar
                                    Informações</button></div>
                        </div>
                    </form>

                    <hr class="my-8">
                    <h3 class="text-lg font-semibold mb-2">Benefícios</h3>
                    <div id="benefitsDnd" class="space-y-3">
                        @foreach ($service->benefits as $benefit)
                            <div class="dnd-item" draggable="true" data-id="{{ $benefit->id }}">
                                <form method="POST" action="{{ route('admin.benefits.update', $benefit) }}"
                                    class="grid grid-cols-12 gap-3 items-end bg-white p-4 rounded shadow-sm border border-gray-100">
                                    @csrf @method('PUT')

                                    <div class="col-span-3">
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Título</label>
                                        <input type="text" name="title" value="{{ $benefit->title }}"
                                            class="w-full border-gray-300 rounded-md text-sm focus:ring-orange-500 focus:border-orange-500"
                                            placeholder="Título">
                                    </div>

                                    <div class="col-span-6">
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Descrição</label>
                                        <input type="text" name="subtitle" value="{{ $benefit->subtitle }}"
                                            class="w-full border-gray-300 rounded-md text-sm focus:ring-orange-500 focus:border-orange-500"
                                            placeholder="Descrição">
                                    </div>

                                    <div class="col-span-1">
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Ordem</label>
                                        <input type="number" name="order" value="{{ $benefit->order }}"
                                            class="w-full border-gray-300 rounded-md text-sm focus:ring-orange-500 focus:border-orange-500"
                                            placeholder="#">
                                    </div>

                                    <div class="col-span-2 flex gap-2 justify-end">
                                        <button
                                            class="flex-1 inline-flex items-center justify-center px-4 py-4 bg-orange-600 text-white rounded border border-transparent hover:bg-white hover:text-orange-600 hover:border-orange-600 hover:border-solid text-sm transition-colors duration-200">
                                            <i class="fa-solid fa-rotate-right"></i>
                                            <span></span>
                                        </button>
                                        <form method="POST" action="{{ route('admin.benefits.destroy', $benefit) }}"
                                            onsubmit="return confirm('Remover benefício?');" class="inline-block">
                                            @csrf @method('DELETE')
                                            <button
                                                class="flex-1 inline-flex items-center justify-center px-4 py-4 bg-red-600 text-white rounded hover:bg-red-700 transition">
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
                                        class="w-full border-gray-300 rounded-md text-sm focus:ring-orange-500 focus:border-orange-500"
                                        placeholder="Novo Título" required>
                                </div>

                                <div class="col-span-6">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Descrição</label>
                                    <input type="text" name="subtitle"
                                        class="w-full border-gray-300 rounded-md text-sm focus:ring-orange-500 focus:border-orange-500"
                                        placeholder="Novo Subtítulo">
                                </div>

                                <div class="col-span-1 flex items-end">
                                    <button type="submit"
                                        class="flex-1 inline-flex items-center justify-center px-4 py-4 bg-orange-600 text-white rounded border border-transparent hover:bg-white hover:text-orange-600 hover:border-orange-600 hover:border-solid text-sm transition-colors duration-200">
                                        <i class="fa-solid fa-plus"></i>
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
                                            class="w-full border-gray-300 rounded-md text-sm focus:ring-orange-500 focus:border-orange-500"
                                            placeholder="Título">
                                    </div>

                                    <div class="col-span-6">
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Descrição</label>
                                        <input type="text" name="subtitle" value="{{ $feature->subtitle }}"
                                            class="w-full border-gray-300 rounded-md text-sm focus:ring-orange-500 focus:border-orange-500"
                                            placeholder="Descrição">
                                    </div>

                                    <div class="col-span-1">
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Ordem</label>
                                        <input type="number" name="order" value="{{ $feature->order }}"
                                            class="w-full border-gray-300 rounded-md text-sm focus:ring-orange-500 focus:border-orange-500"
                                            placeholder="#">
                                    </div>

                                    <div class="col-span-2 flex gap-2 justify-end">
                                        <button
                                            class="flex-1 inline-flex items-center justify-center px-4 py-4 bg-orange-600 text-white rounded border border-transparent hover:bg-white hover:text-orange-600 hover:border-orange-600 hover:border-solid text-sm transition-colors duration-200">
                                            <i class="fa-solid fa-rotate-right"></i>
                                            <span></span>
                                        </button>

                                        <form method="POST" action="{{ route('admin.features.destroy', $feature) }}"
                                            onsubmit="return confirm('Remover característica?');"
                                            class="inline-block">
                                            @csrf @method('DELETE')
                                            <button
                                                class="flex-1 inline-flex items-center justify-center px-4 py-4 bg-red-600 text-white rounded hover:bg-red-700 transition">
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
                                            class="w-full border-gray-300 rounded-md text-sm focus:ring-orange-500 focus:border-orange-500"
                                            placeholder="Novo Título" required>
                                    </div>

                                    <div class="col-span-6">
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Descrição</label>
                                        <input type="text" name="subtitle"
                                            class="w-full border-gray-300 rounded-md text-sm focus:ring-orange-500 focus:border-orange-500"
                                            placeholder="Novo Subtítulo">
                                    </div>

                                    <div class="col-span-1 flex items-end">
                                        <button type="submit"
                                            class="w-full flex items-center justify-center gap-2 px-3 py-2 bg-orange-600 text-white rounded hover:bg-orange-700 transition">
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
                                                    <input type="text" name="title"
                                                        value="{{ $process->title }}"
                                                        class="w-full border-gray-300 rounded-md text-sm focus:ring-orange-500 focus:border-orange-500"
                                                        placeholder="Título" required>
                                                </div>
                                                <div class="col-span-2">
                                                    <label
                                                        class="block text-sm font-medium text-gray-700 mb-1">Ordem</label>
                                                    <input type="number" name="order"
                                                        value="{{ $process->order }}"
                                                        class="w-full border-gray-300 rounded-md text-sm focus:ring-orange-500 focus:border-orange-500"
                                                        placeholder="#">
                                                </div>
                                            </div>

                                            {{-- Linha 2: Descrição --}}
                                            <div>
                                                <label
                                                    class="block text-sm font-medium text-gray-700 mb-1">Descrição</label>
                                                <textarea name="description" rows="3"
                                                    class="w-full border-gray-300 rounded-md text-sm focus:ring-orange-500 focus:border-orange-500 resize-none"
                                                    placeholder="Descrição detalhada do processo">{{ $process->description }}</textarea>
                                            </div>
                                        </div>

                                        {{-- Coluna 3: Botões --}}
                                        <div class="col-span-1 flex flex-col gap-2 justify-start items-end">
                                            <button type="submit"
                                                class="inline-flex items-center justify-center p-2 bg-orange-600 text-white rounded border border-transparent hover:bg-white hover:text-orange-600 hover:border-orange-600 hover:border-solid text-sm transition-colors duration-200"
                                                title="Atualizar">
                                                <i class="fa-solid fa-rotate-right"></i>
                                            </button>

                                            <form method="POST"
                                                action="{{ route('admin.processes.destroy', $process) }}"
                                                onsubmit="return confirm('Remover processo?');">
                                                @csrf @method('DELETE')
                                                <button
                                                    class="inline-flex items-center justify-center p-2 bg-red-600 text-white rounded hover:bg-red-700 transition"
                                                    title="Apagar">
                                                    <i class="fa-regular fa-trash-can"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        @endforeach

                        <h3 class="text-md font-semibold mb-2">Adicionar novo processo</h3>

                        {{-- Novo processo --}}
                        <form method="POST" action="{{ route('admin.services.processes.store', $service) }}"
                            enctype="multipart/form-data"
                            class="bg-white p-4 rounded shadow-sm border border-gray-300">
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
                                                class="w-full border-gray-300 rounded-md text-sm focus:ring-orange-500 focus:border-orange-500"
                                                placeholder="Novo Título" required>
                                        </div>
                                        <div class="col-span-2">
                                            <label class="block text-sm font-medium text-gray-700 mb-1">Ordem</label>
                                            <input type="number" name="order"
                                                class="w-full border-gray-300 rounded-md text-sm focus:ring-orange-500 focus:border-orange-500"
                                                placeholder="#">
                                        </div>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Descrição</label>
                                        <textarea name="description" rows="3"
                                            class="w-full border-gray-300 rounded-md text-sm focus:ring-orange-500 focus:border-orange-500 resize-none"
                                            placeholder="Nova descrição"></textarea>
                                    </div>
                                </div>

                                {{-- Coluna 3: Botão Adicionar --}}
                                <div class="col-span-1 flex flex-col gap-2 justify-start items-end">
                                    <button type="submit"
                                        class="inline-flex items-center justify-center p-2 bg-orange-600 text-white rounded border border-transparent hover:bg-white hover:text-orange-600 hover:border-orange-600 hover:border-solid text-sm transition-colors duration-200"
                                        title="Adicionar">
                                        <i class="fa-solid fa-plus"></i>
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
                                                        class="w-full border-gray-300 rounded-md text-sm focus:ring-orange-500 focus:border-orange-500"
                                                        placeholder="Título" required>
                                                </div>

                                                <div class="col-span-5">
                                                    <label
                                                        class="block text-sm font-medium text-gray-700 mb-1">Telefone</label>
                                                    <input type="text" name="phone" value="{{ $cta->phone }}"
                                                        class="w-full border-gray-300 rounded-md text-sm focus:ring-orange-500 focus:border-orange-500"
                                                        placeholder="(00) 00000-0000">
                                                </div>
                                            </div>
                                        </div>

                                        {{-- Coluna 3: Botões --}}
                                        <div class="col-span-1 flex flex-col gap-2 justify-start items-end">
                                            <button type="submit"
                                                class="inline-flex items-center justify-center p-2 bg-orange-600 text-white rounded border border-transparent hover:bg-white hover:text-orange-600 hover:border-orange-600 hover:border-solid text-sm transition-colors duration-200"
                                                title="Atualizar">
                                                <i class="fa-solid fa-rotate-right"></i>
                                            </button>

                                            <form method="POST" action="{{ route('admin.ctas.destroy', $cta) }}"
                                                onsubmit="return confirm('Remover CTA?');">
                                                @csrf @method('DELETE')
                                                <button
                                                    class="inline-flex items-center justify-center p-2 bg-red-600 text-white rounded hover:bg-red-700 transition"
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
                            enctype="multipart/form-data"
                            class="bg-white p-4 rounded shadow-sm border border-gray-300">
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
                                            class="w-full border-gray-300 rounded-md text-sm focus:ring-orange-500 focus:border-orange-500"
                                            placeholder="Novo Título" required>
                                    </div>

                                    <div class="col-span-5">
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Telefone</label>
                                        <input type="text" name="phone"
                                            class="w-full border-gray-300 rounded-md text-sm focus:ring-orange-500 focus:border-orange-500"
                                            placeholder="(00) 00000-0000">
                                    </div>
                                </div>

                                {{-- Coluna 3: Botão Adicionar --}}
                                <div class="col-span-1 flex flex-col gap-2 justify-start items-end">
                                    <button type="submit"
                                        class="inline-flex items-center justify-center p-2 bg-orange-600 text-white rounded border border-transparent hover:bg-white hover:text-orange-600 hover:border-orange-600 hover:border-solid text-sm transition-colors duration-200"
                                        title="Adicionar">
                                        <i class="fa-solid fa-plus"></i>
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
</x-app-layout>

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
