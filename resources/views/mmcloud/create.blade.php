@extends('layouts.app')

@section('content')
    <div class="min-h-[70vh] flex items-center justify-center px-4 py-10">
        <div class="w-full max-w-4xl bg-white dark:bg-neutral-900 rounded-2xl shadow-xl overflow-hidden grid md:grid-cols-2">

            <div class="px-8 py-10 md:py-12 bg-gradient-to-br from-[#ff8800] to-[#feb365] text-white space-y-6">
                <div>
                    <p class="text-sm uppercase tracking-[0.4em]">MM Criativos Cloud</p>
                    <h1 class="text-3xl font-bold mt-2">Criar empresa</h1>
                </div>

                <p class="text-sm leading-relaxed">
                    Solicite a criação de um tenant dentro do MM Criativos Cloud. Basta informar o nome da empresa e o subdomínio desejado; o token será gerado automaticamente.
                </p>

                <ul class="text-sm space-y-2">
                    <li>• Nome da empresa que aparecerá no painel.</li>
                    <li>• Subdomínio exclusivo (será usado como slug em <code>mmcriativos.cloud</code>).</li>
                    <li>• A plataforma responde com o <strong>api_token</strong>; armazene-o com segurança.</li>
                </ul>
            </div>

            <div class="px-8 py-10">
                @if (session('status'))
                    <div class="mb-4 px-4 py-3 rounded-lg bg-emerald-50 text-emerald-700 border border-emerald-100">
                        {{ session('status') }}
                    </div>
                @endif

                <form action="{{ route('mmcloud.tenants.store') }}" method="POST" class="space-y-4">
                    @csrf

                    <div>
                        <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-200">Nome da empresa</label>
                        <input name="name" value="{{ old('name') }}"
                            class="mt-1 block w-full border border-neutral-200 rounded-xl px-4 py-2 text-sm bg-white dark:bg-neutral-800 dark:border-neutral-700 focus:border-[#ff8800] focus:ring-0"
                            required>
                        @error('name')
                            <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-200">Domínio (subdomínio)</label>
                        <input name="domain" value="{{ old('domain') }}"
                            class="mt-1 block w-full border border-neutral-200 rounded-xl px-4 py-2 text-sm bg-white dark:bg-neutral-800 dark:border-neutral-700 focus:border-[#ff8800] focus:ring-0"
                            placeholder="exemplo" required>
                        @error('domain')
                            <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <button type="submit"
                        class="w-full inline-flex items-center justify-center gap-2 rounded-xl px-4 py-3 bg-gradient-to-r from-[#feb365] to-[#ff8800] text-white font-semibold">
                        <i class="fa-solid fa-cloud-arrow-up icon-project"></i>
                        Solicitar tenant
                    </button>
                </form>

                @if (session('tenant_token') && session('tenant_slug'))
                    <div class="mt-6 border border-neutral-200 dark:border-neutral-700 rounded-xl p-4 bg-white dark:bg-neutral-900">
                        <p class="text-xs text-neutral-500 mb-2">Token gerado</p>
                        <div class="flex gap-2 items-center">
                            <input id="tenant-token" type="text" readonly
                                value="{{ session('tenant_token') }}"
                                class="flex-1 border border-neutral-200 dark:border-neutral-700 rounded-xl px-3 py-2 text-sm bg-neutral-50 dark:bg-neutral-800">
                            <button type="button"
                                class="px-3 py-2 rounded-xl border border-[#ff8800] text-sm text-[#ff8800]"
                                onclick="copyTenantToken()">
                                Copiar
                            </button>
                        </div>
                        <p class="text-xs text-neutral-500 mt-3">
                            A empresa será disponibilizada em <strong>{{ session('tenant_slug') }}.mmcriativos.cloud</strong>.
                        </p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <script>
        function copyTenantToken() {
            const field = document.getElementById('tenant-token');
            if (!field) {
                return;
            }

            if (navigator.clipboard?.writeText) {
                navigator.clipboard.writeText(field.value);
                return;
            }

            field.select();
            document.execCommand('copy');
        }
    </script>
@endsection
