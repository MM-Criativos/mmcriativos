@extends('layouts.app')

@php
    $tenantId = $tenant['tenant_id'] ?? null;
@endphp

@section('content')
    <div class="px-4 py-8">
        <div class="max-w-4xl mx-auto">
            <div class="bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-800 rounded-2xl shadow-sm overflow-hidden">
                <div class="px-6 py-6 border-b border-neutral-200 dark:border-neutral-800 flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                    <div>
                        <p class="text-sm uppercase tracking-[0.3em] text-neutral-500">MM Criativos Cloud</p>
                        <h1 class="text-2xl font-bold text-neutral-900 dark:text-white mt-1">Editar empresa</h1>
                    </div>

                    <div class="flex items-center gap-2">
                        <a href="{{ route('mmcloud.tenants.index') }}"
                            class="inline-flex items-center rounded-xl px-4 py-2 border border-neutral-200 dark:border-neutral-700 text-sm text-neutral-700 dark:text-neutral-200">
                            Voltar para lista
                        </a>
                        <a href="{{ route('mmcloud.tenants.instances.create', ['tenant' => $tenantId]) }}"
                            class="inline-flex items-center rounded-xl px-4 py-2 border border-[#ff8800] text-[#ff8800] font-semibold text-sm">
                            Instancia
                        </a>
                        <a href="{{ route('mmcloud.tenants.create') }}"
                            class="inline-flex items-center rounded-xl px-4 py-2 bg-gradient-to-r from-[#feb365] to-[#ff8800] text-white font-semibold text-sm">
                            Criar empresa
                        </a>
                    </div>
                </div>

                <div class="px-6 py-6">
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

                    <form method="POST" action="{{ route('mmcloud.tenants.update', ['tenant' => $tenantId], false) }}" class="space-y-4">
                        @csrf
                        @method('PATCH')

                        <div>
                            <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-200">Nome da empresa</label>
                            <input name="name" value="{{ old('name', $tenant['name'] ?? '') }}"
                                class="mt-1 block w-full border border-neutral-200 rounded-xl px-4 py-2 text-sm bg-white dark:bg-neutral-800 dark:border-neutral-700 focus:border-[#ff8800] focus:ring-0"
                                required>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-200">Slug</label>
                            <input name="slug" value="{{ old('slug', $tenant['slug'] ?? '') }}"
                                class="mt-1 block w-full border border-neutral-200 rounded-xl px-4 py-2 text-sm bg-white dark:bg-neutral-800 dark:border-neutral-700 focus:border-[#ff8800] focus:ring-0"
                                required>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-200">Status</label>
                            <select name="status"
                                class="mt-1 block w-full border border-neutral-200 rounded-xl px-4 py-2 text-sm bg-white dark:bg-neutral-800 dark:border-neutral-700 focus:border-[#ff8800] focus:ring-0"
                                required>
                                <option value="active" @selected(old('status', $tenant['status'] ?? '') === 'active')>Ativo</option>
                                <option value="trial" @selected(old('status', $tenant['status'] ?? '') === 'trial')>Trial</option>
                                <option value="suspended" @selected(old('status', $tenant['status'] ?? '') === 'suspended')>Suspenso</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-200">API key</label>
                            <div class="mt-1 flex items-center gap-2">
                                <input id="tenant-token" type="text" readonly
                                    value="{{ session('tenant_token', $tenant['api_token'] ?? '') }}"
                                    class="flex-1 border border-neutral-200 dark:border-neutral-700 rounded-xl px-3 py-2 text-sm bg-neutral-50 dark:bg-neutral-800">
                                <button type="button" onclick="copyTenantToken()"
                                    class="px-3 py-2 rounded-xl border border-[#ff8800] text-sm text-[#ff8800]">
                                    Copiar
                                </button>
                            </div>
                        </div>

                        <div class="pt-2">
                            <button type="submit"
                                class="inline-flex items-center justify-center gap-2 rounded-xl px-4 py-3 bg-neutral-900 text-white dark:bg-[#ff8800] dark:text-black font-semibold">
                                <i class="fa-regular fa-floppy-disk"></i>
                                Salvar alteracoes
                            </button>
                        </div>
                    </form>

                    <form method="POST" action="{{ route('mmcloud.tenants.regenerate-api-token', ['tenant' => $tenantId], false) }}"
                        class="mt-4"
                        onsubmit="return confirm('Regenerar API key deste tenant? A chave atual sera invalidada.');">
                        @csrf
                        <button type="submit"
                            class="inline-flex items-center justify-center gap-2 rounded-xl px-4 py-3 border border-[#ff8800] text-[#ff8800] font-semibold">
                            <i class="fa-regular fa-arrows-rotate"></i>
                            Regenerar API key
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        function copyTenantToken() {
            const field = document.getElementById('tenant-token');
            if (!field) return;

            if (navigator.clipboard?.writeText) {
                navigator.clipboard.writeText(field.value);
                return;
            }

            field.select();
            document.execCommand('copy');
        }
    </script>
@endsection
