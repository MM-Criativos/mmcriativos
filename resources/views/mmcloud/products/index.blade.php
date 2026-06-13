@extends('layouts.app')

@section('content')
<div class="px-4 py-8">
    <div class="max-w-7xl mx-auto">

        <div class="bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-800 rounded-2xl shadow-sm">

            {{-- Header --}}
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between px-6 py-6 border-b border-neutral-200 dark:border-neutral-800">
                <div>
                    <p class="text-sm uppercase tracking-[0.3em] text-neutral-500">MM Criativos Cloud</p>
                    <h1 class="text-2xl font-bold text-neutral-900 dark:text-white mt-1">Produtos</h1>
                    <p class="text-sm text-neutral-500 mt-1">Pacotes de features + identidade visual por nicho (ex: MM Health).</p>
                </div>
                <a href="{{ route('mmcloud.products.create') }}"
                   class="inline-flex items-center justify-center gap-2 rounded-xl px-4 py-3 bg-gradient-to-r from-[#feb365] to-[#ff8800] text-white font-semibold">
                    <i data-lucide="package-plus" class="w-4 h-4"></i>
                    Novo produto
                </a>
            </div>

            {{-- Alerts --}}
            <div class="px-6 pt-4">
                @if(session('status'))
                    <div class="mb-4 px-4 py-3 rounded-lg bg-emerald-50 text-emerald-700 border border-emerald-100">
                        {{ session('status') }}
                    </div>
                @endif
                @if($errors->any())
                    <div class="mb-4 px-4 py-3 rounded-lg bg-red-50 text-red-700 border border-red-100">
                        {{ $errors->first() }}
                    </div>
                @endif
            </div>

            {{-- Tabela --}}
            <div class="px-6 pb-6">
                @if(empty($products))
                    <p class="text-sm text-neutral-500 py-6">Nenhum produto cadastrado ainda.</p>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="bg-[#ff8800] text-black">
                                    <th class="text-left px-4 py-3 rounded-tl-xl font-semibold">Nome</th>
                                    <th class="text-left px-4 py-3 font-semibold">Slug</th>
                                    <th class="text-left px-4 py-3 font-semibold">Status</th>
                                    <th class="text-center px-4 py-3 font-semibold">Features</th>
                                    <th class="text-center px-4 py-3 font-semibold">Tenants</th>
                                    <th class="text-right px-4 py-3 rounded-tr-xl font-semibold">Ações</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-neutral-100 dark:divide-neutral-800">
                                @foreach($products as $product)
                                    <tr class="hover:bg-neutral-50 dark:hover:bg-neutral-800/50 transition-colors">
                                        <td class="px-4 py-3">
                                            <div class="flex items-center gap-3">
                                                @if(!empty($product['primary_color']))
                                                    <div class="w-3 h-3 rounded-full flex-shrink-0"
                                                         style="background-color: {{ $product['primary_color'] }}"></div>
                                                @endif
                                                <div>
                                                    <p class="font-medium text-neutral-900 dark:text-white">{{ $product['name'] }}</p>
                                                    @if(!empty($product['tagline']))
                                                        <p class="text-xs text-neutral-500">{{ $product['tagline'] }}</p>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-4 py-3">
                                            <code class="text-xs bg-neutral-100 dark:bg-neutral-800 px-2 py-1 rounded">{{ $product['slug'] }}</code>
                                        </td>
                                        <td class="px-4 py-3">
                                            @if($product['status'] === 'active')
                                                <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold bg-emerald-100 text-emerald-700">Ativo</span>
                                            @else
                                                <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold bg-neutral-100 text-neutral-600 dark:bg-neutral-700 dark:text-neutral-400">Inativo</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 text-center text-neutral-700 dark:text-neutral-300">
                                            {{ $product['features_count'] ?? '—' }}
                                        </td>
                                        <td class="px-4 py-3 text-center text-neutral-700 dark:text-neutral-300">
                                            {{ $product['tenants_count'] ?? '—' }}
                                        </td>
                                        <td class="px-4 py-3">
                                            <div class="flex items-center justify-end gap-2">
                                                <a href="{{ route('mmcloud.products.edit', $product['id']) }}"
                                                   class="inline-flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-xs font-medium border border-neutral-200 dark:border-neutral-700 text-neutral-700 dark:text-neutral-300 hover:border-[#ff8800] hover:text-[#ff8800] transition-colors">
                                                    <i data-lucide="pencil" class="w-3 h-3"></i>
                                                    Editar
                                                </a>
                                                <form action="{{ route('mmcloud.products.destroy', $product['id']) }}" method="POST" class="inline"
                                                      onsubmit="return confirm('Remover \"{{ addslashes($product['name']) }}\"? Tenants associados voltarão para o fallback.')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit"
                                                            class="inline-flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-xs font-medium border border-red-200 text-red-600 hover:bg-red-50 transition-colors">
                                                        <i data-lucide="trash-2" class="w-3 h-3"></i>
                                                        Remover
                                                    </button>
                                                </form>
                                            </div>
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
@endsection
