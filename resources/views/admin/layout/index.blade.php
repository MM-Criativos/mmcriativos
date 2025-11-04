<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-100 leading-tight">Layout</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                        <a href="{{ route('admin.layout.slider.edit') }}"
                            class="block bg-white dark:bg-dark-800 border rounded-lg p-6 shadow-sm hover:bg-gray-50 dark:hover:bg-dark-700">
                            <div class="text-lg font-semibold">Slider</div>
                            <p class="text-sm text-gray-600">Gerencie o vídeo de fundo e os três textos do herói.</p>
                        </a>
                        <a href="{{ route('admin.layout.lines.edit') }}"
                            class="block bg-white dark:bg-dark-800 border rounded-lg p-6 shadow-sm hover:bg-gray-50 dark:hover:bg-dark-700">
                            <div class="text-lg font-semibold">Linhas</div>
                            <p class="text-sm text-gray-600">Edite as frases do bloco deslizante.</p>
                        </a>
                        <a href="{{ route('admin.layout.price.edit') }}"
                            class="block bg-white dark:bg-dark-800 border rounded-lg p-6 shadow-sm hover:bg-gray-50 dark:hover:bg-dark-700">
                            <div class="text-lg font-semibold">Preços</div>
                            <p class="text-sm text-gray-600">Gerencie valores, descrições e vantagens dos planos.</p>
                        </a>
                        <a href="{{ route('admin.layout.aboutus.edit') }}"
                            class="block bg-white dark:bg-dark-800 border rounded-lg p-6 shadow-sm hover:bg-gray-50 dark:hover:bg-dark-700">
                            <div class="text-lg font-semibold">Sobre nós</div>
                            <p class="text-sm text-gray-600">Atualize imagem, título, subtítulo e descrição.</p>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

