<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-100 leading-tight">
            {{ __('Painel Administrativo') }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">

                <a href="{{ route('admin.layout.index') }}"
                    class="block bg-white dark:bg-dark-800 border rounded-lg p-6 shadow-sm opacity-80 hover:bg-gray-50 dark:hover:bg-dark-700">
                    <div class="text-lg font-semibold">Layout</div>
                    <p class="text-sm text-gray-600">
                        Acesse configurações de layout como o Slider.
                    </p>
                </a>

                <a href="{{ route('admin.content.dashboard') }}"
                    class="block bg-white dark:bg-dark-800 hover:bg-gray-50 dark:hover:bg-dark-700 border rounded-lg p-6 shadow-sm">
                    <div class="text-lg font-semibold">Conteúdo</div>
                    <p class="text-sm text-gray-600">
                        Serviços e Habilidades em um só lugar.
                    </p>
                </a>

                <a href="{{ route('admin.projects.index') }}"
                    class="block bg-white dark:bg-dark-800 border rounded-lg p-6 shadow-sm opacity-80 hover:bg-gray-50 dark:hover:bg-dark-700">
                    <div class="text-lg font-semibold">Projetos</div>
                    <p class="text-sm text-gray-600">
                        Organize projetos com clientes, serviços, equipe e materiais visuais.
                    </p>
                </a>

                @if (Route::has('admin.commercial.dashboard'))
                    <a href="{{ route('admin.commercial.dashboard') }}"
                        class="block bg-white dark:bg-dark-800 border rounded-lg p-6 shadow-sm hover:bg-gray-50 dark:hover:bg-dark-700">
                        <div class="text-lg font-semibold">Comercial</div>
                        <p class="text-sm text-gray-600">
                            Clientes, depoimentos, orçamentos, planos, extras e e-mails.
                        </p>
                    </a>
                @endif

                @if (Auth::user()->role === 'admin' && Route::has('admin.team.index'))
                    <a href="{{ route('admin.team.index') }}"
                        class="block bg-white dark:bg-dark-800 border rounded-lg p-6 shadow-sm opacity-80 hover:bg-gray-50 dark:hover:bg-dark-700">
                        <div class="text-lg font-semibold">Equipe</div>
                        <p class="text-sm text-gray-600">
                            Gerencie a equipe MM Criativos, funções e aprovações.
                        </p>
                    </a>
                @endif
            </div>

        </div>
    </div>
</x-app-layout>
