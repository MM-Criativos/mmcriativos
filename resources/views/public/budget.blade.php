<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Orçamento #{{ $budget->id }} • MM Criativos</title>
    @vite(['resources/css/app.css','resources/js/app.js'])
</head>
<body class="bg-gray-100 text-gray-900">
<div class="min-h-screen flex flex-col items-center py-10">
    <div class="w-full max-w-3xl bg-white shadow sm:rounded-lg p-6">
        <div class="flex items-center justify-between mb-4">
            <h1 class="text-xl font-semibold">Orçamento #{{ $budget->id }}</h1>
            <span class="text-sm text-gray-500">{{ ucfirst($budget->status) }}</span>
        </div>

        @if (session('status'))
            <div class="mb-4 p-3 bg-green-100 text-green-800 rounded">{{ session('status') }}</div>
        @endif

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
            <div>
                <div class="text-gray-500 text-sm">Cliente</div>
                <div class="font-medium">{{ $budget->client_name }}</div>
                <div class="text-sm text-gray-600">{{ $budget->client_email }}</div>
                @if($budget->client_phone)
                    <div class="text-sm text-gray-600">{{ $budget->client_phone }}</div>
                @endif
            </div>
            <div>
                <div class="text-gray-500 text-sm">Serviço / Plano</div>
                <div class="font-medium">{{ $budget->service->name ?? '-' }} @if($budget->plan) — {{ $budget->plan->category }} @endif</div>
                <div class="text-sm text-gray-600">Validade: {{ optional($budget->valid_until)->format('d/m/Y') ?? '—' }}</div>
            </div>
        </div>

        @php($symbols = ['BRL'=>'R$','USD'=>'$','EUR'=>'€'])
        @php($sym = $symbols[$budget->currency] ?? $budget->currency)

        <h2 class="font-semibold mb-2">Itens</h2>
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
                @php($productName = $budget->plan ? $budget->plan->category . ' — ' . ($budget->service->name ?? '') : $budget->service->name ?? 'Produto')
                @php($productUnit = (float) $budget->base_price_snapshot)
                @php($productDiscount = (float) ($budget->discount_amount ?? 0))
                @php($productTotal = max($productUnit - $productDiscount, 0))
                <tr class="border-b">
                    <td class="py-3 pr-4">{{ trim($productName) }}</td>
                    <td class="py-3 pr-4">1</td>
                    <td class="py-3 pr-4">Único</td>
                    <td class="py-3 pr-4">{{ $sym }} {{ number_format($productUnit, 2, ',', '.') }}</td>
                    <td class="py-3 pr-4">{{ $sym }} {{ number_format($productDiscount, 2, ',', '.') }}</td>
                    <td class="py-3 pr-4">{{ $sym }} {{ number_format($productTotal, 2, ',', '.') }}</td>
                </tr>

                @forelse ($budget->items as $item)
                    @php($periodMap = ['one_time' => 'Único', 'monthly' => 'Mensal', 'yearly' => 'Anual'])
                    <tr class="border-b">
                        <td class="py-3 pr-4">{{ $item->name }}</td>
                        <td class="py-3 pr-4">{{ $item->qty }}</td>
                        <td class="py-3 pr-4">{{ $periodMap[$item->billing_period] ?? $item->billing_period }}</td>
                        <td class="py-3 pr-4">{{ $sym }} {{ number_format($item->unit_price, 2, ',', '.') }}</td>
                        <td class="py-3 pr-4">{{ $sym }} {{ number_format($item->discount_amount, 2, ',', '.') }}</td>
                        <td class="py-3 pr-4">{{ $sym }} {{ number_format($item->total, 2, ',', '.') }}</td>
                    </tr>
                @empty
                    <tr><td class="py-3 pr-4 text-gray-600" colspan="6">Nenhum extra listado.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>

        @php($servicesTotal = 0.0)
        @foreach ($budget->items as $item)
            @php($servicesTotal += (float) $item->total * ($item->billing_period === 'monthly' ? 12 : 1))
        @endforeach
        @php($product = max((float) $budget->base_price_snapshot - (float) ($budget->discount_amount ?? 0), 0))
        @php($grand = $product + $servicesTotal)

        @php($ratePercent = $installmentRates[$installments] ?? 0)
        @php($grandWithInterest = round($grand * (1 + $ratePercent / 100), 2))
        @php($perInstallment = round($grandWithInterest / max($installments, 1), 2))

        <div class="flex items-center justify-between mb-3">
            <h2 class="font-semibold">Totais</h2>
            <form method="GET" action="{{ route('budget.public', $budget->public_token) }}" class="flex items-center gap-2">
                <label class="text-sm text-gray-600">Parcelamento</label>
                <select name="installments" class="border-gray-300 rounded">
                    @foreach ($installmentRates ?? [] as $n => $rate)
                        @php($label = $n . 'x' . ($rate > 0 ? ' (' . $rate . '%)' : ' (sem juros)'))
                        <option value="{{ $n }}" @selected(($installments ?? 1) == $n)>{{ $label }}</option>
                    @endforeach
                </select>
                <button class="px-3 py-2 bg-gray-500 text-white rounded hover:bg-gray-600">Aplicar</button>
            </form>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
            <div class="p-4 bg-gray-50 rounded">
                <div class="text-gray-500 text-sm">Preço produto</div>
                <div class="text-2xl font-semibold">{{ $sym }} {{ number_format($product, 2, ',', '.') }}</div>
            </div>
            <div class="p-4 bg-gray-50 rounded">
                <div class="text-gray-500 text-sm">Preço serviços</div>
                <div class="text-2xl font-semibold">{{ $sym }} {{ number_format($servicesTotal, 2, ',', '.') }}</div>
            </div>
            @if ($installments > 1)
                <div class="p-4 bg-gray-50 rounded">
                    <div class="text-gray-500 text-sm">Preço parcelado</div>
                    <div class="text-2xl font-bold">{{ $installments }}x {{ $sym }} {{ number_format($perInstallment, 2, ',', '.') }}</div>
                    <div class="text-xs text-gray-500 mt-1">
                        Total: {{ $sym }} {{ number_format($grandWithInterest, 2, ',', '.') }}
                        @if ($ratePercent > 0)
                            ({{ $ratePercent }}% juros)
                        @else
                            (sem juros)
                        @endif
                    </div>
                </div>
            @else
                <div class="p-4 bg-gray-50 rounded">
                    <div class="text-gray-500 text-sm">Preço à vista</div>
                    <div class="text-2xl font-bold">{{ $sym }} {{ number_format($grandWithInterest, 2, ',', '.') }}</div>
                </div>
            @endif
        </div>

        @if ($isExpired ?? false)
            <div class="p-4 bg-red-50 text-red-600 rounded">
                Este orçamento expirou em {{ optional($budget->valid_until)->format('d/m/Y') ?? 'data não informada' }}.
                Entre em contato com nossa equipe para solicitar uma nova proposta.
            </div>
        @else
            <div class="flex flex-wrap gap-3">
                <a href="{{ route('budget.accept', ['token' => $budget->public_token, 'installments' => $installments]) }}" class="px-5 py-3 bg-green-600 text-white rounded">Aprovar orçamento</a>
                <a href="{{ route('budget.decline', ['token' => $budget->public_token, 'installments' => $installments]) }}" class="px-5 py-3 bg-red-600 text-white rounded">Recusar orçamento</a>
            </div>
        @endif
    </div>
    <p class="mt-6 text-xs text-gray-500">MM Criativos</p>
</div>
</body>
</html>
