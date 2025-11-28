@php
    $title = 'Depoimentos';
    $subTitle = 'Depoimentos de nossos clientes';
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
                        <h3 class="text-lg font-semibold text-gray-800 dark:text-white mb-0">Depoimento de nossos clientes
                        </h3>
                    </div>
                    <a href="{{ route('admin.testimonials.create') }}"
                        class="btn btn-mmcriativos text-sm btn-sm px-3 py-3 rounded-lg flex items-center gap-2">
                        <i class="fa-duotone fa-solid fa-circle-plus icon-project mr-2"></i>
                        Adicionar Depoimento
                    </a>
                </div>
                <div class="bg-white dark:bg-black overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        @if ($testimonials->isEmpty())
                            <p class="text-gray-600">Nenhum depoimento cadastrado ainda.</p>
                        @else
                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                                @foreach ($testimonials as $testimonial)
                                    <div
                                        class="rounded-lg shadow-sm bg-[#f5f5f5] dark:bg-dark-800 p-4 flex items-start gap-3">
                                        <div class="shrink-0">
                                            <img src="{{ $testimonial->photo_url }}"
                                                alt="{{ $testimonial->contact?->name ?? 'Contato' }}"
                                                style="width:125px;height:125px;border-radius:10px;object-fit:cover;">
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <h3 class="font-medium text-gray-800 truncate">
                                                {{ $testimonial->contact?->name ?? 'Contato' }}</h3>
                                            <p class="text-sm !text-orange-500 truncate">
                                                {{ $testimonial->author_position ?? $testimonial->contact?->role }}
                                                @if ($testimonial->client)
                                                    — {{ $testimonial->client->name }}
                                                @endif
                                            </p>
                                            @if ($testimonial->title)
                                                <p class="mt-1 text-sm text-gray-600 truncate">“{{ $testimonial->title }}”
                                                </p>
                                            @endif
                                            <div class="mt-3 flex items-center gap-2">
                                                <a href="{{ route('admin.testimonials.edit', $testimonial) }}"
                                                    class="inline-flex items-center gap-1 px-5 py-3 btn-mmcriativos rounded-md">
                                                    <i class="fa-duotone fa-pen-to-square icon-project mr-2"></i> Editar
                                                </a>
                                                <form method="POST"
                                                    action="{{ route('admin.testimonials.destroy', $testimonial) }}"
                                                    onsubmit="return confirm('Remover este depoimento?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit"
                                                        class="inline-flex items-center gap-1 px-5 py-4 rounded bg-red-500 text-white border-red-500 hover:bg-white dark:hover:bg-black hover:text-red-500 hover:border-red-500">
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
