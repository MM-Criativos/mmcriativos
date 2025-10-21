<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Painel Administrativo') }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                <a href="{{ route('admin.services.index') }}"
                    class="block bg-white hover:bg-gray-50 border rounded-lg p-6 shadow-sm">
                    <div class="text-lg font-semibold">ServiÃ§os</div>
                    <p class="text-sm text-gray-600">Gerencie serviÃ§os, infos, benefÃ­cios, caracterÃ­sticas, processos e
                        CTAs.</p>
                </a>
                <a href="{{ route('admin.skills.index') }}"
                    class="block bg-white border rounded-lg p-6 shadow-sm opacity-80">
                    <div class="text-lg font-semibold">Habilidades</div>
                    <p class="text-sm text-gray-600">Defina e gerencie as habilidades que a MM Criativos possui, por
                        Ã¡rea e tecnologia.</p>
                </a>
                <a href="{{ route('admin.projects.index') }}"
                    class="block bg-white border rounded-lg p-6 shadow-sm opacity-80">
                    <div class="text-lg font-semibold">Projetos</div>
                    <p class="text-sm text-gray-600">Organize projetos com clientes, serviÃ§os, equipe e materiais
                        visuais.</p>
                </a>
                <a href="{{ route('admin.clients.index') }}"
                    class="block bg-white border rounded-lg p-6 shadow-sm opacity-80">
                    <div class="text-lg font-semibold">Clientes</div>
                    <p class="text-sm text-gray-600">Gerencie clientes, logotipos, contatos e informaÃ§Ãµes comerciais.
                    </p>
                </a>
                <a href="{{ route('admin.testimonials.index') }}"
                    class="block bg-white border rounded-lg p-6 shadow-sm opacity-80">
                    <div class="text-lg font-semibold">Depoimentos</div>
                    <p class="text-sm text-gray-600">Gerencie depoimentos de clientes e parceiros que fortalecem a
                        marca.</p>
                </a>
                @if (Auth::user()->role === 'admin')
                <a href="{{ route('admin.team.index') }}" class="block bg-white border rounded-lg p-6 shadow-sm opacity-80">
                    <div class="text-lg font-semibold">Equipe</div>
                    <p class="text-sm text-gray-600">Gerencie a equipe MM Criativos, funções e aprovações.</p>
                </a>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>

