<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Novo Processo</h2>
            <a href="{{ route('admin.processes.index') }}"
                class="inline-flex items-center px-6 py-4 bg-white text-orange-600 rounded border border-orange-600 font-semibold text-xs uppercase tracking-widest hover:bg-orange-600 hover:text-white transition">
                Voltar
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            @include('admin.content._tabs')

            @if ($errors->any())
                <div class="mb-4 p-3 bg-red-100 text-red-800 rounded">
                    <ul class="list-disc list-inside text-sm">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <form method="POST" action="{{ route('admin.processes.store') }}" class="space-y-6">
                        @csrf
                        @include('admin.processes._form', ['process' => $process])

                        <div class="flex justify-end gap-3">
                            <a href="{{ route('admin.processes.index') }}"
                                class="inline-flex items-center px-5 py-3 rounded border border-gray-300 text-sm text-gray-700 hover:bg-gray-100">
                                Cancelar
                            </a>
                            <button
                                class="inline-flex items-center gap-2 px-5 py-3 bg-orange-600 text-white rounded border border-transparent hover:bg-white hover:text-orange-600 hover:border-orange-600 text-sm">
                                <i class="fa-solid fa-plus"></i>
                                <span>Criar Processo</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
