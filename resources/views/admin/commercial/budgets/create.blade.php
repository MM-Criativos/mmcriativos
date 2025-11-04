<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Novo Orçamento</h2>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            @include('admin.commercial._tabs')
            @if ($errors->any())
                <div class="mb-4 p-3 bg-red-100 text-red-800 rounded">
                    <ul class="list-disc pl-5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="bg-white dark:bg-dark-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <form method="POST" action="{{ route('admin.commercial.budgets.store') }}" class="space-y-4">
                        @csrf

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm text-gray-600 mb-1">Cliente</label>
                                <select name="client_id"
                                    class="w-full border-gray-300 rounded dark:bg-dark-700 dark:border-dark-600 dark:text-gray-200">
                                    <option value="">Selecione...</option>
                                    @foreach ($clients as $c)
                                        <option value="{{ $c->id }}" @selected(old('client_id') == $c->id)>
                                            {{ $c->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm text-gray-600 mb-1">Serviço</label>
                                <select name="service_id"
                                    class="w-full border-gray-300 rounded dark:bg-dark-700 dark:border-dark-600 dark:text-gray-200">
                                    <option value="">Selecione...</option>
                                    @foreach ($services as $s)
                                        <option value="{{ $s->id }}" @selected(old('service_id') == $s->id)>
                                            {{ $s->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm text-gray-600 mb-1">Plano (opcional)</label>
                                <select name="plan_id"
                                    class="w-full border-gray-300 rounded dark:bg-dark-700 dark:border-dark-600 dark:text-gray-200">
                                    <option value="">Selecione...</option>
                                    @foreach ($plans as $p)
                                        <option value="{{ $p->id }}" @selected(old('plan_id', request('plan_id')) == $p->id)>
                                            {{ $p->category }} — {{ $p->service->name ?? '-' }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm text-gray-600 mb-1">Moeda</label>
                                <select name="currency"
                                    class="w-full border-gray-300 rounded dark:bg-dark-700 dark:border-dark-600 dark:text-gray-200">
                                    @php($curr = old('currency', 'BRL'))
                                    <option value="BRL" @selected($curr === 'BRL')>BRL (R$)</option>
                                    <option value="USD" @selected($curr === 'USD')>USD ($)</option>
                                    <option value="EUR" @selected($curr === 'EUR')>EUR (€)</option>
                                </select>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm text-gray-600 mb-1">Nome do cliente</label>
                                <input name="client_name" value="{{ old('client_name') }}"
                                    class="w-full border-gray-300 rounded dark:bg-dark-700 dark:border-dark-600 dark:text-gray-200"
                                    required />
                            </div>
                            <div>
                                <label class="block text-sm text-gray-600 mb-1">E-mail do cliente</label>
                                <input type="email" name="client_email" value="{{ old('client_email') }}"
                                    class="w-full border-gray-300 rounded dark:bg-dark-700 dark:border-dark-600 dark:text-gray-200"
                                    required />
                            </div>
                            <div>
                                <label class="block text-sm text-gray-600 mb-1">Telefone</label>
                                <input name="client_phone" value="{{ old('client_phone') }}"
                                    class="w-full border-gray-300 rounded dark:bg-dark-700 dark:border-dark-600 dark:text-gray-200" />
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            @php($symbol = ['BRL' => 'R$', 'USD' => '$', 'EUR' => '€'][old('currency', 'BRL')] ?? '')
                            <div>
                                @php($symbol = ['BRL' => 'R$', 'USD' => '$', 'EUR' => '€'][old('currency', 'BRL')] ?? '')
                                <label class="block text-sm text-gray-600 mb-1">Valor (a partir de)
                                    ({{ $symbol }})</label>
                                <input type="number" step="0.01" name="base_price_snapshot"
                                    value="{{ old('base_price_snapshot') }}"
                                    class="w-full border-gray-300 rounded dark:bg-dark-700 dark:border-dark-600 dark:text-gray-200" />
                            </div>

                            <div>
                                <label class="block text-sm text-gray-600 mb-1">Desconto global
                                    ({{ $symbol }})</label>
                                <input type="number" step="0.01" name="discount_amount"
                                    value="{{ old('discount_amount', 0) }}"
                                    class="w-full border-gray-300 rounded dark:bg-dark-700 dark:border-dark-600 dark:text-gray-200" />
                            </div>

                            <div>
                                <label class="block text-sm text-gray-600 mb-1">Validade</label>
                                <input type="date" name="valid_until" value="{{ old('valid_until') }}"
                                    class="w-full border-gray-300 rounded dark:bg-dark-700 dark:border-dark-600 dark:text-gray-200" />
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm text-gray-600 mb-1">Observações</label>
                            <textarea name="notes" rows="4"
                                class="w-full border-gray-300 rounded dark:bg-dark-700 dark:border-dark-600 dark:text-gray-200">{{ old('notes') }}</textarea>
                        </div>

                        <div class="flex gap-2">
                            <button class="px-5 py-3 bg-orange-600 text-white rounded hover:bg-orange-700">Criar
                                orçamento</button>
                            <a href="{{ route('admin.commercial.budgets.index') }}"
                                class="px-5 py-3 bg-gray-500 text-white rounded hover:bg-gray-600 dark:bg-gray-500 dark:hover:bg-gray-400">Cancelar</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
