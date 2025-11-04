<?php

namespace App\Services;

use App\Models\ExchangeRate;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;

class ExchangeRateHttpService
{
    public function update(array $currencies = ['USD','EUR']): array
    {
        $provider = config('exchange.provider', 'frankfurter');

        $rates = [];
        if ($provider === 'awesomeapi') {
            $map = $this->fetchFromAwesomeApi($currencies);
        } else {
            $map = $this->fetchFromFrankfurter($currencies);
        }

        $now = now();
        foreach ($map as $currency => $rate) {
            if (!is_null($rate)) {
                ExchangeRate::create([
                    'currency' => $currency,
                    'rate_to_brl' => $rate,
                    'fetched_at' => $now,
                ]);
                $rates[$currency] = (float) $rate;
            }
        }

        return $rates;
    }

    protected function fetchFromFrankfurter(array $currencies): array
    {
        // API: https://api.frankfurter.app/latest?from=USD&to=BRL
        $out = [];
        foreach ($currencies as $cur) {
            try {
                $res = Http::timeout(8)->get('https://api.frankfurter.app/latest', [
                    'from' => strtoupper($cur),
                    'to' => 'BRL',
                ]);
                if ($res->successful()) {
                    $json = $res->json();
                    $out[$cur] = (float) Arr::get($json, 'rates.BRL');
                }
            } catch (\Throwable $e) {
                $out[$cur] = null;
            }
        }
        return $out;
    }

    protected function fetchFromAwesomeApi(array $currencies): array
    {
        // API: https://economia.awesomeapi.com.br/last/USD-BRL,EUR-BRL
        $pairs = collect($currencies)->map(fn($c) => strtoupper($c).'-BRL')->implode(',');
        try {
            $res = Http::timeout(8)->get('https://economia.awesomeapi.com.br/last/'.$pairs);
            if (!$res->successful()) return [];
            $json = $res->json();
        } catch (\Throwable $e) {
            return [];
        }

        $out = [];
        foreach ($currencies as $cur) {
            $key = strtoupper($cur).'BRL';
            $rate = Arr::get($json, "$key.bid");
            $out[$cur] = $rate ? (float) $rate : null;
        }
        return $out;
    }
}

