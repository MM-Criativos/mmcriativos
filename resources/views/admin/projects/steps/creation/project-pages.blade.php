@php
    $pagesGrouped = $availablePages->groupBy(fn($page) => data_get($page->meta, 'primary_layer', 'Outros'));
    $componentsGrouped = $availableComponents->groupBy(fn($component) => $component->layer ?? 'outros');
    $existingGlobalPageIds = $project->pages->pluck('global_page_id')->filter()->all();
@endphp

<div class="mt-6" x-data="{ pageModal: false, componentModal: null }">
    <div class="mb-4 flex items-center justify-between">
        <h3 class="text-lg font-semibold text-gray-800">Páginas do Projeto</h3>
        <button type="button" @click="pageModal = true"
            class="inline-flex items-center gap-2 px-4 py-2 bg-orange-600 text-white rounded border border-transparent text-sm hover:bg-orange-700">
            <i class="fa-solid fa-plus"></i>
            <span>Adicionar Páginas</span>
        </button>
    </div>

    <div class="space-y-4">
        @forelse ($project->pages as $page)
            @php
                $componentIds = $page->components->pluck('id')->all();
            @endphp
            <div class="border rounded-lg bg-white overflow-hidden" x-data="{ open: true }">
                <div class="flex items-center justify-between px-4 py-3 bg-gray-50 border-b">
                    <button type="button" class="flex items-center gap-3 text-left w-full" @click="open = !open">
                        <div>
                            <div class="text-base font-medium text-gray-800">{{ $page->name }}</div>
                            <div class="text-xs text-gray-500">
                                Blueprint: {{ optional($page->globalPage)->name ?? 'Personalizada' }} · Ordem:
                                {{ $page->order }}
                            </div>
                        </div>
                    </button>
                    <div class="flex items-center gap-2">
                        <button type="button" class="text-gray-500 hover:text-gray-700" @click="open = !open">
                            <i class="fa-solid" :class="{ 'fa-chevron-down': !open, 'fa-chevron-up': open }"></i>
                        </button>
                        <form method="POST" action="{{ route('admin.project-pages.destroy', $page) }}"
                            onsubmit="return confirm('Remover esta página do projeto?');">
                            @csrf
                            @method('DELETE')
                            <button
                                class="inline-flex items-center justify-center h-9 w-9 rounded border border-transparent text-red-600 hover:bg-red-50"
                                title="Remover página">
                                <i class="fa-regular fa-trash-can"></i>
                            </button>
                        </form>
                    </div>
                </div>

                <div x-show="open" x-transition class="px-4 py-4 space-y-5">
                    <form method="POST" action="{{ route('admin.project-pages.update', $page) }}"
                        class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                        @csrf
                        @method('PUT')
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Título da página</label>
                            <input type="text" name="name" value="{{ old('name', $page->name) }}"
                                class="w-full border-gray-300 rounded-md text-sm" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Ordem</label>
                            <input type="number" name="order" value="{{ old('order', $page->order) }}"
                                class="w-full border-gray-300 rounded-md text-sm" min="0" required>
                        </div>
                        <div class="flex items-center gap-2">
                            <input type="hidden" name="is_active" value="0">
                            <input type="checkbox" name="is_active" value="1"
                                @checked(old('is_active', $page->is_active)) class="rounded border-gray-300 text-orange-600 focus:ring-orange-500">
                            <span class="text-sm text-gray-700">Página ativa</span>
                        </div>
                        <div class="md:col-span-4 flex justify-end">
                            <button
                                class="inline-flex items-center gap-2 px-4 py-2 bg-gray-800 text-white rounded border border-transparent text-sm hover:bg-gray-900">
                                <i class="fa-solid fa-rotate-right"></i>
                                <span>Salvar alterações</span>
                            </button>
                        </div>
                    </form>

                    <div class="flex items-center justify-between">
                        <h4 class="text-sm font-semibold text-gray-700 uppercase tracking-wide">Componentes</h4>
                        <button type="button" @click="componentModal = {{ $page->id }}"
                            class="inline-flex items-center gap-2 px-3 py-2 bg-white text-orange-600 rounded border border-orange-200 text-sm hover:bg-orange-50">
                            <i class="fa-solid fa-plus"></i>
                            <span>Adicionar componentes</span>
                        </button>
                    </div>

                    <div class="space-y-3">
                        @forelse ($page->components as $component)
                            <div class="border rounded-md p-3 flex flex-col md:flex-row md:items-center md:justify-between gap-3">
                                <div>
                                    <div class="text-sm font-medium text-gray-800">{{ $component->name }}</div>
                                    <div class="text-xs text-gray-500">
                                        Camada: {{ ucfirst($component->layer ?? 'desconhecida') }} · Ordem atual:
                                        {{ $component->pivot->order }}
                                    </div>
                                </div>
                                <div class="flex items-center gap-3">
                                    <form method="POST"
                                        action="{{ route('admin.project-page-components.update', $component->pivot->id) }}"
                                        class="flex items-center gap-3">
                                        @csrf
                                        @method('PUT')
                                        <div>
                                            <label class="block text-xs font-medium text-gray-500 mb-1">Ordem</label>
                                            <input type="number" name="order" value="{{ $component->pivot->order }}"
                                                class="w-20 border-gray-300 rounded-md text-sm" min="0" required>
                                        </div>
                                        <div class="flex items-center gap-2 mt-5">
                                            <input type="hidden" name="is_visible" value="0">
                                            <input type="checkbox" name="is_visible" value="1"
                                                @checked($component->pivot->is_visible)
                                                class="rounded border-gray-300 text-orange-600 focus:ring-orange-500">
                                            <span class="text-xs text-gray-600">Visível</span>
                                        </div>
                                        <button
                                            class="inline-flex items-center justify-center px-3 py-2 bg-gray-800 text-white rounded border border-transparent text-xs hover:bg-gray-900 mt-4">
                                            <i class="fa-solid fa-rotate-right"></i>
                                        </button>
                                    </form>
                                    <form method="POST"
                                        action="{{ route('admin.project-page-components.destroy', $component->pivot->id) }}"
                                        onsubmit="return confirm('Remover este componente?');">
                                        @csrf
                                        @method('DELETE')
                                        <button
                                            class="inline-flex items-center justify-center px-3 py-2 bg-red-600 text-white rounded border border-transparent text-xs hover:bg-red-700">
                                            <i class="fa-regular fa-trash-can"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @empty
                            <div class="border rounded-md p-4 text-sm text-gray-500 bg-gray-50">
                                Nenhum componente configurado nesta página.
                            </div>
                        @endforelse
                    </div>

                    <!-- Modal de componentes -->
                    <div x-show="componentModal === {{ $page->id }}" x-cloak
                        class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50"
                        @click.self="componentModal = null">
                        <div class="bg-white rounded-lg shadow-lg w-full max-w-3xl p-6 max-h-[80vh] overflow-y-auto">
                            <div class="flex items-center justify-between mb-4">
                                <h4 class="text-lg font-semibold text-gray-800">Adicionar componentes · {{ $page->name }}
                                </h4>
                                <button type="button" @click="componentModal = null" class="text-gray-500 hover:text-gray-700">
                                    <i class="fa-solid fa-xmark text-xl"></i>
                                </button>
                            </div>

                            <form method="POST"
                                action="{{ route('admin.project-pages.components.store', $page) }}"
                                class="space-y-5">
                                @csrf
                                @foreach ($componentsGrouped as $layer => $components)
                                    <div class="border rounded-lg">
                                        <button type="button" class="w-full px-4 py-3 text-left bg-gray-100 font-medium text-gray-700"
                                            @click="$el.nextElementSibling.classList.toggle('hidden')">
                                            {{ ucfirst($layer) }}
                                        </button>
                                        <div class="p-4 space-y-3">
                                            @foreach ($components as $component)
                                                @php
                                                    $checked = in_array($component->id, $componentIds, true);
                                                @endphp
                                                <label class="flex items-start gap-3">
                                                    <input type="checkbox" name="components[]" value="{{ $component->id }}"
                                                        @checked($checked)
                                                        @disabled($checked)
                                                        class="mt-1 rounded border-gray-300 text-orange-600 focus:ring-orange-500">
                                                    <span>
                                                        <span class="text-sm font-medium text-gray-800">{{ $component->name }}</span>
                                                        <span class="block text-xs text-gray-500">{{ $component->description }}</span>
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
                </div>
            </div>
        @empty
            <div class="bg-white border border-dashed border-gray-300 rounded-lg p-6 text-sm text-gray-600">
                Nenhuma página configurada ainda. Clique em <strong>Adicionar Páginas</strong> para começar.
            </div>
        @endforelse
    </div>

    <!-- Modal de páginas -->
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
                @foreach ($pagesGrouped as $category => $pages)
                    <div class="border rounded-lg">
                        <button type="button" class="w-full px-4 py-3 text-left bg-gray-100 font-medium text-gray-700"
                            @click="$el.nextElementSibling.classList.toggle('hidden')">
                            {{ ucfirst(str_replace('_', ' ', $category)) }}
                        </button>
                        <div class="p-4 space-y-3">
                            @foreach ($pages as $globalPage)
                                @php
                                    $alreadyLinked = in_array($globalPage->id, $existingGlobalPageIds, true);
                                    $isSelected = in_array($globalPage->id, old('pages', []), true);
                                @endphp
                                <label class="flex items-start gap-3">
                                    <input type="checkbox" name="pages[]" value="{{ $globalPage->id }}"
                                        @checked(!empty(old('pages')) ? $isSelected : $alreadyLinked)
                                        @disabled($alreadyLinked)
                                        class="mt-1 rounded border-gray-300 text-orange-600 focus:ring-orange-500">
                                    <span>
                                        <span class="text-sm font-medium text-gray-800">{{ $globalPage->name }}</span>
                                        @if ($globalPage->description)
                                            <span class="block text-xs text-gray-500">{{ $globalPage->description }}</span>
                                        @endif
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
</div>
