@php
    $title = 'Serviços Extras';
    $subTitle = 'Serviços adicionais aos projetos';
@endphp

@extends('layouts.app')

@section('content')
    <div>
        <div class="grid grid-cols-1">
            @if (session('status'))
                <div class="mb-4 p-3 bg-green-100 text-green-800 rounded">{{ session('status') }}</div>
            @endif

            <div class="bg-white dark:bg-black overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <form method="GET" class="mb-4">
                        <label class="block text-sm text-gray-600 mb-1">Filtrar por serviço</label>

                        <div class="flex items-center justify-between w-full">

                            {{-- 🔹 ESQUERDA: Select + Filtrar + Limpar --}}
                            <div class="flex gap-2">
                                <select name="service_id"
                                    class="border-gray-300 rounded dark:bg-dark-700 dark:border-dark-600 dark:text-gray-200">
                                    <option value="">Todos</option>
                                    @foreach ($services as $s)
                                        <option value="{{ $s->id }}" @selected((string) request('service_id') === (string) $s->id)>
                                            {{ $s->name }}
                                        </option>
                                    @endforeach
                                </select>

                                <button class="px-4 py-2 rounded-md btn-mmcriativos">Filtrar</button>

                                <a href="{{ route('admin.commercial.extras.index') }}"
                                    class="px-4 py-2 rounded-md bg-red-500 text-white border-red-500 hover:bg-white dark:hover:bg-black hover:text-red-500">
                                    Limpar
                                </a>
                            </div>

                            {{-- 🔸 DIREITA: Botão Novo Extra --}}
                            <a href="{{ route('admin.commercial.extras.create') }}"
                                class="px-4 py-2 rounded-md btn-mmcriativos inline-flex items-center gap-2">
                                <i class="fa-duotone fa-solid fa-circle-plus icon-project"></i>
                                Novo Extra
                            </a>

                        </div>
                    </form>

                    @if ($extras->isEmpty())
                        <p class="text-gray-600">Nenhum extra encontrado.</p>
                    @else
                        <div class="overflow-x-auto">
                            <table class="min-w-full">
                                <thead>
                                    <tr class="text-left text-gray-600 text-sm border-b">
                                        <th class="py-2 pr-4">Nome</th>
                                        <th class="py-2 pr-4">Período</th>
                                        <th class="py-2 pr-4">Preço</th>
                                        <th class="py-2 pr-4">Ativo</th>
                                        <th class="py-2 pr-4">Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($extras as $e)
                                        <tr class="border-b">
                                            <td class="py-3 pr-4">{{ $e->name }}</td>
                                            <td class="py-3 pr-4">{{ $e->billing_period }}</td>
                                            <td class="py-3 pr-4">
                                                @if ($e->price_type === 'percent')
                                                    {{ $e->price }}%
                                                @else
                                                    R$ {{ number_format($e->price, 2, ',', '.') }}
                                                @endif
                                            </td>
                                            <td class="py-3 pr-4">{{ $e->is_active ? 'Sim' : 'Não' }}</td>
                                            <td class="py-3 pr-4 text-sm flex gap-3">
                                                <a href="{{ route('admin.commercial.extras.edit', $e) }}"
                                                    class="inline-flex items-center px-3 py-2 rounded-md btn-mmcriativos">Editar</a>
                                                <form method="POST"
                                                    action="{{ route('admin.commercial.extras.destroy', $e) }}"
                                                    onsubmit="return confirm('Remover extra?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button
                                                        class="inline-flex items-center rounded-md px-4 py-3 bg-red-500 text-white border-red-500 hover:bg-white dark:hover:bg-black hover:text-red-500">
                                                        <i class="fa-regular fa-trash-can"></i>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-4">{{ $extras->links() }}</div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
