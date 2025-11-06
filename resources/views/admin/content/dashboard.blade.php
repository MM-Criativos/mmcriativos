<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Conteúdo</h2>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @include('admin.content._tabs')

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                <a href="{{ route('admin.services.index') }}" class="block bg-white p-6 rounded shadow hover:shadow-md">
                    <div class="text-gray-500 text-sm">Serviços</div>
                    <div class="text-3xl font-bold text-gray-800">{{ $stats['services'] ?? 0 }}</div>
                </a>
                <a href="{{ route('admin.skills.index') }}" class="block bg-white p-6 rounded shadow hover:shadow-md">
                    <div class="text-gray-500 text-sm">Habilidades</div>
                    <div class="text-3xl font-bold text-gray-800">{{ $stats['skills'] ?? 0 }}</div>
                </a>
                <a href="{{ route('admin.processes.index') }}" class="block bg-white p-6 rounded shadow hover:shadow-md">
                    <div class="text-gray-500 text-sm">Processos</div>
                    <div class="text-3xl font-bold text-gray-800">{{ $stats['processes'] ?? 0 }}</div>
                </a>
            </div>
        </div>
    </div>
</x-app-layout>
