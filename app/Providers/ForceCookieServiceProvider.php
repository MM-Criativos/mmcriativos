<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Event;
use Illuminate\Http\Response;

class ForceCookieServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Event::listen('kernel.handled', function ($request, $response) {
            if ($response instanceof Response) {
                $response->headers->setCookie(
                    cookie(
                        'FORCE_SESSION_TEST',
                        '1',
                        120,
                        '/',
                        null,
                        true,
                        true,
                        false,
                        'Lax'
                    )
                );
            }
        });
    }
}
