<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use App\Services\ExchangeRateHttpService;
use App\Models\ExchangeRate;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('exchange:update', function () {
    /** @var ExchangeRateHttpService $svc */
    $svc = app(ExchangeRateHttpService::class);
    $rates = $svc->update(['USD','EUR']);

    if (empty($rates)) {
        $this->error('Não foi possível atualizar as cotações agora.');
        return 1;
    }

    foreach ($rates as $cur => $rate) {
        $this->info("{$cur} -> BRL = {$rate}");
    }
    $this->info('Exchange rates atualizados com sucesso.');
    return 0;
})->purpose('Atualiza as taxas de câmbio (USD/EUR para BRL)');
