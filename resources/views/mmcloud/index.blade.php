@extends('layouts.app')

@php
    $statusOptions = [
        '' => 'Todos',
        'active' => 'Ativo',
        'trial' => 'Trial',
        'suspended' => 'Suspenso',
    ];

    $meta = array_merge([
        'current_page' => 1,
        'last_page' => 1,
        'per_page' => 20,
        'total' => count($tenants ?? []),
    ], $meta ?? []);

    $currentPage = (int) ($meta['current_page'] ?? 1);
    $lastPage = max(1, (int) ($meta['last_page'] ?? 1));
@endphp

@section('content')
    <div class="px-4 py-8">
        <div class="max-w-7xl mx-auto">
            <div class="bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-800 rounded-2xl shadow-sm">
                <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between px-6 py-6 border-b border-neutral-200 dark:border-neutral-800">
                    <div>
                        <p class="text-sm uppercase tracking-[0.3em] text-neutral-500">MM Criativos Cloud</p>
                        <h1 class="text-2xl font-bold text-neutral-900 dark:text-white mt-1">Empresas</h1>
                        <p class="text-sm text-neutral-500 mt-1">Listagem de tenants criados no MMCC com nome, slug, status e api key.</p>
                    </div>

                    <a href="{{ route('mmcloud.tenants.create') }}"
                        class="inline-flex items-center justify-center gap-2 rounded-xl px-4 py-3 bg-gradient-to-r from-[#feb365] to-[#ff8800] text-white font-semibold">
                        <i data-lucide="cloud-upload" class="icon-project"></i>
                        Criar empresa
                    </a>
                </div>

                <div class="px-6 pt-4">
                    @if (session('status'))
                        <div class="mb-4 px-4 py-3 rounded-lg bg-emerald-50 text-emerald-700 border border-emerald-100">
                            {{ session('status') }}
                        </div>
                    @endif

                    @if ($errors->any())
                        <div class="mb-4 px-4 py-3 rounded-lg bg-red-50 text-red-700 border border-red-100">
                            {{ $errors->first() }}
                        </div>
                    @endif
                </div>

                <div class="px-6 pb-4">
                    <form method="GET" action="{{ route('mmcloud.tenants.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-3">
                        <div class="md:col-span-2">
                            <input name="search" value="{{ request('search') }}" placeholder="Buscar por nome ou slug"
                                class="w-full border border-neutral-200 dark:border-neutral-700 rounded-xl px-4 py-2 text-sm bg-white dark:bg-neutral-800 dark:text-white focus:border-[#ff8800] focus:ring-0">
                        </div>

                        <div>
                            <select name="status"
                                class="w-full border border-neutral-200 dark:border-neutral-700 rounded-xl px-4 py-2 text-sm bg-white dark:bg-neutral-800 dark:text-white focus:border-[#ff8800] focus:ring-0">
                                @foreach ($statusOptions as $value => $label)
                                    <option value="{{ $value }}" @selected((string) request('status', '') === (string) $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="flex gap-2">
                            <button type="submit"
                                class="flex-1 inline-flex items-center justify-center rounded-xl px-4 py-2 bg-neutral-900 text-white dark:bg-[#ff8800] dark:text-black font-medium">
                                Filtrar
                            </button>

                            <a href="{{ route('mmcloud.tenants.index') }}"
                                class="inline-flex items-center justify-center rounded-xl px-4 py-2 border border-neutral-200 dark:border-neutral-700 text-sm text-neutral-700 dark:text-neutral-200">
                                Limpar
                            </a>
                        </div>
                    </form>
                </div>

                <div class="overflow-x-auto px-6 pb-4">
                    <table class="w-full text-sm border border-neutral-200 dark:border-neutral-800 rounded-xl overflow-hidden border-separate border-spacing-0">
                        <thead class="bg-[#ff8800] text-black">
                            <tr>
                                <th class="text-left py-3 px-4 first:rounded-tl-xl">ID</th>
                                <th class="text-left py-3 px-4">Empresa</th>
                                <th class="text-left py-3 px-4">Slug</th>
                                <th class="text-left py-3 px-4">Status</th>
                                <th class="text-left py-3 px-4">API Key</th>
                                <th class="text-right py-3 px-4 last:rounded-tr-xl">Acoes</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-neutral-900 dark:text-neutral-100">
                            @forelse ($tenants as $tenant)
                                @php
                                    $statusLabel = match ($tenant['status'] ?? null) {
                                        'active' => 'Ativo',
                                        'suspended' => 'Suspenso',
                                        default => 'Trial',
                                    };

                                    $statusClass = match ($tenant['status'] ?? null) {
                                        'active' => 'bg-emerald-100 text-emerald-700',
                                        'suspended' => 'bg-red-100 text-red-700',
                                        default => 'bg-amber-100 text-amber-700',
                                    };
                                @endphp
                                <tr class="hover:bg-neutral-50 dark:hover:bg-neutral-800 transition">
                                    <td class="py-3 px-4 border-t border-neutral-200 dark:border-neutral-800">{{ $tenant['id'] ?? '-' }}</td>
                                    <td class="py-3 px-4 border-t border-neutral-200 dark:border-neutral-800 font-medium">{{ $tenant['name'] ?? '-' }}</td>
                                    <td class="py-3 px-4 border-t border-neutral-200 dark:border-neutral-800">{{ $tenant['slug'] ?? '-' }}</td>
                                    <td class="py-3 px-4 border-t border-neutral-200 dark:border-neutral-800">
                                        <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold {{ $statusClass }}">{{ $statusLabel }}</span>
                                    </td>
                                    <td class="py-3 px-4 border-t border-neutral-200 dark:border-neutral-800">
                                        <div class="flex items-center gap-2">
                                            <code class="text-xs break-all">{{ $tenant['api_token'] ?? '-' }}</code>
                                            @if (!empty($tenant['api_token']))
                                                <button type="button" data-copy="{{ $tenant['api_token'] }}" class="text-[#ff8800]" title="Copiar token">
                                                    <i data-lucide="copy"></i>
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="py-3 px-4 border-t border-neutral-200 dark:border-neutral-800 text-right">
                                        <a href="{{ route('mmcloud.tenants.edit', ['tenant' => $tenant['id'] ?? $tenant['slug'] ?? '']) }}"
                                            class="inline-flex items-center gap-2 rounded-lg px-3 py-2 border border-[#ff8800] text-[#ff8800] font-semibold">
                                            <i data-lucide="square-pen"></i>
                                            Editar
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="py-8 px-4 text-center text-neutral-500">Nenhum tenant encontrado.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="px-6 py-4 border-t border-neutral-200 dark:border-neutral-800 flex items-center justify-between text-sm text-neutral-600 dark:text-neutral-300">
                    <span>Total: <strong>{{ $meta['total'] ?? 0 }}</strong></span>

                    <div class="flex items-center gap-2">
                        @if ($currentPage > 1)
                            <a href="{{ request()->fullUrlWithQuery(['page' => $currentPage - 1]) }}"
                                class="px-3 py-1 rounded-lg border border-neutral-200 dark:border-neutral-700">Anterior</a>
                        @endif

                        <span>Pagina {{ $currentPage }} de {{ $lastPage }}</span>

                        @if ($currentPage < $lastPage)
                            <a href="{{ request()->fullUrlWithQuery(['page' => $currentPage + 1]) }}"
                                class="px-3 py-1 rounded-lg border border-neutral-200 dark:border-neutral-700">Proxima</a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.querySelectorAll('[data-copy]').forEach(function(button) {
            button.addEventListener('click', function() {
                const value = button.getAttribute('data-copy');
                if (!value) return;

                if (navigator.clipboard?.writeText) {
                    navigator.clipboard.writeText(value);
                    return;
                }

                const helper = document.createElement('textarea');
                helper.value = value;
                document.body.appendChild(helper);
                helper.select();
                document.execCommand('copy');
                document.body.removeChild(helper);
            });
        });
    </script>
@endsection
