<?php

return [
    'provider' => env('EXCHANGE_PROVIDER', 'frankfurter'), // frankfurter|awesomeapi

    // Base: BRL — valores indicativos; ajuste via .env (fallback)
    'rates' => [
        'USD' => (float) env('EXCHANGE_USD', 5.00),
        'EUR' => (float) env('EXCHANGE_EUR', 5.50),
    ],
];
