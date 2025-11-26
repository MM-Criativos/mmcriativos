@php
    $title = 'Orçamentos';
    $subTitle = 'Lista de orçamentos enviados';
@endphp

@extends('layouts.app')

@section('content')
    <div>
        <div class="grid grid-cols-1">
            @if (session('status'))
                <div class="mb-4 p-3 bg-green-100 text-green-800 rounded">{{ session('status') }}</div>
            @endif

            <div class="bg-white dark:bg-black overflow-hidden shadow-sm sm:rounded-lg">

                {{-- Linha 1 – Título / Descrição + Botão (col-12) --}}
                <div class="px-6 py-6 border-b border-neutral-300 dark:border-neutral-800 flex items-center justify-between">

                    {{-- Título + descrição --}}
                    <div>
                        <h6 class="text-lg font-semibold text-neutral-800 dark:text-white">
                            Orçamentos criados
                        </h6>
                        <p class="text-sm text-neutral-500 dark:text-neutral-400 mt-1">
                            Orçamentos que foram enviados a nossos clientes
                        </p>
                    </div>

                    {{-- Botão --}}
                    <a href="{{ route('admin.commercial.budgets.create') }}"
                        class="inline-flex items-center gap-2 px-4 py-2 rounded-md btn-mmcriativos">

                        <i class="fa-duotone fa-solid fa-circle-plus icon-project"></i>
                        Novo Orçamento
                    </a>
                </div>

                {{-- Linha 2 – Filtros + Tabela (col-12) --}}
                <div class="p-6 text-gray-900 dark:text-gray-100">

                    {{-- Filtros --}}
                    {{-- Filtros --}}
                    <form method="GET" class="mb-6">

                        <div class="grid grid-cols-1 md:grid-cols-5 gap-4">

                            {{-- Status --}}
                            <div class="md:col-span-1">
                                <label class="block text-sm text-gray-600 dark:text-gray-300 mb-1">Status</label>
                                <select name="status"
                                    class="w-full border-gray-300 dark:border-neutral-700 dark:bg-neutral-800 rounded">
                                    <option value="">Todos</option>
                                    @foreach ([
            'draft' => 'Rascunho',
            'sent' => 'Enviado',
            'opened' => 'Aberto',
            'accepted' => 'Aceito',
            'declined' => 'Recusado',
            'expired' => 'Expirado',
        ] as $k => $v)
                                        <option value="{{ $k }}" @selected(request('status') === $k)>
                                            {{ $v }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Cliente --}}
                            <div class="md:col-span-1">
                                <label class="block text-sm text-gray-600 dark:text-gray-300 mb-1">Cliente</label>
                                <select name="client_id"
                                    class="w-full border-gray-300 dark:border-neutral-700 dark:bg-neutral-800 rounded">
                                    <option value="">Todos</option>
                                    @foreach ($clients as $c)
                                        <option value="{{ $c->id }}" @selected(request('client_id') == $c->id)>
                                            {{ $c->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Serviço --}}
                            <div class="md:col-span-1">
                                <label class="block text-sm text-gray-600 dark:text-gray-300 mb-1">Serviço</label>
                                <select name="service_id"
                                    class="w-full border-gray-300 dark:border-neutral-700 dark:bg-neutral-800 rounded">
                                    <option value="">Todos</option>
                                    @foreach ($services as $s)
                                        <option value="{{ $s->id }}" @selected(request('service_id') == $s->id)>
                                            {{ $s->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Botões --}}
                            <div class="md:col-span-1 flex items-end gap-3">

                                <button class="px-4 py-2 rounded-md btn-mmcriativos">
                                    Filtrar
                                </button>

                                <a href="{{ route('admin.commercial.budgets.index') }}"
                                    class="px-4 py-2 bg-red-500 text-white border-red-500 hover:bg-[#f5f5f5]
                       dark:hover:bg-[#262626] hover:text-red-500 hover:border-red-500 rounded-md">
                                    Limpar
                                </a>
                            </div>

                        </div>

                    </form>


                    {{-- Tabela --}}
                    @if ($budgets->isEmpty())
                        <p class="text-gray-600 dark:text-gray-400">Nenhum orçamento encontrado.</p>
                    @else
                        <div class="overflow-x-auto w-full">
                            <table class="w-full table-auto">
                                <thead>
                                    <tr
                                        class="text-left text-gray-600 dark:text-gray-400 text-sm border-b border-neutral-300 dark:border-neutral-700">
                                        <th class="py-2 pr-4">#</th>
                                        <th class="py-2 pr-4">Cliente</th>
                                        <th class="py-2 pr-4">Serviço</th>
                                        <th class="py-2 pr-4">Plano</th>
                                        <th class="py-2 pr-4">Total</th>
                                        <th class="py-2 pr-4">Status</th>
                                        <th class="py-2 pr-4">Ações</th>
                                    </tr>
                                </thead>

                                <tbody>
                                    @foreach ($budgets as $b)
                                        <tr class="border-b border-neutral-200 dark:border-neutral-800">

                                            <td class="py-3 pr-4 text-gray-700 dark:text-gray-300">{{ $b->id }}</td>
                                            <td class="py-3 pr-4">{{ $b->client_name }}</td>
                                            <td class="py-3 pr-4">{{ $b->service->name ?? '-' }}</td>
                                            <td class="py-3 pr-4">{{ $b->plan->category ?? '-' }}</td>

                                            @php($currencySymbols = ['BRL' => 'R$', 'USD' => '$', 'EUR' => '€'])
                                            @php($sym = $currencySymbols[$b->currency] ?? $b->currency)
                                            @php($displayTotal = optional($b->selectedPayment)->total_with_interest ?? $b->grand_total)

                                            <td class="py-3 pr-4">
                                                {{ $sym }}
                                                {{ number_format((float) $displayTotal, 2, ',', '.') }}
                                            </td>

                                            <td class="py-3 pr-4">{!! $b->statusBadge() !!}</td>

                                            <td class="py-3 pr-4 text-sm flex gap-3">
                                                <a href="{{ route('admin.commercial.budgets.edit', $b) }}"
                                                    class="inline-flex items-center px-4 py-3 rounded-lg btn-mmcriativos">
                                                    <i class="fa-duotone fa-solid fa-pen-to-square icon-project"></i>
                                                </a>

                                                <a href="{{ route('admin.commercial.budgets.preview', $b) }}"
                                                    target="_blank"
                                                    class="inline-flex items-center px-4 py-3 rounded-lg btn-mmcriativos">
                                                    <i class="fa-duotone fa-solid fa-eye icon-project"></i>
                                                </a>

                                                <form method="POST"
                                                    action="{{ route('admin.commercial.budgets.destroy', $b) }}"
                                                    onsubmit="return confirm('Excluir este orçamento? Esta ação não pode ser desfeita.');">
                                                    @csrf
                                                    @method('DELETE')

                                                    <button
                                                        class="inline-flex items-center px-5 py-4 bg-red-500 text-white border-red-500 hover:bg-[#f5f5f5] dark:hover:bg-[#262626] hover:text-red-500 hover:border-red-500 rounded-lg">
                                                        <i class="fa-solid fa-trash"></i>
                                                    </button>
                                                </form>
                                            </td>

                                        </tr>
                                    @endforeach
                                </tbody>

                            </table>
                        </div>

                        {{-- Paginação --}}
                        <div class="mt-4">
                            {{ $budgets->links() }}
                        </div>
                    @endif

                </div>

            </div>

        </div>
    </div>
@endsection
