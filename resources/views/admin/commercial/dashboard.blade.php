<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Comercial</h2>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @include('admin.commercial._tabs')
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <a href="{{ route('admin.clients.index') }}"
                    class="block bg-white p-6 rounded shadow hover:shadow-md">
                    <div class="text-gray-500 text-sm">Clientes</div>
                    <div class="text-3xl font-bold text-gray-800">{{ $stats['clients'] ?? 0 }}</div>
                </a>
                <a href="{{ route('admin.testimonials.index') }}"
                    class="block bg-white p-6 rounded shadow hover:shadow-md">
                    <div class="text-gray-500 text-sm">Depoimentos</div>
                    <div class="text-3xl font-bold text-gray-800">{{ $stats['testimonials'] ?? 0 }}</div>
                </a>
                <a href="{{ route('admin.commercial.budgets.index') }}"
                    class="block bg-white p-6 rounded shadow hover:shadow-md">
                    <div class="text-gray-500 text-sm">Orçamentos</div>
                    <div class="text-3xl font-bold text-gray-800">{{ $stats['budgets'] ?? 0 }}</div>
                </a>
                <a href="{{ route('admin.commercial.plans.index') }}"
                    class="block bg-white p-6 rounded shadow hover:shadow-md">
                    <div class="text-gray-500 text-sm">Planos</div>
                    <div class="text-3xl font-bold text-gray-800">{{ $stats['plans'] ?? 0 }}</div>
                </a>
                <a href="{{ route('admin.commercial.extras.index') }}"
                    class="block bg-white p-6 rounded shadow hover:shadow-md">
                    <div class="text-gray-500 text-sm">Extras</div>
                    <div class="text-3xl font-bold text-gray-800">{{ $stats['extras'] ?? 0 }}</div>
                </a>
                <a href="{{ route('admin.commercial.email-templates.index') }}"
                    class="block bg-white p-6 rounded shadow hover:shadow-md">
                    <div class="text-gray-500 text-sm">Templates de E-mail</div>
                    <div class="text-3xl font-bold text-gray-800">{{ $stats['email_templates'] ?? 0 }}</div>
                </a>
                @if (auth()->user()?->role === 'admin' && Route::has('admin.commercial.kpi.index'))
                    <a href="{{ route('admin.commercial.kpi.index') }}"
                        class="block bg-white p-6 rounded shadow hover:shadow-md">
                        <div class="text-gray-500 text-sm">KPI</div>
                        <div class="text-3xl font-bold text-gray-800">{{ $stats['kpi'] ?? 0 }}</div>
                    </a>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
