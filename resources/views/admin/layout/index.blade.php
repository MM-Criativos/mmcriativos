@php
    $title = 'Layout';
    $subTitle = 'Ajuste aspectos do site';
@endphp

@extends('layouts.app')

@section('content')
    <div>
        <div class="grid grid-cols-1">
            <div class="bg-white dark:bg-black overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                        <a href="{{ route('admin.layout.slider.edit') }}"
                            class="block bg-[#f5f5f5] dark:bg-dark-800 rounded-lg p-6 shadow-sm hover:bg-gray-50 dark:hover:bg-dark-700">
                            <div class="text-orange-500 text-lg font-semibold">Slider</div>
                            <p class="text-sm text-gray-600">Gerencie o vídeo de fundo e os três textos do herói.</p>
                        </a>
                        <a href="{{ route('admin.layout.lines.edit') }}"
                            class="block bg-[#f5f5f5] dark:bg-dark-800 rounded-lg p-6 shadow-sm hover:bg-gray-50 dark:hover:bg-dark-700">
                            <div class="text-orange-500 text-lg font-semibold">Linhas</div>
                            <p class="text-sm text-gray-600">Edite as frases do bloco deslizante.</p>
                        </a>
                        <a href="{{ route('admin.layout.aboutus.edit') }}"
                            class="block bg-[#f5f5f5] dark:bg-dark-800 rounded-lg p-6 shadow-sm hover:bg-gray-50 dark:hover:bg-dark-700">
                            <div class="text-orange-500 text-lg font-semibold">Sobre nós</div>
                            <p class="text-sm text-gray-600">Atualize imagem, título, subtítulo e descrição.</p>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
