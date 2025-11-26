@php
    $title = 'Orçamentos';
    $subTitle = 'Veja como ficou seu orçamento';
@endphp

@extends('layouts.app')

@section('content')
    <div>
        <div class="grid grid-cols-1">
            @php($sym = ['BRL' => 'R$', 'USD' => '$', 'EUR' => '€'][$budget->currency] ?? $budget->currency)
            <div class="bg-white dark:bg-black overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                        <div>
                            <div class="text-orange-500 text-sm">Cliente</div>
                            <div class="font-medium">{{ $budget->client_name }}</div>
                            <div class="text-sm text-gray-600">{{ $budget->client_email }}</div>
                            @if ($budget->client_phone)
                                <div class="text-sm text-gray-600">{{ $budget->client_phone }}</div>
                            @endif
                        </div>
                        <div>
                            <div class="text-orange-500 text-sm">Serviço / Plano</div>
                            <div class="font-medium">{{ $budget->service->name ?? '-' }} @if ($budget->plan)
                                    — {{ $budget->plan->category }}
                                @endif
                            </div>
                            <div class="text-sm text-gray-600">Validade:
                                {{ optional($budget->valid_until)->format('d/m/Y') ?? '—' }}</div>
                        </div>
                    </div>

                    <h3 class="font-semibold mb-3">Itens</h3>
                    <div class="overflow-x-auto mb-6">
                        <table class="min-w-full">
                            <thead>
                                <tr class="text-left text-gray-600 text-sm border-b">
                                    <th class="py-2 pr-4">Nome</th>
                                    <th class="py-2 pr-4">Qtd</th>
                                    <th class="py-2 pr-4">Período</th>
                                    <th class="py-2 pr-4">Unitário ({{ $sym }})</th>
                                    <th class="py-2 pr-4">Desconto ({{ $sym }})</th>
                                    <th class="py-2 pr-4">Total ({{ $sym }})</th>
                                </tr>
                            </thead>
                            <tbody>
                                {{-- Produto (primeiro item) --}}
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
                                </tr>

                                {{-- Extras --}}
                                @foreach ($budget->items as $i)
                                    <tr class="border-b">
                                        <td class="py-3 pr-4">{{ $i->name }}</td>
                                        <td class="py-3 pr-4">{{ $i->qty }}</td>
                                        <td class="py-3 pr-4">
                                            {{ ['one_time' => 'Único', 'monthly' => 'Mensal', 'yearly' => 'Anual'][$i->billing_period] ?? $i->billing_period }}
                                        </td>
                                        <td class="py-3 pr-4">{{ $sym }}
                                            {{ number_format($i->unit_price, 2, ',', '.') }}</td>
                                        <td class="py-3 pr-4">{{ $sym }}
                                            {{ number_format($i->discount_amount, 2, ',', '.') }}</td>
                                        <td class="py-3 pr-4">{{ $sym }}
                                            {{ number_format($i->total, 2, ',', '.') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="flex items-center justify-between mb-3">
                        <h3 class="font-semibold">Totais</h3>
                        <form method="GET" action="{{ route('admin.commercial.budgets.preview', $budget) }}"
                            class="flex items-center gap-2">
                            <label class="text-sm text-gray-600">Parcelamento</label>
                            <select name="installments"
                                class="border-gray-300 rounded bg-[#f5f5f5] dark:bg-[#262626] dark:border-dark-600 dark:text-gray-200">
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
                        <div class="p-4 bg-[#f5f5f5] dark:bg-[#262626] rounded">
                            <div class="text-gray-900 text-sm">Preço produto</div>
                            <div class="text-2xl font-semibold">{{ $sym }}
                                {{ number_format($product, 2, ',', '.') }}</div>
                        </div>
                        <div class="p-4 bg-[#f5f5f5] dark:bg-[#262626] rounded">
                            <div class="text-gray-900 text-sm">Preço serviços</div>
                            <div class="text-2xl font-semibold">{{ $sym }}
                                {{ number_format($servicesTotal, 2, ',', '.') }}</div>
                        </div>
                        @if ($installments > 1)
                            <div class="p-4 bg-[#f5f5f5] dark:bg-[#262626] rounded">
                                <div class="text-gray-900 text-sm">Preço parcelado</div>

                                <div class="text-2xl font-bold">
                                    {{ $installments }}x {{ $sym }}
                                    {{ number_format($perInstallment, 2, ',', '.') }}
                                </div>

                                <div class="text-xs text-gray-900 mt-1">
                                    Total: {{ $sym }} {{ number_format($grandWithInterest, 2, ',', '.') }}
                                    @if ($ratePercent > 0)
                                        ({{ $ratePercent }}% juros)
                                    @else
                                        (sem juros)
                                    @endif
                                </div>
                            </div>
                        @else
                            <div class="p-4 bg-[#f5f5f5] dark:bg-[#262626] rounded">
                                <div class="text-gray-900 text-sm">Preço à vista</div>

                                <div class="text-2xl font-bold">
                                    {{ $sym }} {{ number_format($grandWithInterest, 2, ',', '.') }}
                                </div>
                            </div>
                        @endif

                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
