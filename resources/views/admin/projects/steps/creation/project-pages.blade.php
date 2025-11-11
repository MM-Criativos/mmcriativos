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
                    class="inline-flex items-center gap-2 px-4 py-2 bg-orange-600 text-white rounded text-sm hover:bg-white hover:text-orange-600 hover:border hover:border-orange-600">
                    <i class="fa-solid fa-rotate-right"></i> Atualizar todas
                </button>

                {{-- ✅ Adicionar páginas --}}
                <button type="button" @click="pageModal = true"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-gray-800 text-white rounded text-sm hover:bg-gray-900">
                    <i class="fa-solid fa-plus"></i> Adicionar Páginas
                </button>
            </div>
        </div>

        {{-- ✅ Form principal de páginas --}}
        <form id="form-update-pages" method="POST" action="{{ route('admin.projects.pages.updateAll', $project) }}">
            @csrf
            @method('PUT')

            <div class="grid gap-4">
                @forelse ($project->pages as $page)
                    <div
                        class="border rounded-lg p-4 bg-white hover:shadow transition flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 flex-1">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Título</label>
                                <input type="text" name="pages[{{ $page->id }}][name]"
                                    value="{{ $page->name }}" class="w-full border-gray-300 rounded-md text-sm">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Ordem</label>
                                <input type="number" name="pages[{{ $page->id }}][order]"
                                    value="{{ $page->order }}" class="w-full border-gray-300 rounded-md text-sm"
                                    min="0">
                            </div>

                            <div class="flex items-center gap-2 mt-6 md:mt-0">
                                <input type="hidden" name="pages[{{ $page->id }}][is_active]" value="0">
                                <input type="checkbox" name="pages[{{ $page->id }}][is_active]" value="1"
                                    @checked($page->is_active)
                                    class="rounded border-gray-300 text-orange-600 focus:ring-orange-500">
                                <span class="text-sm text-gray-700">Ativa</span>
                            </div>
                        </div>

                        {{-- Botão de exclusão --}}
                        <button type="button"
                            class="inline-flex items-center justify-center px-3 py-2 bg-red-600 text-white rounded text-xs hover:bg-red-700"
                            onclick="deletePage('{{ route('admin.project-pages.destroy', $page) }}')">
                            <i class="fa-regular fa-trash-can mr-1"></i> Remover
                        </button>
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
            <div class="border rounded-lg overflow-hidden bg-white" x-data="{ open: false }">
                {{-- Cabeçalho do accordion --}}
                <div class="flex items-center justify-between px-4 py-3 bg-gray-50 border-b cursor-pointer"
                    @click="open = !open">
                    <div class="flex items-center gap-2">
                        <i class="fa-solid" :class="{ 'fa-chevron-down': !open, 'fa-chevron-up': open }"></i>
                        <span class="font-medium text-gray-800">{{ $page->name }}</span>
                    </div>
                    <span class="text-xs text-gray-500">Clique para expandir</span>
                </div>

                {{-- Conteúdo do accordion --}}
                <div x-show="open" x-transition class="p-4 space-y-4">
                    <div class="flex items-center justify-between mb-3">
                        <h4 class="text-sm font-semibold text-gray-700 uppercase tracking-wide">Componentes</h4>
                        <div class="flex gap-2">
                            {{-- ✅ Botão de atualizar componentes --}}
                            <button type="submit" form="form-update-components-{{ $page->id }}"
                                class="inline-flex items-center gap-2 px-3 py-2 bg-orange-600 text-white rounded text-xs hover:bg-white hover:text-orange-600 hover:border hover:border-orange-600">
                                <i class="fa-solid fa-rotate-right"></i> Atualizar todos
                            </button>

                            {{-- ✅ Botão de adicionar componentes --}}
                            <button type="button" @click="componentModal = {{ $page->id }}"
                                class="inline-flex items-center gap-2 px-3 py-2 bg-white text-orange-600 rounded border border-orange-200 text-sm hover:bg-orange-50">
                                <i class="fa-solid fa-plus"></i> Adicionar
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
                                class="border rounded-md p-3 flex flex-col md:flex-row md:items-center md:justify-between gap-3">
                                <div>
                                    <div class="text-sm font-medium text-gray-800">{{ $component->name }}</div>
                                    <div class="text-xs text-gray-500">
                                        Camada: {{ ucfirst($component->layer ?? 'desconhecida') }}
                                    </div>
                                </div>

                                <div class="flex items-center gap-5">
                                    <div>
                                        <label class="block text-xs font-medium text-gray-500 mb-1">Ordem</label>
                                        <input type="number" name="components[{{ $component->pivot->id }}][order]"
                                            value="{{ $component->pivot->order }}"
                                            class="w-20 border-gray-300 rounded-md text-sm" min="0">
                                    </div>

                                    <div class="flex items-center gap-2 mt-4">
                                        <input type="hidden"
                                            name="components[{{ $component->pivot->id }}][is_visible]" value="0">
                                        <input type="checkbox"
                                            name="components[{{ $component->pivot->id }}][is_visible]" value="1"
                                            @checked($component->pivot->is_visible)
                                            class="rounded border-gray-300 text-orange-600 focus:ring-orange-500">
                                        <span class="text-xs text-gray-600">Visível</span>
                                    </div>

                                    {{-- Botão de exclusão --}}
                                    <button type="button"
                                        class="inline-flex items-center justify-center px-3 py-2 bg-red-600 text-white rounded text-xs hover:bg-red-700 mt-4"
                                        onclick="deleteComponent('{{ route('admin.project-page-components.destroy', $component->pivot->id) }}')">
                                        <i class="fa-regular fa-trash-can"></i>
                                    </button>
                                </div>
                            </div>
                        @empty
                            <div class="border rounded-md p-4 text-sm text-gray-500 bg-gray-50">
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
            <div class="bg-white rounded-lg shadow-lg w-full max-w-3xl p-6 max-h-[80vh] overflow-y-auto">
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
                        <div class="border rounded-lg">
                            <button type="button"
                                class="w-full px-4 py-3 text-left bg-gray-100 font-medium text-gray-700"
                                @click="$el.nextElementSibling.classList.toggle('hidden')">
                                {{ ucfirst($layer) }}
                            </button>
                            <div class="p-4 space-y-3">
                                @foreach ($components as $component)
                                    @php $checked = in_array($component->id, $componentIds, true); @endphp
                                    <label class="flex items-start gap-3">
                                        <input type="checkbox" name="components[]" value="{{ $component->id }}"
                                            @checked($checked) @disabled($checked)
                                            class="mt-1 rounded border-gray-300 text-orange-600 focus:ring-orange-500">
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

                    <div class="flex items-center justify-end gap-3">
                        <button type="button" @click="componentModal = null"
                            class="px-4 py-2 text-sm rounded border border-gray-300 text-gray-600 hover:bg-gray-50">
                            Cancelar
                        </button>
                        <button
                            class="px-4 py-2 text-sm bg-orange-600 text-white rounded border border-transparent hover:bg-orange-700">
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
        <div class="bg-white rounded-lg shadow-lg w-full max-w-3xl p-6 max-h-[80vh] overflow-y-auto">
            <div class="flex items-center justify-between mb-4">
                <h4 class="text-lg font-semibold text-gray-800">Adicionar Páginas ao Projeto</h4>
                <button type="button" @click="pageModal = false" class="text-gray-500 hover:text-gray-700">
                    <i class="fa-solid fa-xmark text-xl"></i>
                </button>
            </div>

            <form method="POST" action="{{ route('admin.projects.pages.store', $project) }}" class="space-y-5">
                @csrf
                @foreach ($pagesGrouped as $layer => $pages)
                    <div class="border rounded-lg">
                        <button type="button"
                            class="w-full px-4 py-3 text-left bg-gray-100 font-medium text-gray-700"
                            @click="$el.nextElementSibling.classList.toggle('hidden')">
                            {{ ucfirst($layer) }}
                        </button>
                        <div class="p-4 space-y-3">
                            @foreach ($pages as $p)
                                @php $checked = in_array($p->id, $existingGlobalPageIds, true); @endphp
                                <label class="flex items-start gap-3">
                                    <input type="checkbox" name="pages[]" value="{{ $p->id }}"
                                        @checked($checked) @disabled($checked)
                                        class="mt-1 rounded border-gray-300 text-orange-600 focus:ring-orange-500">
                                    <span>
                                        <span class="text-sm font-medium text-gray-800">{{ $p->name }}</span>
                                        <span class="block text-xs text-gray-500">{{ $p->description }}</span>
                                    </span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                @endforeach

                <div class="flex items-center justify-end gap-3">
                    <button type="button" @click="pageModal = false"
                        class="px-4 py-2 text-sm rounded border border-gray-300 text-gray-600 hover:bg-gray-50">
                        Cancelar
                    </button>
                    <button
                        class="px-4 py-2 text-sm bg-orange-600 text-white rounded border border-transparent hover:bg-orange-700">
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
