@extends('layouts.app')

@php
    $title = 'Clientes';
    $subTitle = 'Editar cliente';
@endphp

@section('content')
    <div class="grid grid-cols-1">
        @if (session('status'))
            <div class="mb-4 p-3 bg-green-100 text-green-800 rounded">{{ session('status') }}</div>
        @endif
        <div class="bg-white dark:bg-dark-800 overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 text-gray-900 dark:text-gray-100">
                @if ($plans->isEmpty())
                    <p class="text-gray-600">Nenhum plano cadastrado ainda.</p>
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full">
                            <thead>
                                <tr class="text-left text-gray-600 text-sm border-b">
                                    <th class="py-2 pr-4">Serviço</th>
                                    <th class="py-2 pr-4">Categoria</th>
                                    <th class="py-2 pr-4">Preço</th>
                                    <th class="py-2 pr-4">Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($plans as $plan)
                                    <tr class="border-b">
                                        <td class="py-3 pr-4">{{ $plan->service->name ?? '-' }}</td>
                                        <td class="py-3 pr-4">{{ $plan->category }}</td>
                                        <td class="py-3 pr-4">R$ {{ number_format($plan->price, 2, ',', '.') }}</td>
                                        <td class="py-3 pr-4 text-sm text-gray-600 dark:text-gray-300">
                                            <a href="{{ route('admin.commercial.budgets.create', ['plan_id' => $plan->id]) }}"
                                                class="inline-flex items-center px-4 py-2 bg-orange-600 text-white rounded hover:bg-orange-700">Criar
                                                orçamento</a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-4">{{ $plans->links() }}</div>
                @endif
            </div>
        </div>
    </div>
@endsection
