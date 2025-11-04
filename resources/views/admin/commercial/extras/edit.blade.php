<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Editar Extra</h2>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            @include('admin.commercial._tabs')
            @if (session('status'))
                <div class="mb-4 p-3 bg-green-100 text-green-800 rounded">{{ session('status') }}</div>
            @endif
            @if ($errors->any())
                <div class="mb-4 p-3 bg-red-100 text-red-800 rounded">
                    <ul class="list-disc pl-5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="bg-white dark:bg-dark-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <form method="POST" action="{{ route('admin.commercial.extras.update', $extra) }}" class="space-y-4">
                        @csrf
                        @method('PUT')
                        @include('admin.commercial.extras._form', ['extra' => $extra])
                        <div class="flex gap-2">
                            <button class="px-5 py-3 bg-orange-600 text-white rounded hover:bg-orange-700">Salvar</button>
                            <a href="{{ route('admin.commercial.extras.index') }}" class="px-5 py-3 bg-gray-500 text-white rounded hover:bg-gray-600 dark:bg-gray-500 dark:hover:bg-gray-400">Voltar</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
