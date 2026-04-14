@extends('layouts.app')

@php
    $tenantId = $tenantId ?? ($tenant['tenant_id'] ?? null);
    $tenantName = $tenant['name'] ?? '-';
    $tenantSlug = $tenant['slug'] ?? '-';
    $instances = $instances ?? [];
    $instanceLoadError = $instanceLoadError ?? null;
    $selectedInstance = is_array($selectedInstance ?? null) ? $selectedInstance : null;
    $selectedInstanceId = $selectedInstanceId ?? ($selectedInstance['id'] ?? null);
    $isUpdatingInstance = is_array($selectedInstance) && !empty($selectedInstance['id']);
    $selectedConfigJson = '';

    if (is_array($selectedInstance['config'] ?? null) && ($selectedInstance['config'] ?? []) !== []) {
        $selectedConfigJson = json_encode($selectedInstance['config'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }
@endphp

@section('content')
    <div class="px-4 py-8">
        <div class="max-w-4xl mx-auto">
            <div class="bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-800 rounded-2xl shadow-sm overflow-hidden">
                <div class="px-6 py-6 border-b border-neutral-200 dark:border-neutral-800 flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                    <div>
                        <p class="text-sm uppercase tracking-[0.3em] text-neutral-500">MM Criativos Cloud</p>
                        <h1 class="text-2xl font-bold text-neutral-900 dark:text-white mt-1">
                            {{ $isUpdatingInstance ? 'Atualizar instancia' : 'Criar instancia' }}
                        </h1>
                        <p class="text-sm text-neutral-500 mt-1">
                            Tenant: <strong>{{ $tenantName }}</strong> ({{ $tenantSlug }})
                        </p>
                    </div>

                    <div class="flex items-center gap-2">
                        <a href="{{ route('mmcloud.tenants.edit', ['tenant' => $tenantId]) }}"
                            class="inline-flex items-center rounded-xl px-4 py-2 border border-neutral-200 dark:border-neutral-700 text-sm text-neutral-700 dark:text-neutral-200">
                            Voltar para tenant
                        </a>
                        <a href="{{ route('mmcloud.tenants.index') }}"
                            class="inline-flex items-center rounded-xl px-4 py-2 border border-neutral-200 dark:border-neutral-700 text-sm text-neutral-700 dark:text-neutral-200">
                            Empresas
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

                    @if ($isUpdatingInstance)
                        <div class="mb-4 px-4 py-3 rounded-lg bg-amber-50 text-amber-700 border border-amber-100">
                            Editando instancia existente: <strong>{{ $selectedInstance['instance_name'] ?? '-' }}</strong>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('mmcloud.tenants.instances.store', ['tenant' => $tenantId], false) }}" class="space-y-4">
                        @csrf
                        <input type="hidden" name="instance_id" value="{{ old('instance_id', $selectedInstanceId) }}">

                        <div>
                            <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-200">Nome da instancia</label>
                            <input name="instance_name" value="{{ old('instance_name', $selectedInstance['instance_name'] ?? '') }}"
                                class="mt-1 block w-full border border-neutral-200 rounded-xl px-4 py-2 text-sm bg-white dark:bg-neutral-800 dark:border-neutral-700 focus:border-[#ff8800] focus:ring-0"
                                placeholder="mmcc-agents" required>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-200">Evolution instance id</label>
                            <input name="evolution_instance_id" value="{{ old('evolution_instance_id', $selectedInstance['evolution_instance_id'] ?? '') }}"
                                class="mt-1 block w-full border border-neutral-200 rounded-xl px-4 py-2 text-sm bg-white dark:bg-neutral-800 dark:border-neutral-700 focus:border-[#ff8800] focus:ring-0"
                                placeholder="Opcional">
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-200">Canal</label>
                                <input name="channel" value="{{ old('channel', $selectedInstance['channel'] ?? 'whatsapp') }}"
                                    class="mt-1 block w-full border border-neutral-200 rounded-xl px-4 py-2 text-sm bg-white dark:bg-neutral-800 dark:border-neutral-700 focus:border-[#ff8800] focus:ring-0"
                                    placeholder="whatsapp">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-200">Status</label>
                                <select name="status"
                                    class="mt-1 block w-full border border-neutral-200 rounded-xl px-4 py-2 text-sm bg-white dark:bg-neutral-800 dark:border-neutral-700 focus:border-[#ff8800] focus:ring-0"
                                    required>
                                    <option value="inactive" @selected(old('status', $selectedInstance['status'] ?? 'inactive') === 'inactive')>Inactive</option>
                                    <option value="active" @selected(old('status', $selectedInstance['status'] ?? 'inactive') === 'active')>Active</option>
                                    <option value="maintenance" @selected(old('status', $selectedInstance['status'] ?? 'inactive') === 'maintenance')>Maintenance</option>
                                </select>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-200">Config JSON (opcional)</label>
                            <textarea name="config_json" rows="6"
                                class="mt-1 block w-full border border-neutral-200 rounded-xl px-4 py-2 text-sm bg-white dark:bg-neutral-800 dark:border-neutral-700 focus:border-[#ff8800] focus:ring-0"
                                placeholder='{"provider":"evolution","auto_reconnect":true}'>{{ old('config_json', $selectedConfigJson) }}</textarea>
                        </div>

                        <div class="pt-2">
                            <button type="submit"
                                class="inline-flex items-center justify-center gap-2 rounded-xl px-4 py-3 bg-neutral-900 text-white dark:bg-[#ff8800] dark:text-black font-semibold">
                                <i class="fa-solid {{ $isUpdatingInstance ? 'fa-pen-to-square' : 'fa-plus' }}"></i>
                                {{ $isUpdatingInstance ? 'Atualizar instancia' : 'Criar instancia' }}
                            </button>
                        </div>
                    </form>

                    @if (session('instance_result'))
                        <div class="mt-6 border border-neutral-200 dark:border-neutral-700 rounded-xl p-4 bg-neutral-50 dark:bg-neutral-800">
                            <p class="text-sm font-semibold text-neutral-800 dark:text-neutral-100 mb-2">Ultimo retorno da instancia</p>
                            <pre class="text-xs whitespace-pre-wrap break-all text-neutral-700 dark:text-neutral-200">{{ json_encode(session('instance_result'), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                        </div>
                    @endif

                    <div class="mt-6 border border-neutral-200 dark:border-neutral-700 rounded-xl overflow-hidden">
                        <div class="px-4 py-3 border-b border-neutral-200 dark:border-neutral-700 bg-neutral-50 dark:bg-neutral-800">
                            <h3 class="text-sm font-semibold text-neutral-800 dark:text-neutral-100">Instancias cadastradas</h3>
                        </div>

                        @if ($instanceLoadError)
                            <div class="px-4 py-3 text-sm text-red-600 dark:text-red-400">
                                {{ $instanceLoadError }}
                            </div>
                        @elseif (empty($instances))
                            <div class="px-4 py-3 text-sm text-neutral-500">
                                Nenhuma instancia cadastrada para este tenant.
                            </div>
                        @else
                            <div class="overflow-x-auto">
                                <table class="w-full text-sm">
                                    <thead class="bg-neutral-100 dark:bg-neutral-800 text-neutral-700 dark:text-neutral-200">
                                        <tr>
                                            <th class="text-left px-4 py-2">Nome</th>
                                            <th class="text-left px-4 py-2">Evolution ID</th>
                                            <th class="text-left px-4 py-2">Canal</th>
                                            <th class="text-left px-4 py-2">Status</th>
                                            <th class="text-left px-4 py-2">Atualizado em</th>
                                            <th class="text-left px-4 py-2">Formulario</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($instances as $instance)
                                            @php
                                                $status = (string) ($instance['status'] ?? 'inactive');
                                                $isSelected = (int) ($instance['id'] ?? 0) === (int) ($selectedInstanceId ?? 0);
                                                $statusBadge = match ($status) {
                                                    'active' => 'bg-emerald-100 text-emerald-700',
                                                    'maintenance' => 'bg-amber-100 text-amber-700',
                                                    default => 'bg-neutral-200 text-neutral-700',
                                                };
                                            @endphp
                                            <tr class="border-t border-neutral-200 dark:border-neutral-700 {{ $isSelected ? 'bg-amber-50 dark:bg-neutral-700/30' : '' }}">
                                                <td class="px-4 py-2 font-medium text-neutral-800 dark:text-neutral-100">{{ $instance['instance_name'] ?? '-' }}</td>
                                                <td class="px-4 py-2 text-neutral-700 dark:text-neutral-300">{{ $instance['evolution_instance_id'] ?? '-' }}</td>
                                                <td class="px-4 py-2 text-neutral-700 dark:text-neutral-300">{{ $instance['channel'] ?? '-' }}</td>
                                                <td class="px-4 py-2">
                                                    <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold {{ $statusBadge }}">
                                                        {{ strtoupper($status) }}
                                                    </span>
                                                </td>
                                                <td class="px-4 py-2 text-neutral-700 dark:text-neutral-300">{{ $instance['updated_at'] ?? '-' }}</td>
                                                <td class="px-4 py-2">
                                                    <a href="{{ route('mmcloud.tenants.instances.create', ['tenant' => $tenantId, 'instance_id' => $instance['id'] ?? 0]) }}"
                                                        class="inline-flex items-center rounded-lg px-3 py-1.5 border border-[#ff8800] text-[#ff8800] text-xs font-semibold">
                                                        Usar no formulario
                                                    </a>
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
@endsection
