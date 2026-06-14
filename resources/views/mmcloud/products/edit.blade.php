@extends('layouts.app')

@section('content')
@php
    $enabledKeys    = $product['feature_keys']    ?? [];
    $vocabulary     = $product['vocabulary']       ?? [];
    $customerFields = $product['customer_fields']  ?? [];
    $modalityMode   = $product['modality_mode']    ?? 'full';

    $customerFieldGroups = [
        'identification' => ['label' => 'Identificação', 'hint' => 'CPF, data de nascimento, sexo'],
        'address'        => ['label' => 'Endereço',       'hint' => 'CEP, logradouro, bairro, cidade, UF'],
        'responsible'    => ['label' => 'Responsável',    'hint' => 'Nome e telefone do responsável (menores)'],
        'insurance'      => ['label' => 'Convênio',       'hint' => 'Plano de saúde, carteirinha, validade'],
    ];
@endphp

<div class="px-4 py-8">
    <div class="max-w-4xl mx-auto">

        <div class="bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-800 rounded-2xl shadow-sm">

            {{-- Header --}}
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between px-6 py-6 border-b border-neutral-200 dark:border-neutral-800">
                <div>
                    <p class="text-sm uppercase tracking-[0.3em] text-neutral-500">MM Criativos Cloud</p>
                    <h1 class="text-2xl font-bold text-neutral-900 dark:text-white mt-1">
                        {{ $product['name'] }}
                    </h1>
                    @if(!empty($product['tagline']))
                        <p class="text-sm text-neutral-500 mt-0.5">{{ $product['tagline'] }}</p>
                    @endif
                </div>
                <a href="{{ route('mmcloud.products.index') }}"
                   class="inline-flex items-center gap-2 rounded-xl px-4 py-2.5 border border-neutral-200 dark:border-neutral-700 text-sm text-neutral-700 dark:text-neutral-300 hover:border-[#ff8800] transition-colors">
                    <i data-lucide="arrow-left" class="w-4 h-4"></i>
                    Voltar
                </a>
            </div>

            {{-- Tabs --}}
            <div class="px-6 pt-4 border-b border-neutral-200 dark:border-neutral-800 flex gap-0 overflow-x-auto" id="tabs">
                @foreach([
                    ['tab' => 'identidade', 'label' => 'Identidade'],
                    ['tab' => 'features',   'label' => 'Features'],
                    ['tab' => 'vocabulary', 'label' => 'Vocabulário'],
                    ['tab' => 'perfil',     'label' => 'Perfil do cliente'],
                    ['tab' => 'tenants',    'label' => 'Tenants', 'badge' => count($assignedTenants)],
                ] as $t)
                    <button type="button"
                            class="tab-btn whitespace-nowrap px-4 py-2.5 text-sm font-medium border-b-2 transition-colors
                                   {{ $loop->first
                                       ? 'tab-active border-[#ff8800] text-[#ff8800]'
                                       : 'border-transparent text-neutral-500 hover:text-neutral-700 dark:hover:text-neutral-300' }}"
                            data-tab="{{ $t['tab'] }}">
                        {{ $t['label'] }}
                        @isset($t['badge'])
                            <span class="ml-1.5 text-xs bg-neutral-100 dark:bg-neutral-700 text-neutral-600 dark:text-neutral-300 rounded-full px-1.5 py-0.5">{{ $t['badge'] }}</span>
                        @endisset
                    </button>
                @endforeach
            </div>

            {{-- Alerts --}}
            <div class="px-6 pt-4">
                @if(session('status'))
                    <div class="mb-4 px-4 py-3 rounded-lg bg-emerald-50 text-emerald-700 border border-emerald-100">
                        {{ session('status') }}
                    </div>
                @endif
                @if($errors->any())
                    <div class="mb-4 px-4 py-3 rounded-lg bg-red-50 text-red-700 border border-red-100 text-sm">
                        {{ $errors->first() }}
                    </div>
                @endif
            </div>

            {{-- Formulário principal (Identidade + Features + Vocabulário + Perfil) --}}
            <form method="POST"
                  action="{{ route('mmcloud.products.update', $product['id']) }}"
                  enctype="multipart/form-data"
                  id="product-form">
                @csrf
                @method('PUT')

                {{-- ══════════════════════════════════════════════════════════
                     TAB: Identidade
                ══════════════════════════════════════════════════════════ --}}
                <div id="tab-identidade" class="tab-panel px-6 pb-6 space-y-8 mt-4">

                    {{-- Dados básicos --}}
                    <div>
                        <h2 class="text-base font-semibold text-neutral-800 dark:text-neutral-200 mb-4">Dados básicos</h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                            <div>
                                <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1.5">
                                    Nome interno <span class="text-red-500">*</span>
                                </label>
                                <input type="text" name="name"
                                       value="{{ old('name', $product['name'] ?? '') }}" required
                                       class="w-full border border-neutral-200 dark:border-neutral-700 rounded-xl px-4 py-2 text-sm bg-white dark:bg-neutral-800 dark:text-white focus:border-[#ff8800] focus:ring-0 focus:outline-none">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1.5">
                                    Slug <span class="text-red-500">*</span>
                                </label>
                                <input type="text" name="slug"
                                       value="{{ old('slug', $product['slug'] ?? '') }}" required
                                       pattern="[a-z0-9_-]+"
                                       class="w-full border border-neutral-200 dark:border-neutral-700 rounded-xl px-4 py-2 text-sm bg-white dark:bg-neutral-800 dark:text-white focus:border-[#ff8800] focus:ring-0 focus:outline-none font-mono">
                                <p class="text-xs text-neutral-400 mt-1">Minúsculas, números, hífen, underscore.</p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1.5">
                                    Nome exibido (branding)
                                </label>
                                <input type="text" name="display_name"
                                       value="{{ old('display_name', $product['display_name'] ?? '') }}"
                                       class="w-full border border-neutral-200 dark:border-neutral-700 rounded-xl px-4 py-2 text-sm bg-white dark:bg-neutral-800 dark:text-white focus:border-[#ff8800] focus:ring-0 focus:outline-none">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1.5">
                                    Status <span class="text-red-500">*</span>
                                </label>
                                <select name="status" required
                                        class="w-full border border-neutral-200 dark:border-neutral-700 rounded-xl px-4 py-2 text-sm bg-white dark:bg-neutral-800 dark:text-white focus:border-[#ff8800] focus:ring-0 focus:outline-none">
                                    <option value="active"   {{ old('status', $product['status'] ?? '') === 'active'   ? 'selected' : '' }}>Ativo</option>
                                    <option value="inactive" {{ old('status', $product['status'] ?? '') === 'inactive' ? 'selected' : '' }}>Inativo</option>
                                </select>
                            </div>

                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1.5">
                                    Tagline
                                </label>
                                <input type="text" name="tagline"
                                       value="{{ old('tagline', $product['tagline'] ?? '') }}"
                                       class="w-full border border-neutral-200 dark:border-neutral-700 rounded-xl px-4 py-2 text-sm bg-white dark:bg-neutral-800 dark:text-white focus:border-[#ff8800] focus:ring-0 focus:outline-none">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1.5">
                                    Modalidade
                                </label>
                                <select name="modality_mode"
                                        class="w-full border border-neutral-200 dark:border-neutral-700 rounded-xl px-4 py-2 text-sm bg-white dark:bg-neutral-800 dark:text-white focus:border-[#ff8800] focus:ring-0 focus:outline-none">
                                    <option value="full"   {{ old('modality_mode', $modalityMode) === 'full'   ? 'selected' : '' }}>Completa (padrão)</option>
                                    <option value="health" {{ old('modality_mode', $modalityMode) === 'health' ? 'selected' : '' }}>Saúde (fisioterapia, clínica)</option>
                                </select>
                                <p class="text-xs text-neutral-400 mt-1">Define o formulário de modalidade e habilita a ficha de paciente.</p>
                            </div>

                        </div>
                    </div>

                    {{-- Cores --}}
                    <div>
                        <h2 class="text-base font-semibold text-neutral-800 dark:text-neutral-200 mb-1">Cores</h2>
                        <p class="text-xs text-neutral-500 mb-4">Sobrescrevem as variáveis CSS do painel do cliente. Deixe em branco para usar o padrão MM.</p>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            @foreach([
                                ['primary_color',   'Primária',   '--primary-color'],
                                ['secondary_color', 'Secundária', '--secondary-color'],
                                ['accent_color',    'Destaque',   '--mm-orange'],
                            ] as [$field, $label, $var])
                                @php $current = old($field, $product[$field] ?? ''); @endphp
                                <div>
                                    <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1.5">
                                        {{ $label }}
                                        <span class="text-xs text-neutral-400 font-normal ml-1">{{ $var }}</span>
                                    </label>
                                    <div class="flex items-center gap-2">
                                        <input type="color"
                                               id="picker_{{ $field }}"
                                               value="{{ $current ?: '#ffffff' }}"
                                               class="h-9 w-10 rounded-lg border border-neutral-200 dark:border-neutral-700 p-0.5 cursor-pointer bg-white dark:bg-neutral-800 flex-shrink-0">
                                        <input type="text"
                                               name="{{ $field }}"
                                               id="text_{{ $field }}"
                                               value="{{ $current }}"
                                               placeholder="#rrggbb"
                                               class="w-full border border-neutral-200 dark:border-neutral-700 rounded-xl px-3 py-2 text-sm bg-white dark:bg-neutral-800 dark:text-white focus:border-[#ff8800] focus:ring-0 focus:outline-none font-mono">
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    {{-- Logos & Favicon --}}
                    <div>
                        <h2 class="text-base font-semibold text-neutral-800 dark:text-neutral-200 mb-1">Logos &amp; Favicon</h2>
                        <p class="text-xs text-neutral-500 mb-4">Envie um novo arquivo apenas para substituir. Assets ficam no storage do MMCC.</p>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            @foreach([
                                ['logo_primary', 'Logo principal',        'png,jpg,jpeg,svg,webp', 'logo_primary_url', 'Sidebar / Login'],
                                ['logo_icon',    'Ícone (sidebar mínimo)','png,jpg,jpeg,svg,webp', 'logo_icon_url',    'Sidebar fechado'],
                                ['favicon',      'Favicon',               'png,ico',               'favicon_url',      '32×32 px'],
                            ] as [$field, $label, $accept, $urlKey, $hint])
                                <div>
                                    <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1.5">{{ $label }}</label>
                                    @if(!empty($product[$urlKey]))
                                        <div class="mb-2 flex items-center gap-2">
                                            <img src="{{ $product[$urlKey] }}" alt="{{ $label }}" class="h-8 w-auto rounded border border-neutral-200 dark:border-neutral-700 bg-neutral-50">
                                            <span class="text-xs text-neutral-400">atual</span>
                                        </div>
                                    @endif
                                    <input type="file" name="{{ $field }}"
                                           accept="{{ collect(explode(',', $accept))->map(fn($e) => str_starts_with($e, 'i') ? "image/$e" : "image/$e")->implode(',') }}"
                                           class="w-full text-sm text-neutral-700 dark:text-neutral-300 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-medium file:bg-[#ff8800]/10 file:text-[#ff8800] hover:file:bg-[#ff8800]/20 cursor-pointer">
                                    <p class="text-xs text-neutral-400 mt-1">{{ $hint }}</p>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    {{-- Submit --}}
                    <div class="flex items-center gap-3 pt-2">
                        <button type="submit" form="product-form"
                                class="inline-flex items-center gap-2 rounded-xl px-5 py-2.5 bg-gradient-to-r from-[#feb365] to-[#ff8800] text-white font-semibold text-sm">
                            <i data-lucide="save" class="w-4 h-4"></i>
                            Salvar alterações
                        </button>
                    </div>
                </div>

                {{-- ══════════════════════════════════════════════════════════
                     TAB: Features
                ══════════════════════════════════════════════════════════ --}}
                <div id="tab-features" class="tab-panel hidden px-6 pb-6 mt-4">
                    <p class="text-xs text-neutral-500 mb-4">Tenants sem produto veem tudo (fallback). Selecione apenas o que este nicho precisa.</p>

                    @include('mmcloud.products._feature_tree', [
                        'catalog'     => $catalog,
                        'enabledKeys' => $enabledKeys,
                    ])

                    <div class="mt-6">
                        <div class="flex items-center gap-3 pt-2">
                        <button type="submit" form="product-form"
                                class="inline-flex items-center gap-2 rounded-xl px-5 py-2.5 bg-gradient-to-r from-[#feb365] to-[#ff8800] text-white font-semibold text-sm">
                            <i data-lucide="save" class="w-4 h-4"></i>
                            Salvar alterações
                        </button>
                    </div>
                    </div>
                </div>

                {{-- ══════════════════════════════════════════════════════════
                     TAB: Vocabulário
                ══════════════════════════════════════════════════════════ --}}
                <div id="tab-vocabulary" class="tab-panel hidden px-6 pb-6 mt-4">

                    <p class="text-xs text-neutral-500 mb-4">
                        Sobrescreva labels do painel para este nicho. A chave deve corresponder ao que as views do MMCC leem via
                        <code class="bg-neutral-100 dark:bg-neutral-800 px-1 rounded text-xs">product_identity()->label('chave')</code>.
                    </p>

                    <div class="border border-neutral-200 dark:border-neutral-700 rounded-xl overflow-hidden mb-4">
                        <table class="w-full text-sm">
                            <thead class="bg-neutral-50 dark:bg-neutral-800">
                                <tr>
                                    <th class="text-left px-4 py-2.5 font-medium text-neutral-600 dark:text-neutral-400 w-2/5">Chave</th>
                                    <th class="text-left px-4 py-2.5 font-medium text-neutral-600 dark:text-neutral-400">Valor</th>
                                    <th class="w-10"></th>
                                </tr>
                            </thead>
                            <tbody id="vocab-rows" class="divide-y divide-neutral-100 dark:divide-neutral-800">
                                @forelse($vocabulary as $key => $value)
                                    <tr class="vocab-row">
                                        <td class="px-4 py-2">
                                            <input type="text" name="vocab_keys[]" value="{{ $key }}"
                                                   placeholder="ex: customer"
                                                   class="w-full border border-neutral-200 dark:border-neutral-700 rounded-lg px-3 py-1.5 text-xs bg-white dark:bg-neutral-800 dark:text-white focus:border-[#ff8800] focus:ring-0 focus:outline-none font-mono">
                                        </td>
                                        <td class="px-4 py-2">
                                            <input type="text" name="vocab_values[]" value="{{ $value }}"
                                                   placeholder="ex: Paciente"
                                                   class="w-full border border-neutral-200 dark:border-neutral-700 rounded-lg px-3 py-1.5 text-xs bg-white dark:bg-neutral-800 dark:text-white focus:border-[#ff8800] focus:ring-0 focus:outline-none">
                                        </td>
                                        <td class="px-2 py-2 text-center">
                                            <button type="button" onclick="this.closest('tr').remove()"
                                                    class="text-neutral-400 hover:text-red-500 transition-colors">
                                                <i data-lucide="x" class="w-4 h-4"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    {{-- linhas adicionadas via JS --}}
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <button type="button" id="add-vocab-row"
                            class="inline-flex items-center gap-2 rounded-xl px-4 py-2 border border-dashed border-neutral-300 dark:border-neutral-600 text-sm text-neutral-500 hover:border-[#ff8800] hover:text-[#ff8800] transition-colors mb-6">
                        <i data-lucide="plus" class="w-4 h-4"></i>
                        Adicionar chave
                    </button>

                    <div class="flex items-center gap-3 pt-2">
                        <button type="submit" form="product-form"
                                class="inline-flex items-center gap-2 rounded-xl px-5 py-2.5 bg-gradient-to-r from-[#feb365] to-[#ff8800] text-white font-semibold text-sm">
                            <i data-lucide="save" class="w-4 h-4"></i>
                            Salvar alterações
                        </button>
                    </div>
                </div>

                {{-- ══════════════════════════════════════════════════════════
                     TAB: Perfil do cliente
                ══════════════════════════════════════════════════════════ --}}
                <div id="tab-perfil" class="tab-panel hidden px-6 pb-6 mt-4">

                    <p class="text-xs text-neutral-500 mb-6">
                        Defina quais seções do perfil de cliente este produto coleta.
                        Os campos básicos (nome, telefone, e-mail) são sempre exibidos.
                    </p>

                    <div class="space-y-3 mb-6">
                        @foreach($customerFieldGroups as $groupKey => $group)
                            @php
                                $checked = old("customer_fields.$groupKey",
                                    isset($customerFields[$groupKey]) && $customerFields[$groupKey]);
                            @endphp
                            <label class="flex items-start gap-4 p-4 rounded-xl border cursor-pointer transition-colors
                                          {{ $checked
                                              ? 'border-[#ff8800] bg-[#ff8800]/5'
                                              : 'border-neutral-200 dark:border-neutral-700 hover:border-neutral-300' }}"
                                   id="label-cf-{{ $groupKey }}">
                                <input type="checkbox"
                                       name="customer_fields[{{ $groupKey }}]"
                                       value="1"
                                       {{ $checked ? 'checked' : '' }}
                                       class="mt-0.5 accent-[#ff8800] w-4 h-4 flex-shrink-0"
                                       onchange="toggleFieldLabel(this, 'label-cf-{{ $groupKey }}')">
                                <div>
                                    <p class="text-sm font-medium text-neutral-800 dark:text-neutral-200">{{ $group['label'] }}</p>
                                    <p class="text-xs text-neutral-500 mt-0.5">{{ $group['hint'] }}</p>
                                </div>
                            </label>
                        @endforeach
                    </div>

                    <div class="flex items-center gap-3 pt-2">
                        <button type="submit" form="product-form"
                                class="inline-flex items-center gap-2 rounded-xl px-5 py-2.5 bg-gradient-to-r from-[#feb365] to-[#ff8800] text-white font-semibold text-sm">
                            <i data-lucide="save" class="w-4 h-4"></i>
                            Salvar alterações
                        </button>
                    </div>
                </div>

            </form>

            {{-- ══════════════════════════════════════════════════════════
                 TAB: Tenants
            ══════════════════════════════════════════════════════════ --}}
            <div id="tab-tenants" class="tab-panel hidden px-6 pb-6 mt-4">

                {{-- Associar --}}
                <div class="mb-6">
                    <h2 class="text-base font-semibold text-neutral-800 dark:text-neutral-200 mb-3">Associar tenant</h2>
                    @if(empty($freeTenants))
                        <p class="text-sm text-neutral-500">Não há tenants sem produto para associar.</p>
                    @else
                        <form method="POST" action="{{ route('mmcloud.products.tenants.assign', $product['id']) }}"
                              class="flex items-center gap-3">
                            @csrf
                            <select name="tenant_id" required
                                    class="flex-1 border border-neutral-200 dark:border-neutral-700 rounded-xl px-4 py-2 text-sm bg-white dark:bg-neutral-800 dark:text-white focus:border-[#ff8800] focus:ring-0 focus:outline-none">
                                <option value="">Selecione um tenant…</option>
                                @foreach($freeTenants as $t)
                                    <option value="{{ $t['id'] }}">{{ $t['name'] }} ({{ $t['slug'] }})</option>
                                @endforeach
                            </select>
                            <button type="submit"
                                    class="inline-flex items-center gap-2 rounded-xl px-4 py-2 bg-gradient-to-r from-[#feb365] to-[#ff8800] text-white font-semibold text-sm flex-shrink-0">
                                <i data-lucide="link" class="w-4 h-4"></i>
                                Associar
                            </button>
                        </form>
                    @endif
                </div>

                {{-- Lista --}}
                <div>
                    <h2 class="text-base font-semibold text-neutral-800 dark:text-neutral-200 mb-3">Tenants associados</h2>
                    @if(empty($assignedTenants))
                        <p class="text-sm text-neutral-500 py-4">Nenhum tenant associado a este produto.</p>
                    @else
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm">
                                <thead>
                                    <tr class="bg-[#ff8800] text-black">
                                        <th class="text-left px-4 py-2.5 rounded-tl-xl font-semibold">Nome</th>
                                        <th class="text-left px-4 py-2.5 font-semibold">Slug</th>
                                        <th class="text-left px-4 py-2.5 font-semibold">Status</th>
                                        <th class="text-right px-4 py-2.5 rounded-tr-xl font-semibold">Ações</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-neutral-100 dark:divide-neutral-800">
                                    @foreach($assignedTenants as $t)
                                        <tr class="hover:bg-neutral-50 dark:hover:bg-neutral-800/50 transition-colors">
                                            <td class="px-4 py-3 font-medium text-neutral-900 dark:text-white">{{ $t['name'] }}</td>
                                            <td class="px-4 py-3">
                                                <code class="text-xs bg-neutral-100 dark:bg-neutral-800 px-2 py-1 rounded">{{ $t['slug'] }}</code>
                                            </td>
                                            <td class="px-4 py-3">
                                                @if(($t['active'] ?? true))
                                                    <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold bg-emerald-100 text-emerald-700">Ativo</span>
                                                @else
                                                    <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold bg-neutral-100 text-neutral-600 dark:bg-neutral-700 dark:text-neutral-400">Inativo</span>
                                                @endif
                                            </td>
                                            <td class="px-4 py-3 text-right">
                                                <form method="POST"
                                                      action="{{ route('mmcloud.products.tenants.unassign', [$product['id'], $t['id']]) }}"
                                                      class="inline"
                                                      onsubmit="return confirm('Desassociar {{ addslashes($t['name']) }}? O tenant voltará para o fallback.')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit"
                                                            class="inline-flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-xs font-medium border border-red-200 text-red-600 hover:bg-red-50 transition-colors">
                                                        <i data-lucide="unlink" class="w-3 h-3"></i>
                                                        Desassociar
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>

            </div>

        </div>
    </div>
