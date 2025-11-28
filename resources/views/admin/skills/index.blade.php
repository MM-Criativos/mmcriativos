<style>
    .navbar-header {
        margin-top: -20px !important;
    }
</style>

@php
    $title = 'Habilidades';
    $subTitle = 'Como criamos nossos projetos';
@endphp

@extends('layouts.app')

@section('content')
    <div>
        <div class="grid grid-cols-1">
            @if (session('status'))
                <div class="mb-4 p-3 bg-green-100 text-green-800 rounded">{{ session('status') }}</div>
            @endif
            <div class="card h-full p-0 rounded-xl border-0 overflow-hidden">
                <div
                    class="card-header border-b border-neutral-200 dark:border-neutral-600 bg-gradient-to-r from-orange-500 to-transparent py-4 px-6 flex items-center flex-wrap gap-3 justify-between">
                    <div class="flex items-center flex-wrap gap-2">
                        <h3 class="text-lg font-semibold text-gray-800 dark:text-white mb-0">Nossas habilidades</h3>
                    </div>
                    <a href="{{ route('admin.skills.create') }}"
                        class="btn btn-mmcriativos text-sm btn-sm px-3 py-3 rounded-lg flex items-center gap-2">
                        <i class="fa-duotone fa-solid fa-circle-plus icon-project mr-2"></i>
                        Adicionar Habilidade
                    </a>
                </div>
                <div class="bg-white dark:bg-black overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        @if ($skills->isEmpty())
                            <p class="text-gray-600 dark:text-gray-300">Nenhuma skill cadastrada ainda.</p>
                        @else
                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 pb-2">

                                @foreach ($skills as $skill)
                                    <div class="user-grid-card">
                                        <div
                                            class="bg-[#f5f5f5] dark:bg-[#262626] relative border border-neutral-200 dark:border-neutral-600 rounded-2xl overflow-hidden">

                                            {{-- Banner superior --}}
                                            @if ($skill->thumb)
                                                <img src="{{ asset($skill->thumb) }}" alt="{{ $skill->name }}"
                                                    class="w-full object-cover h-28">
                                            @else
                                                <img src="{{ asset('admin/images/user-grid/user-grid-bg1.png') }}"
                                                    class="w-full object-cover h-28 opacity-60">
                                            @endif

                                            {{-- Ícone da Skill (avatar redondo) --}}
                                            @if ($skill->icon)
                                                <div
                                                    class="w-[80px] h-[80px] flex items-center justify-center
                                           ms-auto me-auto -mt-[40px]
                                           bg-white dark:bg-black
                                           border border-gray-300 dark:border-neutral-700
                                           rounded-full shadow-sm mb-5
                                           relative z-[5]">
                                                    <i class="{{ $skill->icon }} text-orange-500 text-4xl"></i>
                                                </div>
                                            @else
                                                <div
                                                    class="w-[80px] h-[80px] flex items-center justify-center
                                           ms-auto me-auto -mt-[40px]
                                           bg-gray-200 dark:bg-[#181818]
                                           text-gray-400 rounded-full border border-gray-300 mb-5
                                           relative z-[5]">
                                                    <i class="fa-regular fa-image text-2xl"></i>
                                                </div>
                                            @endif

                                            {{-- Nome da Skill --}}
                                            <div class="pe-6 pb-4 ps-6 text-center relative z-[1]">
                                                <h6 class="text-lg mb-1 text-gray-900 dark:text-gray-100">
                                                    {{ $skill->name }}
                                                </h6>

                                                {{-- Botão Editar --}}
                                                <div class="mt-6 flex justify-center">
                                                    <a href="{{ route('admin.skills.edit', $skill) }}"
                                                        class="btn-mmcriativos inline-flex items-center gap-2 px-6 py-3 rounded-md">
                                                        <i class="fa-duotone fa-solid fa-pen-to-square icon-project"></i>
                                                        Editar Skill
                                                    </a>
                                                </div>

                                            </div>

                                        </div>
                                    </div>
                                @endforeach

                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
