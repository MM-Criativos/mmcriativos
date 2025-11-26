@php
    $title = 'Orçamentos';
    $subTitle = 'Crie um novo orçamento comercial';
@endphp

@extends('layouts.app')

@section('content')
    <div>
        <div class="grid grid-cols-1">

            <div class="bg-white dark:bg-black overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <form method="POST" action="{{ route('admin.commercial.budgets.store') }}" class="space-y-4">
                        @csrf

                        <!-- 🧩 Linha 1 -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm text-gray-600 dark:text-gray-300 mb-1">Cliente</label>
                                <select name="client_id"
                                    class="w-full appearance-none bg-[#f5f5f5] dark:bg-[#262626] border border-gray-300 dark:border-dark-600 text-gray-800 dark:text-gray-200 rounded px-3 py-2 focus:ring-2 focus:ring-orange-500 focus:border-orange-500">
                                    <option value="">Selecione...</option>
                                    @foreach ($clients as $c)
                                        <option value="{{ $c->id }}" @selected(old('client_id') == $c->id)>
                                            {{ $c->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm text-gray-600 dark:text-gray-300 mb-1">Serviço</label>
                                <select name="service_id"
                                    class="w-full appearance-none bg-[#f5f5f5] dark:bg-[#262626] border border-gray-300 dark:border-dark-600 text-gray-800 dark:text-gray-200 rounded px-3 py-2 focus:ring-2 focus:ring-orange-500 focus:border-orange-500">
                                    <option value="">Selecione...</option>
                                    @foreach ($services as $s)
                                        <option value="{{ $s->id }}" @selected(old('service_id') == $s->id)>
                                            {{ $s->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <!-- 🧩 Linha 2 -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm text-gray-600 dark:text-gray-300 mb-1">Plano
                                    (opcional)</label>
                                <select name="plan_id"
                                    class="w-full appearance-none bg-[#f5f5f5] dark:bg-[#262626] border border-gray-300 dark:border-dark-600 text-gray-800 dark:text-gray-200 rounded px-3 py-2 focus:ring-2 focus:ring-orange-500 focus:border-orange-500">
                                    <option value="">Selecione...</option>
                                    @foreach ($plans as $p)
                                        <option value="{{ $p->id }}" @selected(old('plan_id', request('plan_id')) == $p->id)>
                                            {{ $p->category }} — {{ $p->service->name ?? '-' }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm text-gray-600 dark:text-gray-300 mb-1">Moeda</label>
                                <select name="currency"
                                    class="w-full appearance-none bg-[#f5f5f5] dark:bg-[#262626] border border-gray-300 dark:border-dark-600 text-gray-800 dark:text-gray-200 rounded px-3 py-2 focus:ring-2 focus:ring-orange-500 focus:border-orange-500">
                                    @php($curr = old('currency', 'BRL'))
                                    <option value="BRL" @selected($curr === 'BRL')>BRL (R$)</option>
                                    <option value="USD" @selected($curr === 'USD')>USD ($)</option>
                                    <option value="EUR" @selected($curr === 'EUR')>EUR (€)</option>
                                </select>
                            </div>
                        </div>

                        <!-- 🧩 Linha 3 -->
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm text-gray-600 dark:text-gray-300 mb-1">Nome do
                                    cliente</label>
                                <input type="text" name="client_name" value="{{ old('client_name') }}"
                                    class="w-full appearance-none bg-[#f5f5f5] dark:!bg-[#262626] border border-gray-300 dark:border-dark-600 text-gray-800 dark:text-gray-200 rounded px-3 py-2 focus:ring-2 focus:ring-orange-500 focus:border-orange-500"
                                    required />
                            </div>
                            <div>
                                <label class="block text-sm text-gray-600 dark:text-gray-300 mb-1">E-mail do
                                    cliente</label>
                                <input type="email" name="client_email" value="{{ old('client_email') }}"
                                    class="w-full appearance-none bg-[#f5f5f5] dark:!bg-[#262626] border border-gray-300 dark:border-dark-600 text-gray-800 dark:text-gray-200 rounded px-3 py-2 focus:ring-2 focus:ring-orange-500 focus:border-orange-500"
                                    required />
                            </div>
                            <div>
                                <label class="block text-sm text-gray-600 dark:text-gray-300 mb-1">Telefone</label>
                                <input type="text" name="client_phone" value="{{ old('client_phone') }}"
                                    class="w-full appearance-none bg-[#f5f5f5] dark:!bg-[#262626] border border-gray-300 dark:border-dark-600 text-gray-800 dark:text-gray-200 rounded px-3 py-2 focus:ring-2 focus:ring-orange-500 focus:border-orange-500" />
                            </div>
                        </div>

                        <!-- 🧩 Linha 4 -->
                        @php($symbol = ['BRL' => 'R$', 'USD' => '$', 'EUR' => '€'][old('currency', 'BRL')] ?? '')
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm text-gray-600 dark:text-gray-300 mb-1">Valor (a partir de)
                                    ({{ $symbol }})</label>
                                <input type="number" step="0.01" name="base_price_snapshot"
                                    value="{{ old('base_price_snapshot') }}"
                                    class="w-full appearance-none bg-[#f5f5f5] dark:!bg-[#262626] border border-gray-300 dark:border-dark-600 text-gray-800 dark:text-gray-200 rounded px-3 py-2 focus:ring-2 focus:ring-orange-500 focus:border-orange-500" />
                            </div>

                            <div>
                                <label class="block text-sm text-gray-600 dark:text-gray-300 mb-1">Desconto global
                                    ({{ $symbol }})</label>
                                <input type="number" step="0.01" name="discount_amount"
                                    value="{{ old('discount_amount', 0) }}"
                                    class="w-full appearance-none bg-[#f5f5f5] dark:!bg-[#262626] border border-gray-300 dark:border-dark-600 text-gray-800 dark:text-gray-200 rounded px-3 py-2 focus:ring-2 focus:ring-orange-500 focus:border-orange-500" />
                            </div>

                            <div>
                                <label class="block text-sm text-gray-600 dark:text-gray-300 mb-1">Validade</label>
                                <input type="date" name="valid_until" value="{{ old('valid_until') }}"
                                    class="w-full appearance-none bg-[#f5f5f5] dark:bg-[#262626] border border-gray-300 dark:border-dark-600 text-gray-800 dark:text-gray-200 rounded px-3 py-2 focus:ring-2 focus:ring-orange-500 focus:border-orange-500" />
                            </div>
                        </div>

                        <!-- 🧩 Observações -->
                        <div>
                            <label class="block text-sm text-gray-600 dark:text-gray-300 mb-1">Observações</label>
                            <textarea name="notes" rows="4"
                                class="w-full appearance-none bg-[#f5f5f5] dark:bg-[#262626] border border-gray-300 dark:border-dark-600 text-gray-800 dark:text-gray-200 rounded px-3 py-2 focus:ring-2 focus:ring-orange-500 focus:border-orange-500">{{ old('notes') }}</textarea>
                        </div>

                        <!-- 🧩 Botões -->
                        <div class="flex gap-2 justify-center mt-5">
                            <button type="submit" class="px-5 py-3 rounded-md btn-mmcriativos">
                                Criar orçamento
                            </button>
                            <a href="{{ route('admin.commercial.budgets.index') }}"
                                class="px-5 py-3 bg-red-500 text-white border-red-500 hover:bg-[#f5f5f5]
                                dark:hover:bg-[#262626] hover:text-red-500 hover:border-red-500 rounded-md">
                                Cancelar
                            </a>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </div>
    </div>
@endsection