</div>

@push('scripts')
<script>
// ── Tabs ─────────────────────────────────────────────────────────────────────
var tabBtns   = document.querySelectorAll('.tab-btn');
var tabPanels = document.querySelectorAll('.tab-panel');

tabBtns.forEach(function(btn) {
    btn.addEventListener('click', function() {
        var target = btn.dataset.tab;

        tabBtns.forEach(function(b) {
            b.classList.remove('tab-active', 'border-[#ff8800]', 'text-[#ff8800]');
            b.classList.add('border-transparent', 'text-neutral-500');
        });
        btn.classList.add('tab-active', 'border-[#ff8800]', 'text-[#ff8800]');
        btn.classList.remove('border-transparent', 'text-neutral-500');

        tabPanels.forEach(function(p) { p.classList.add('hidden'); });
        document.getElementById('tab-' + target).classList.remove('hidden');

        if (window.lucide) lucide.createIcons();
    });
});

// ── Color picker sync ─────────────────────────────────────────────────────────
document.querySelectorAll('[id^="picker_"]').forEach(function(picker) {
    var field     = picker.id.replace('picker_', '');
    var textInput = document.getElementById('text_' + field);
    if (!textInput) return;

    picker.addEventListener('input', function() { textInput.value = picker.value; });
    textInput.addEventListener('input', function() {
        if (/^#[0-9a-fA-F]{6}$/.test(textInput.value)) picker.value = textInput.value;
    });
    if (textInput.value.match(/^#[0-9a-fA-F]{6}$/)) picker.value = textInput.value;
});

// ── Vocabulário: adicionar linha ──────────────────────────────────────────────
function makeVocabRow() {
    var tr = document.createElement('tr');
    tr.className = 'vocab-row';
    tr.innerHTML = `
        <td class="px-4 py-2">
            <input type="text" name="vocab_keys[]" placeholder="ex: customer"
                   class="w-full border border-neutral-200 dark:border-neutral-700 rounded-lg px-3 py-1.5 text-xs bg-white dark:bg-neutral-800 dark:text-white focus:border-[#ff8800] focus:ring-0 focus:outline-none font-mono">
        </td>
        <td class="px-4 py-2">
            <input type="text" name="vocab_values[]" placeholder="ex: Paciente"
                   class="w-full border border-neutral-200 dark:border-neutral-700 rounded-lg px-3 py-1.5 text-xs bg-white dark:bg-neutral-800 dark:text-white focus:border-[#ff8800] focus:ring-0 focus:outline-none">
        </td>
        <td class="px-2 py-2 text-center">
            <button type="button" onclick="this.closest('tr').remove()"
                    class="text-neutral-400 hover:text-red-500 transition-colors">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </td>`;
    return tr;
}

document.getElementById('add-vocab-row')?.addEventListener('click', function() {
    document.getElementById('vocab-rows').appendChild(makeVocabRow());
    if (window.lucide) lucide.createIcons();
});

// ── Perfil do cliente: toggle borda do label ──────────────────────────────────
function toggleFieldLabel(checkbox, labelId) {
    var label = document.getElementById(labelId);
    if (!label) return;
    if (checkbox.checked) {
        label.classList.add('border-[#ff8800]', 'bg-[#ff8800]/5');
        label.classList.remove('border-neutral-200', 'dark:border-neutral-700');
    } else {
        label.classList.remove('border-[#ff8800]', 'bg-[#ff8800]/5');
        label.classList.add('border-neutral-200', 'dark:border-neutral-700');
    }
}
</script>
@endpush
@endsection
