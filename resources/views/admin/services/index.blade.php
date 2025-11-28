@php
    $title = 'Serviços';
    $subTitle = 'Serviços que a MM Criativos oferece';
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
                        <h3 class="text-lg font-semibold text-gray-800 dark:text-white mb-0">Nossos serviços</h3>
                    </div>
                    <a href="{{ route('admin.services.create') }}"
                        class="btn btn-mmcriativos text-sm btn-sm px-3 py-3 rounded-lg flex items-center gap-2">
                        <i class="fa-duotone fa-solid fa-circle-plus icon-project mr-2"></i>
                        Adicionar Serviço
                    </a>
                </div>
                <div class="card-body p-6">
                    @if ($services->isEmpty())
                        <p class="text-gray-600 dark:text-gray-300">Nenhum serviço cadastrado ainda.</p>
                    @else
                        <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-6">

                            @foreach ($services as $service)
                                <div class="user-grid-card">
                                    <div
                                        class="bg-white dark:bg-black relative border border-neutral-200 dark:border-neutral-600 rounded-2xl overflow-hidden">

                                        {{-- Banner superior --}}
                                        @if ($service->thumb)
                                            <img src="{{ asset($service->thumb) }}" alt="{{ $service->name }}"
                                                class="w-full object-cover h-28">
                                        @else
                                            <img src="{{ asset('admin/images/user-grid/user-grid-bg1.png') }}"
                                                class="w-full object-cover h-28 opacity-60">
                                        @endif

                                        {{-- Avatar / Ícone --}}
                                        <div class="pe-6 pb-4 ps-6 text-center mt--50">

                                            @if ($service->icon)
                                                <div
                                                    class="w-[80px] h-[80px] flex items-center justify-center
                                                    ms-auto me-auto -mt-[40px]
                                                    bg-[#f5f5f5] dark:bg-dark-800
                                                    border border-gray-300 dark:border-neutral-600
                                                    rounded-full mb-5
                                                    relative z-[5]">
                                                    <i class="{{ $service->icon }} text-orange-500 text-4xl"></i>
                                                </div>
                                            @else
                                                <div
                                                    class="w-[80px] h-[80px] flex items-center justify-center
                                                    ms-auto me-auto -mt-[40px]
                                                    bg-[#f5f5f5] dark:bg-dark-800 text-gray-400
                                                    rounded-full border border-gray-300 mb-5
                                                    relative z-[5]">
                                                    <i class="fa-regular fa-image text-2xl"></i>
                                                </div>
                                            @endif


                                            {{-- Nome --}}
                                            <h6 class="text-lg mb-1 text-gray-900 dark:text-gray-100">
                                                {{ $service->name }}
                                            </h6>

                                            {{-- Categoria / Subtítulo opcional --}}
                                            @if ($service->category)
                                                <span class="text-secondary-light mb-4 block">
                                                    {{ $service->category }}
                                                </span>
                                            @endif

                                            {{-- Botão de Editar Serviço --}}
                                            <div class="mt-5 mb-3 flex justify-center gap-2">
                                                <a href="{{ route('admin.services.edit', $service) }}"
                                                    class="btn-mmcriativos inline-flex items-center gap-2 px-6 py-2.5 rounded-md">
                                                    <i class="fa-duotone fa-solid fa-pen-to-square icon-project"></i>
                                                    Editar
                                                </a>
                                                <form method="POST"
                                                    action="{{ route('admin.services.destroy', $service) }}"
                                                    onsubmit="return confirm('Tem certeza que deseja apagar este serviço?');">
                                                    @csrf
                                                    @method('DELETE')

                                                    <button type="submit"
                                                        class="delete-btn w-full text-start px-5 py-4 rounded bg-red-500 text-white border-red-500 hover:bg-[#f5f5f5] dark:hover:bg-[#262626] hover:text-red-500 hover:border-red-500 gap-2">
                                                        <i class="fa-regular fa-trash"></i>
                                                    </button>
                                                </form>
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

@endsection
