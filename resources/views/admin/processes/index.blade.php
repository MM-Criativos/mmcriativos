@php
    $title = 'Processos';
    $subTitle = 'Lista de processos';

    // TABS DO NAVBAR
    $navbarTabs = [
        [
            'label' => 'Dashboard',
            'icon' => 'fa-duotone fa-gears',
            'route' => route('admin.processes.index'),
            'active' => request()->routeIs('admin.processes.index'),
        ],
        [
            'label' => 'Criar Processo',
            'icon' => 'fa-duotone fa-wrench',
            'route' => route('admin.processes.create'),
            'active' => request()->routeIs('admin.processes.create'),
        ],
    ];
@endphp

@extends('layouts.app')

@section('content')
    <div>
        <div class="grid grid-cols-1">
            @if (session('status'))
                <div class="mb-4 p-3 bg-green-100 text-green-800 rounded">{{ session('status') }}</div>
            @endif
            <div class="bg-white dark:bg-black overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 space-y-6">

                    <div>
                        @if ($processes->isEmpty())
                            <div class="text-sm text-gray-600">Nenhum processo cadastrado ainda.</div>
                        @else
                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                                @foreach ($processes as $process)
                                    <div
                                        class="rounded-lg shadow-sm bg-[#f5f5f5] dark:bg-[#262626] overflow-hidden flex flex-col transition-transform hover:scale-[1.02] hover:shadow-md">
                                        <div class="p-5 flex-1 flex flex-col gap-4">
                                            <div class="flex items-start justify-between">
                                                <div>
                                                    <h3 class="font-semibold text-gray-800 text-lg">{{ $process->name }}
                                                    </h3>
                                                    <p class="text-xs text-gray-500">Slug: {{ $process->slug }}</p>
                                                </div>
                                                @php
                                                    $iconClasses = $process->icon_class ?? '';
                                                    $iconClasses = $iconClasses ?: 'fa-solid fa-diagram-project';
                                                @endphp
                                                <div class="text-orange-600 text-xl">
                                                    <i class="{{ $iconClasses }} icon-project fa-2x"></i>
                                                </div>
                                            </div>

                                            <div class="text-sm text-gray-600">
                                                <span class="font-medium text-gray-700">Ordem:</span>
                                                {{ $process->order ?? '—' }}
                                            </div>

                                            <div class="flex items-center justify-between mt-auto">
                                                <a href="{{ route('admin.processes.edit', $process) }}"
                                                    class="inline-flex items-center gap-1 px-4 py-2 rounded-md btn-mmcriativos">
                                                    <i class="fa-duotone fa-solid fa-pen-to-square mr-2 icon-project"></i>
                                                    <span>Editar</span>
                                                </a>
                                                <form method="POST"
                                                    action="{{ route('admin.processes.destroy', $process) }}"
                                                    onsubmit="return confirm('Tem certeza que deseja remover este processo?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit"
                                                        class="inline-flex items-center gap-1 px-4 py-3 rounded-md text-sm bg-red-500 text-white border-red-500 hover:bg-white dark:hover:bg-black hover:text-red-500">
                                                        <i class="fa-regular fa-trash-can"></i>
                                                    </button>
                                                </form>
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
