@php
    $pagesGrouped = $availablePages->groupBy(fn($page) => data_get($page->meta, 'primary_layer', 'Outros'));
    $componentsGrouped = $availableComponents->groupBy(fn($component) => $component->layer ?? 'outros');
    $existingGlobalPageIds = $project->pages->pluck('global_page_id')->filter()->all();
@endphp

<div class="mt-6" x-data="{ pageModal: false, componentModal: null }">

    {{-- ========================================================= --}}
    {{-- 🔸 SEÇÃO 1 — CRUD DE PÁGINAS --}}
    {{-- ========================================================= --}}
    <div class="mb-10">
        <div class="mb-4 flex items-center justify-between">
            <h3 class="text-lg font-semibold text-gray-800">Páginas do Projeto</h3>

            <div class="flex gap-2">
                {{-- ✅ Atualizar todas as páginas --}}
                <button type="submit" form="form-update-pages"
                    class="btn-mmcriativos inline-flex items-center gap-2 px-4 py-2 rounded-md">
                    <i class="fa-duotone fa-solid fa-arrow-rotate-right icon-project mr-2"></i> Atualizar todas
                </button>

                {{-- ✅ Adicionar páginas --}}
                <button type="button" @click="pageModal = true"
                    class="btn-mmcriativos inline-flex items-center gap-2 px-4 py-2 rounded-md">
                    <i class="fa-duotone fa-solid fa-circle-plus icon-project mr-2"></i> Adicionar Páginas
                </button>
            </div>
        </div>

        {{-- ✅ Form principal de páginas --}}
        <form id="form-update-pages" method="POST" action="{{ route('admin.projects.pages.updateAll', $project) }}">
            @csrf
            @method('PUT')

            <div class="grid gap-4">
                @forelse ($project->pages as $page)
                    <div class="rounded-lg p-4 bg-[#f5f5f5] dark:bg-[#262626] hover:shadow transition">
                        <div class="grid grid-cols-12 gap-4 items-end">

                            {{-- Título (3 colunas) --}}
                            <div class="col-span-12 md:col-span-3">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Título</label>
                                <input type="text" name="pages[{{ $page->id }}][name]"
                                    value="{{ $page->name }}"
                                    class="w-full bg-white dark:!bg-black rounded-md text-sm">
                            </div>

                            {{-- Descrição (6 colunas) --}}
                            <div class="col-span-12 md:col-span-7">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Descrição</label>
                                <input type="text" name="pages[{{ $page->id }}][description]"
                                    value="{{ $page->description ?? optional($page->globalPage)->description }}"
                                    class="w-full bg-white dark:!bg-black rounded-md text-sm" readonly>
                            </div>

                            {{-- Ordem (2 colunas) --}}
                            <div class="col-span-12 md:col-span-1">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Ordem</label>
                                <input type="number" name="pages[{{ $page->id }}][order]"
                                    value="{{ $page->order }}"
                                    class="w-full bg-white dark:!bg-black rounded-md text-sm" min="0">
                            </div>

                            {{-- Excluir (1 coluna) --}}
                            <div class="col-span-12 md:col-span-1 flex items-end">
                                <button type="button"
                                    class="inline-flex items-center justify-center w-full px-4 py-3 bg-red-500 text-white border-red-500 hover:bg-[#000] hover:text-red-500 rounded-md"
                                    onclick="deletePage('{{ route('admin.project-pages.destroy', $page) }}')">
                                    <i class="fa-regular fa-trash-can"></i>
                                </button>
                            </div>

                        </div>
                    </div>
                @empty
                    <div class="border border-dashed border-gray-300 rounded-lg p-6 text-sm text-gray-500 text-center">
                        Nenhuma página cadastrada. Clique em <strong>Adicionar Páginas</strong> para começar.
                    </div>
                @endforelse
            </div>
        </form>
    </div>


    {{-- ========================================================= --}}
    {{-- 🔸 SEÇÃO 2 — CRUD DE COMPONENTES --}}
    {{-- ========================================================= --}}
    <div class="space-y-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-3 mt-3">Componentes por Página</h3>

        @foreach ($project->pages as $page)
            <div class="rounded-lg overflow-hidden bg-white hover:text-[#ff8800]" x-data="{ open: false }">
                {{-- Cabeçalho do accordion --}}
                <div class="flex items-center justify-between px-4 py-3 bg-[#f5f5f5] dark:!bg-[#262626]" cursor-pointer"
                    @click="open = !open">
                    <div class="flex items-center gap-2">
                        <span class="font-medium  text-gray-800">{{ $page->name }}</span>
                    </div>
                    <i class="fa-solid" :class="{ 'fa-chevron-down': !open, 'fa-chevron-up': open }"></i>
                </div>

                {{-- Conteúdo do accordion --}}
                <div x-show="open" x-transition class="p-4 space-y-4 bg-[#f5f5f5] dark:bg-[#262626] ">
                    <div class="flex items-center justify-between mb-3">
                        <h4 class="text-sm font-semibold text-gray-700 uppercase tracking-wide">
                            Componentes</h4>
                        <div class="flex gap-3">
                            {{-- ✅ Botão de atualizar componentes --}}
                            <button type="submit" form="form-update-components-{{ $page->id }}"
                                class="btn-mmcriativos inline-flex items-center gap-2 px-3 py-2 rounded">
                                <i class="fa-duotone fa-solid fa-arrow-rotate-right icon-project mr-2"></i>
                                Atualizar todos
                            </button>

                            {{-- ✅ Botão de adicionar componentes --}}
                            <button type="button" @click="componentModal = {{ $page->id }}"
                                class="btn-mmcriativos inline-flex items-center gap-2 px-3 py-2 rounded">
                                <i class="fa-duotone fa-solid fa-circle-plus icon-project mr-2"></i>
                                Adicionar
                            </button>
                        </div>
                    </div>

                    {{-- ✅ Form independente de componentes --}}
                    <form id="form-update-components-{{ $page->id }}" method="POST"
                        action="{{ route('admin.project-page-components.updateAll', $page) }}">
                        @csrf
                        @method('PUT')

                        @forelse ($page->components as $component)
                            <div
                                class="bg-white dark:bg-black rounded-md p-3 flex flex-col md:flex-row md:items-center md:justify-between gap-3 mb-2">
                                <div>
                                    <div class="text-sm font-medium text-gray-800">{{ $component->name }}</div>
                                    <div class="text-xs text-gray-500">
                                        Camada: {{ ucfirst($component->layer ?? 'desconhecida') }}
                                    </div>
                                </div>

                                <div class="flex items-center gap-5">
                                    <div>
                                        <label class="block text-xs font-medium  text-gray-800 mb-1">Ordem</label>
                                        <input type="number" name="components[{{ $component->pivot->id }}][order]"
                                            value="{{ $component->pivot->order }}"
                                            class="w-20 bg-[#f5f5f5] dark:!bg-[#262626] rounded-md text-sm"
                                            min="0">
                                    </div>

                                    {{-- Botão de exclusão --}}
                                    <button type="button"
                                        class="inline-flex items-center justify-center w-full px-4 py-3 bg-red-500 text-white border-red-500 hover:bg-white dark:hover:bg-[#262626] hover:text-red-500 rounded-md mt-4"
                                        onclick="deleteComponent('{{ route('admin.project-page-components.destroy', $component->pivot->id) }}')">
                                        <i class="fa-regular fa-trash-can"></i>
                                    </button>
                                </div>
                            </div>
                        @empty
                            <div class="rounded-md p-4 text-sm text-gray-500 bg-white dark:bg-black">
                                Nenhum componente configurado nesta página.
                            </div>
                        @endforelse
                    </form>
                </div>
            </div>
        @endforeach
    </div>


    {{-- ========================================================= --}}
    {{-- 🔸 MODAIS --}}
    {{-- ========================================================= --}}
    {{-- Modal de adicionar componentes --}}
    @foreach ($project->pages as $page)
        @php $componentIds = $page->components->pluck('id')->all(); @endphp
        <div x-show="componentModal === {{ $page->id }}" x-cloak
            class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50"
            @click.self="componentModal = null">
            <div class="bg-white dark:bg-black rounded-lg shadow-lg w-full max-w-3xl p-6 max-h-[80vh] overflow-y-auto">
                <div class="flex items-center justify-between mb-4">
                    <h4 class="text-lg font-semibold text-gray-800">Adicionar componentes · {{ $page->name }}</h4>
                    <button type="button" @click="componentModal = null" class="text-gray-500 hover:text-gray-700">
                        <i class="fa-solid fa-xmark text-xl"></i>
                    </button>
                </div>

                <form method="POST" action="{{ route('admin.project-pages.components.store', $page) }}"
                    class="space-y-5">
                    @csrf
                    @foreach ($componentsGrouped as $layer => $components)
                        <div class="border dark:!border-[#262626] rounded-lg">
                            <button type="button"
                                class="w-full px-4 py-3 text-left bg-[#f5f5f5] dark:bg-[#262626] font-medium text-gray-700"
                                @click="$el.nextElementSibling.classList.toggle('hidden')">
                                {{ ucfirst($layer) }}
                            </button>
                            <div class="p-4 space-y-3">
                                @foreach ($components as $component)
                                    @php $checked = in_array($component->id, $componentIds, true); @endphp
                                    <label class="flex items-start gap-3">
                                        <input type="checkbox" name="components[]" value="{{ $component->id }}"
                                            @checked($checked) @disabled($checked)
                                            class="mt-1 rounded border-gray-300 text-[#ff8800] focus:ring-[#ff8800]">
                                        <span>
                                            <span
                                                class="text-sm font-medium text-gray-800">{{ $component->name }}</span>
                                            <span
                                                class="block text-xs text-gray-500">{{ $component->description }}</span>
                                        </span>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    @endforeach

                    <div class="flex items-center justify-center gap-3">
                        <button type="button" @click="componentModal = null"
                            class="px-4 py-2 text-sm border  bg-red-500 text-white border-red-500 hover:bg-white dark:hover:bg-black hover:text-red-500 rounded-md">
                            Cancelar
                        </button>
                        <button class="btn-mmcriativos px-4 py-2 text-sm rounded">
                            Adicionar componentes selecionados
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endforeach


    {{-- Modal de adicionar páginas --}}
    <div x-show="pageModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50"
        @click.self="pageModal = false">
        <div class="bg-white dark:bg-black rounded-lg shadow-lg w-full max-w-3xl p-6 max-h-[80vh] overflow-y-auto">
            <div class="flex items-center justify-between mb-4">
                <h4 class="text-lg font-semibold text-gray-800">Adicionar Páginas ao Projeto</h4>
                <button type="button" @click="pageModal = false" class="text-gray-500 hover:text-gray-700">
                    <i class="fa-solid fa-xmark text-xl"></i>
                </button>
            </div>

            <form method="POST" action="{{ route('admin.projects.pages.store', $project) }}" class="space-y-5">
                @csrf
                @foreach ($pagesGrouped as $layer => $pages)
                    <div class="border dark:!border-[#262626] rounded-lg">
                        <button type="button"
                            class="w-full px-4 py-3 text-left bg-[#f5f5f5] dark:bg-[#262626] font-medium text-gray-700"
                            @click="$el.nextElementSibling.classList.toggle('hidden')">
                            {{ ucfirst($layer) }}
                        </button>
                        <div class="p-4 space-y-3">
                            @foreach ($pages as $p)
                                @php $checked = in_array($p->id, $existingGlobalPageIds, true); @endphp
                                <label class="flex items-start gap-3">
                                    <input type="checkbox" name="pages[]" value="{{ $p->id }}"
                                        @checked($checked) @disabled($checked)
                                        class="mt-1 rounded border-gray-300 text-[#ff8800] focus:ring-[#ff8800]">
                                    <span>
                                        <span class="text-sm font-medium text-gray-800">{{ $p->name }}</span>
                                        <span class="block text-xs text-gray-500">{{ $p->description }}</span>
                                    </span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                @endforeach

                <div class="flex items-center justify-center gap-3">
                    <button type="button" @click="pageModal = false"
                        class="px-4 py-2 text-sm border  bg-red-500 text-white border-red-500 hover:bg-white dark:hover:bg-black hover:text-red-500 rounded-md">
                        Cancelar
                    </button>
                    <button class="px-4 py-2 text-sm rounded btn-mmcriativos">
                        Adicionar páginas selecionadas
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- 🔹 Form ocultos globais --}}
    <form id="delete-page-form" method="POST" style="display:none;">
        @csrf
        @method('DELETE')
    </form>

    <form id="delete-component-form" method="POST" style="display:none;">
        @csrf
        @method('DELETE')
    </form>
</div>

<script>
    function deletePage(url) {
        if (!confirm('Remover esta página do projeto?')) return;
        const form = document.getElementById('delete-page-form');
        form.action = url;
        form.submit();
    }

    function deleteComponent(url) {
        if (!confirm('Remover este componente?')) return;
        const form = document.getElementById('delete-component-form');
        form.action = url;
        form.submit();
    }
</script>
