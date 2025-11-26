@php
    $title = 'Orçamentos';
    $subTitle = 'Faça ajustes no orçamento';
@endphp

@extends('layouts.app')

@section('content')
    <div>
        <div class="grid grid-cols-1">
            @php($sym = ['BRL' => 'R$', 'USD' => '$', 'EUR' => '€'][$budget->currency] ?? $budget->currency)
            @if (session('status'))
                <div class="p-3 bg-green-100 text-green-800 rounded">{{ session('status') }}</div>
            @endif
            @if ($errors->any())
                <div class="p-3 bg-red-100 text-red-800 rounded">
                    <ul class="list-disc pl-5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class=" bg-white dark:bg-black overflow-hidden shadow-sm sm:rounded-lg mb-5">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <form method="POST" action="{{ route('admin.commercial.budgets.update', $budget) }}" class="space-y-4">
                        @csrf
                        @method('PUT')

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm text-gray-600 mb-1">Cliente</label>
                                <select name="client_id"
                                    class="w-full border-gray-300 rounded dark:!bg-[#262626] dark:border-dark-600 dark:text-gray-200">
                                    <option value="">Selecione...</option>
                                    @foreach ($clients as $c)
                                        <option value="{{ $c->id }}" @selected(old('client_id', $budget->client_id) == $c->id)>
                                            {{ $c->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm text-gray-600 mb-1">Serviço</label>
                                <select name="service_id"
                                    class="w-full border-gray-300 rounded dark:!bg-[#262626] dark:border-dark-600 dark:text-gray-200">
                                    <option value="">Selecione...</option>
                                    @foreach ($services as $s)
                                        <option value="{{ $s->id }}" @selected(old('service_id', $budget->service_id) == $s->id)>
                                            {{ $s->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm text-gray-600 mb-1">Plano</label>
                                <select name="plan_id"
                                    class="w-full border-gray-300 rounded dark:!bg-[#262626] dark:border-dark-600 dark:text-gray-200">
                                    <option value="">Selecione...</option>
                                    @foreach ($plans as $p)
                                        <option value="{{ $p->id }}" @selected(old('plan_id', $budget->plan_id) == $p->id)>
                                            {{ $p->category }} — {{ $p->service->name ?? '-' }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm text-gray-600 mb-1">Nome</label>
                                <input name="client_name" value="{{ old('client_name', $budget->client_name) }}"
                                    class="w-full border-gray-300 rounded dark:!bg-[#262626] dark:border-dark-600 dark:text-gray-200" />
                            </div>
                            <div>
                                <label class="block text-sm text-gray-600 mb-1">E-mail</label>
                                <input type="email" name="client_email"
                                    value="{{ old('client_email', $budget->client_email) }}"
                                    class="w-full border-gray-300 rounded dark:!bg-[#262626] dark:border-dark-600 dark:text-gray-200" />
                            </div>
                            <div>
                                <label class="block text-sm text-gray-600 mb-1">Telefone</label>
                                <input name="client_phone" value="{{ old('client_phone', $budget->client_phone) }}"
                                    class="w-full border-gray-300 rounded dark:!bg-[#262626] dark:border-dark-600 dark:text-gray-200" />
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
                            <div>
                                <label class="block text-sm text-gray-600 mb-1">Moeda</label>
                                <select name="currency"
                                    class="w-full border-gray-300 rounded dark:!bg-[#262626] dark:border-dark-600 dark:text-gray-200">
                                    @php($curr = old('currency', $budget->currency))
                                    <option value="BRL" @selected($curr === 'BRL')>BRL (R$)</option>
                                    <option value="USD" @selected($curr === 'USD')>USD ($)</option>
                                    <option value="EUR" @selected($curr === 'EUR')>EUR (€)</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm text-gray-600 mb-1">Valor (a partir de)
                                    ({{ $sym }})</label>
                                <input type="number" step="0.01" name="base_price_snapshot"
                                    value="{{ old('base_price_snapshot', $budget->base_price_snapshot) }}"
                                    class="w-full border-gray-300 rounded dark:!bg-[#262626] dark:border-dark-600 dark:text-gray-200" />
                            </div>
                            <div>
                                <label class="block text-sm text-gray-600 mb-1">Desconto global
                                    ({{ $sym }})</label>
                                <input type="number" step="0.01" name="discount_amount"
                                    value="{{ old('discount_amount', $budget->discount_amount) }}"
                                    class="w-full border-gray-300 rounded dark:!bg-[#262626] dark:border-dark-600 dark:text-gray-200" />
                            </div>
                            <div>
                                <label class="block text-sm text-gray-600 mb-1">Validade</label>
                                <input type="date" name="valid_until"
                                    value="{{ old('valid_until', optional($budget->valid_until)->format('Y-m-d')) }}"
                                    class="w-full border-gray-300 rounded dark:!bg-[#262626] dark:border-dark-600 dark:text-gray-200" />
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm text-gray-600 mb-1">Observações</label>
                            <textarea name="notes" rows="4"
                                class="w-full border-gray-300 rounded dark:!bg-[#262626] dark:border-dark-600 dark:text-gray-200">{{ old('notes', $budget->notes) }}</textarea>
                        </div>

                        <div class="flex gap-2 justify-center">
                            <button class="px-5 py-3 rounded-md btn-mmcriativos">Salvar
                                alterações</button>
                            <a href="{{ route('admin.commercial.budgets.index') }}"
                                class="px-5 py-3 bg-red-500 text-white border-red-500 hover:bg-[#f5f5f5]
                                dark:hover:bg-[#262626] hover:text-red-500 hover:border-red-500 rounded-md">Voltar</a>
                        </div>
                    </form>
                </div>
            </div>

            <div class="bg-white dark:bg-black overflow-hidden shadow-sm sm:rounded-lg mb-5">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <h3 class="font-semibold mb-3">Itens</h3>
                    @php($sym = ['BRL' => 'R$', 'USD' => '$', 'EUR' => '€'][$budget->currency] ?? $budget->currency)
                    @if ($budget->items->isEmpty())
                        <p class="text-gray-600">Nenhum item adicionado ainda.</p>
                    @else
                        <div class="overflow-x-auto">
                            <table class="min-w-full">
                                <thead>
                                    <tr class="text-left text-gray-600 text-sm border-b">
                                        <th class="py-2 pr-4">Nome</th>
                                        <th class="py-2 pr-4">Qtd</th>
                                        <th class="py-2 pr-4">Período</th>
                                        <th class="py-2 pr-4">Unitário ({{ $sym }})</th>
                                        <th class="py-2 pr-4">Desconto ({{ $sym }})</th>
                                        <th class="py-2 pr-4">Total ({{ $sym }})</th>
                                        <th class="py-2 pr-4">Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {{-- Produto como primeira linha --}}
                                    @php($productName = $budget->plan ? $budget->plan->category . ' — ' . ($budget->service->name ?? '') : $budget->service->name ?? 'Produto')
                                    @php($productUnit = (float) $budget->base_price_snapshot)
                                    @php($productDiscount = (float) ($budget->discount_amount ?? 0))
                                    @php($productTotal = max($productUnit - $productDiscount, 0))
                                    <tr class="border-b">
                                        <td class="py-3 pr-4">{{ trim($productName) }}</td>
                                        <td class="py-3 pr-4">1</td>
                                        <td class="py-3 pr-4">Único</td>
                                        <td class="py-3 pr-4">{{ $sym }}
                                            {{ number_format($productUnit, 2, ',', '.') }}</td>
                                        <td class="py-3 pr-4">{{ $sym }}
                                            {{ number_format($productDiscount, 2, ',', '.') }}</td>
                                        <td class="py-3 pr-4">{{ $sym }}
                                            {{ number_format($productTotal, 2, ',', '.') }}</td>
                                        <td class="py-3 pr-4 text-sm text-gray-500">—</td>
                                    </tr>

                                    @foreach ($budget->items as $i)
                                        <tr class="border-b">
                                            <td class="py-3 pr-4">{{ $i->name }}</td>
                                            <td class="py-3 pr-4">
                                                <form method="POST"
                                                    action="{{ route('admin.commercial.budget-items.update', $i) }}"
                                                    class="flex items-center gap-2">
                                                    @csrf
                                                    @method('PUT')
                                                    <input type="number" min="1" name="qty"
                                                        value="{{ $i->qty }}"
                                                        class="w-20 border-gray-300 rounded bg-[#f5f5f5] dark:!bg-[#262626] dark:border-dark-600 dark:text-gray-200" />
                                                    <button class="px-3 py-3 rounded-md btn-mmcriativos">OK</button>
                                                </form>
                                            </td>
                                            <td class="py-3 pr-4">
                                                {{ ['one_time' => 'Único', 'monthly' => 'Mensal', 'yearly' => 'Anual'][$i->billing_period] ?? $i->billing_period }}
                                            </td>
                                            <td class="py-3 pr-4">{{ $sym }}
                                                {{ number_format($i->unit_price, 2, ',', '.') }}</td>
                                            <td class="py-3 pr-4">
                                                <form method="POST"
                                                    action="{{ route('admin.commercial.budget-items.update', $i) }}"
                                                    class="flex items-center gap-2">
                                                    @csrf
                                                    @method('PUT')
                                                    <input type="number" step="0.01" min="0"
                                                        name="discount_amount" value="{{ $i->discount_amount }}"
                                                        class="w-28 border-gray-300 rounded bg-[#f5f5f5] dark:!bg-[#262626] dark:border-dark-600 dark:text-gray-200" />
                                                    <button class="px-3 py-3 rounded-md btn-mmcriativos">OK</button>
                                                </form>
                                            </td>
                                            <td class="py-3 pr-4">{{ $sym }}
                                                {{ number_format($i->total, 2, ',', '.') }}</td>
                                            <td class="py-3 pr-4">
                                                <form method="POST"
                                                    action="{{ route('admin.commercial.budget-items.destroy', $i) }}"
                                                    onsubmit="return confirm('Remover este item?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button
                                                        class="px-4 py-3 bg-red-500 text-white border-red-500 hover:bg-[#f5f5f5]
                                                        dark:hover:bg-[#262626] hover:text-red-500 hover:border-red-500 rounded-md"><i
                                                            class="fa-solid fa-trash"></i></button>
                                                </form>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif

                    {{-- Adicionar extra --}}
                    <div class="mt-4">
                        <form method="POST" action="{{ route('admin.commercial.budgets.items.extra', $budget) }}"
                            class="flex flex-wrap items-end gap-3">
                            @csrf
                            <div>
                                <label class="block text-sm text-gray-600 mb-1">Adicionar extra</label>
                                <select name="extra_id"
                                    class="border-gray-300 rounded bg-[#f5f5f5] dark:!bg-[#262626] dark:border-dark-600 dark:text-gray-200">
                                    @forelse(($extras ?? []) as $ex)
                                        <option value="{{ $ex->id }}">{{ $ex->name }} —
                                            {{ $ex->billing_period }}</option>
                                    @empty
                                        <option value="">Nenhum extra disponível</option>
                                    @endforelse
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm text-gray-600 mb-1">Qtd</label>
                                <input type="number" min="1" name="qty" value="1"
                                    class="w-24 border-gray-300 rounded bg-[#f5f5f5] dark:!bg-[#262626] dark:border-dark-600 dark:text-gray-200" />
                            </div>
                            <div>
                                <button class="px-4 py-2 rounded-md btn-mmcriativos">Adicionar</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-black overflow-hidden shadow-sm sm:rounded-lg mb-5">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <div class="flex items-center justify-between mb-3">
                        <h3 class="font-semibold">Totais</h3>
                        <form method="GET" action="{{ route('admin.commercial.budgets.edit', $budget) }}"
                            class="flex items-center gap-2">
                            <label class="text-sm text-gray-600">Parcelamento</label>
                            <select name="installments"
                                class="border-gray-300 rounded bg-[#f5f5f5] dark:!bg-[#262626] dark:border-dark-600 dark:text-gray-200">
                                @foreach ($installmentRates ?? [] as $n => $rate)
                                    @php($label = $n . 'x' . ($rate > 0 ? ' (' . $rate . '%)' : ' (sem juros)'))
                                    <option value="{{ $n }}" @selected(($installments ?? 1) == $n)>{{ $label }}
                                    </option>
                                @endforeach
                            </select>
                            <button class="px-3 py-2 rounded-md btn-mmcriativos">Aplicar</button>
                        </form>
                    </div>
                    @php($servicesTotal = 0.0)
                    @foreach ($budget->items as $it)
                        @php($servicesTotal += (float) $it->total * ($it->billing_period === 'monthly' ? 12 : 1))
                    @endforeach
                    @php($product = max((float) $budget->base_price_snapshot - (float) ($budget->discount_amount ?? 0), 0))
                    @php($grand = $product + $servicesTotal)
                    @php($ratePercent = $installmentRates[$installments] ?? 0)
                    @php($grandWithInterest = round($grand * (1 + $ratePercent / 100), 2))
                    @php($perInstallment = round($grandWithInterest / max($installments, 1), 2))

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div class="p-4 bg-[#f5f5f5] dark:!bg-[#262626] rounded">
                            <div class="text-gray-900 text-sm">Preço produto</div>
                            <div class="text-2xl font-semibold">{{ $sym }}
                                {{ number_format($product, 2, ',', '.') }}</div>
                        </div>
                        <div class="p-4 bg-[#f5f5f5] dark:!bg-[#262626] rounded">
                            <div class="text-gray-900 text-sm">Preço serviços</div>
                            <div class="text-2xl font-semibold">{{ $sym }}
                                {{ number_format($servicesTotal, 2, ',', '.') }}</div>
                        </div>
                        <div class="p-4 bg-[#f5f5f5] dark:!bg-[#262626] rounded">
                            <div class="text-gray-900 text-sm">Preço total</div>
                            <div class="text-2xl font-semibold">{{ $sym }}
                                {{ number_format($grandWithInterest, 2, ',', '.') }}</div>
                            <div class="text-xs text-gray-900 mt-1">{{ $installments ?? 1 }}x de {{ $sym }}
                                {{ number_format($perInstallment, 2, ',', '.') }} @if (($ratePercent ?? 0) > 0)
                                    ({{ $ratePercent }}% juros)
                                @else
                                    (sem juros)
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-black overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <h3 class="font-semibold mb-3">Enviar por e-mail</h3>
                    <form method="POST" action="{{ route('admin.commercial.budgets.send-email', $budget) }}"
                        class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        @csrf
                        <div>
                            <label class="block text-sm text-gray-600 mb-1">Para</label>
                            <input type="email" name="to" value="{{ $budget->client_email }}"
                                class="w-full bg-[#f5f5f5] dark:!bg-[#262626] border-gray-300 rounded" />
                        </div>
                        <div class="flex items-end">
                            <button class="px-5 py-2 btn-mmcriativos rounded-md">Enviar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
