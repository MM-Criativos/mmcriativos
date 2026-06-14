@extends('layouts.app')

@section('content')
<div class="px-4 py-8">
    <div class="max-w-4xl mx-auto">

        <div class="bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-800 rounded-2xl shadow-sm">

            {{-- Header --}}
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between px-6 py-6 border-b border-neutral-200 dark:border-neutral-800">
                <div>
                    <p class="text-sm uppercase tracking-[0.3em] text-neutral-500">MM Criativos Cloud</p>
                    <h1 class="text-2xl font-bold text-neutral-900 dark:text-white mt-1">Novo Produto</h1>
                </div>
                <a href="{{ route('mmcloud.products.index') }}"
                   class="inline-flex items-center gap-2 rounded-xl px-4 py-2.5 border border-neutral-200 dark:border-neutral-700 text-sm text-neutral-700 dark:text-neutral-300 hover:border-[#ff8800] transition-colors">
                    <i data-lucide="arrow-left" class="w-4 h-4"></i>
                    Voltar
                </a>
            </div>

            <form method="POST" action="{{ route('mmcloud.products.store') }}" enctype="multipart/form-data" class="px-6 py-6 space-y-8">
                @csrf

                {{-- Erros --}}
                @if($errors->any())
                    <div class="px-4 py-3 rounded-lg bg-red-50 text-red-700 border border-red-100 text-sm">
                        {{ $errors->first() }}
                    </div>
                @endif

                {{-- ── Identidade ──────────────────────────────────────────── --}}
                <div>
                    <h2 class="text-base font-semibold text-neutral-800 dark:text-neutral-200 mb-4">Identidade</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                        <div>
                            <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1.5">
                                Nome interno <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="name" value="{{ old('name') }}" required
                                   placeholder="Ex: MM Health"
                                   class="w-full border border-neutral-200 dark:border-neutral-700 rounded-xl px-4 py-2 text-sm bg-white dark:bg-neutral-800 dark:text-white focus:border-[#ff8800] focus:ring-0 focus:outline-none">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1.5">
                                Slug <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="slug" value="{{ old('slug') }}" required
                                   placeholder="ex: mm_health"
                                   pattern="[a-z0-9_-]+"
                                   class="w-full border border-neutral-200 dark:border-neutral-700 rounded-xl px-4 py-2 text-sm bg-white dark:bg-neutral-800 dark:text-white focus:border-[#ff8800] focus:ring-0 focus:outline-none font-mono">
                            <p class="text-xs text-neutral-400 mt-1">Minúsculas, números, hífen, underscore.</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1.5">
                                Nome exibido (branding)
                            </label>
                            <input type="text" name="display_name" value="{{ old('display_name') }}"
                                   placeholder="Ex: MM Health"
                                   class="w-full border border-neutral-200 dark:border-neutral-700 rounded-xl px-4 py-2 text-sm bg-white dark:bg-neutral-800 dark:text-white focus:border-[#ff8800] focus:ring-0 focus:outline-none">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1.5">
                                Status <span class="text-red-500">*</span>
                            </label>
                            <select name="status" required
                                    class="w-full border border-neutral-200 dark:border-neutral-700 rounded-xl px-4 py-2 text-sm bg-white dark:bg-neutral-800 dark:text-white focus:border-[#ff8800] focus:ring-0 focus:outline-none">
                                <option value="active"   {{ old('status', 'active') === 'active'   ? 'selected' : '' }}>Ativo</option>
                                <option value="inactive" {{ old('status') === 'inactive' ? 'selected' : '' }}>Inativo</option>
                            </select>
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1.5">
                                Tagline
                            </label>
                            <input type="text" name="tagline" value="{{ old('tagline') }}"
                                   placeholder="Ex: Gestão inteligente de fisioterapia"
                                   class="w-full border border-neutral-200 dark:border-neutral-700 rounded-xl px-4 py-2 text-sm bg-white dark:bg-neutral-800 dark:text-white focus:border-[#ff8800] focus:ring-0 focus:outline-none">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1.5">
                                Modalidade
                            </label>
                            <select name="modality_mode"
                                    class="w-full border border-neutral-200 dark:border-neutral-700 rounded-xl px-4 py-2 text-sm bg-white dark:bg-neutral-800 dark:text-white focus:border-[#ff8800] focus:ring-0 focus:outline-none">
                                <option value="full"   {{ old('modality_mode', 'full') === 'full'   ? 'selected' : '' }}>Completa (padrão)</option>
                                <option value="health" {{ old('modality_mode') === 'health' ? 'selected' : '' }}>Saúde (fisioterapia, clínica)</option>
                            </select>
                            <p class="text-xs text-neutral-400 mt-1">Define o formulário de modalidade e habilita a ficha de paciente.</p>
                        </div>

                    </div>
                </div>

                {{-- ── Cores ──────────────────────────────────────────────── --}}
                <div>
                    <h2 class="text-base font-semibold text-neutral-800 dark:text-neutral-200 mb-1">Cores</h2>
                    <p class="text-xs text-neutral-500 mb-4">Sobrescrevem as variáveis CSS do painel do cliente. Deixe em branco para usar o padrão MM.</p>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        @foreach([
                            ['primary_color',   'Primária',  '#0D9488', '--primary-color'],
                            ['secondary_color', 'Secundária','#0F766E', '--secondary-color'],
                            ['accent_color',    'Destaque',  '#14B8A6', '--mm-orange'],
                        ] as [$field, $label, $placeholder, $var])
                            <div>
                                <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1.5">
                                    {{ $label }}
                                    <span class="text-xs text-neutral-400 font-normal ml-1">{{ $var }}</span>
                                </label>
                                <div class="flex items-center gap-2">
                                    <input type="color"
                                           id="picker_{{ $field }}"
                                           value="{{ old($field, '#ffffff') }}"
                                           class="h-9 w-10 rounded-lg border border-neutral-200 dark:border-neutral-700 p-0.5 cursor-pointer bg-white dark:bg-neutral-800 flex-shrink-0">
                                    <input type="text"
                                           name="{{ $field }}"
                                           id="text_{{ $field }}"
                                           value="{{ old($field) }}"
                                           placeholder="{{ $placeholder }}"
                                           class="w-full border border-neutral-200 dark:border-neutral-700 rounded-xl px-3 py-2 text-sm bg-white dark:bg-neutral-800 dark:text-white focus:border-[#ff8800] focus:ring-0 focus:outline-none font-mono">
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- ── Logos & Favicon ───────────────────────────────────── --}}
                <div>
                    <h2 class="text-base font-semibold text-neutral-800 dark:text-neutral-200 mb-1">Logos &amp; Favicon</h2>
                    <p class="text-xs text-neutral-500 mb-4">Assets ficam no storage do MMCC, que renderiza no painel do cliente.</p>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        @foreach([
                            ['logo_primary', 'Logo principal',        'png,jpg,jpeg,svg,webp', 'Sidebar / Login'],
                            ['logo_icon',    'Ícone (sidebar mínimo)','png,jpg,jpeg,svg,webp', 'Quando sidebar está fechado'],
                            ['favicon',      'Favicon',               'png,ico',               '32×32 px'],
                        ] as [$field, $label, $accept, $hint])
                            <div>
                                <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1.5">{{ $label }}</label>
                                <input type="file" name="{{ $field }}" accept="{{ collect(explode(',', $accept))->map(fn($e) => 'image/'.$e)->implode(',') }}"
                                       class="w-full text-sm text-neutral-700 dark:text-neutral-300 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-medium file:bg-[#ff8800]/10 file:text-[#ff8800] hover:file:bg-[#ff8800]/20 cursor-pointer">
                                <p class="text-xs text-neutral-400 mt-1">{{ $hint }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- ── Features ──────────────────────────────────────────── --}}
                <div>
                    <h2 class="text-base font-semibold text-neutral-800 dark:text-neutral-200 mb-1">Features habilitadas</h2>
                    <p class="text-xs text-neutral-500 mb-4">Tenants sem produto veem tudo (fallback). Selecione apenas o que este nicho precisa.</p>

                    @include('mmcloud.products._feature_tree', [
                        'catalog'     => $catalog,
                        'enabledKeys' => [],
                    ])
                </div>

                {{-- Submit --}}
                <div class="flex items-center gap-3 pt-2">
                    <button type="submit"
                            class="inline-flex items-center gap-2 rounded-xl px-5 py-2.5 bg-gradient-to-r from-[#feb365] to-[#ff8800] text-white font-semibold text-sm">
                        <i data-lucide="save" class="w-4 h-4"></i>
                        Criar produto
                    </button>
                    <a href="{{ route('mmcloud.products.index') }}"
                       class="inline-flex items-center gap-2 rounded-xl px-5 py-2.5 border border-neutral-200 dark:border-neutral-700 text-sm text-neutral-700 dark:text-neutral-300">
                        Cancelar
                    </a>
                </div>

            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
// Sincronizar color picker ↔ input texto
document.querySelectorAll('[id^="picker_"]').forEach(function(picker) {
    var field   = picker.id.replace('picker_', '');
    var textInput = document.getElementById('text_' + field);

    picker.addEventListener('input', function() {
        textInput.value = picker.value;
    });
    textInput.addEventListener('input', function() {
        if (/^#[0-9a-fA-F]{6}$/.test(textInput.value)) {
            picker.value = textInput.value;
        }
    });
    // Inicializar picker com valor atual do texto
    if (textInput.value.match(/^#[0-9a-fA-F]{6}$/)) {
        picker.value = textInput.value;
    }
});
</script>
@endpush
@endsection
