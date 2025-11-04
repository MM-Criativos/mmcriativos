<?php

namespace App\Services;

use App\Models\ExchangeRate;

class ExchangeRateService
{
    public function rateToBRL(string $currency): float
    {
        $currency = strtoupper($currency);
        if ($currency === 'BRL') {
            return 1.0;
        }

        $latest = ExchangeRate::where('currency', $currency)
            ->orderByDesc('fetched_at')
            ->orderByDesc('id')
            ->value('rate_to_brl');

        if ($latest) {
            return (float) $latest;
        }

        $rates = config('exchange.rates', []);
        return (float) ($rates[$currency] ?? 1.0);
    }

    public function toBRL(float $amount, string $currency): float
    {
        return round($amount * $this->rateToBRL($currency), 2);
    }
}
