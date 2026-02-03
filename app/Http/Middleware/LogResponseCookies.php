<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class LogResponseCookies
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        $cookies = $response->headers->getCookies();
        $names = array_map(fn($cookie) => $cookie->getName(), $cookies);
        Log::info('Response cookies', ['count' => count($cookies), 'names' => $names]);

        return $response;
    }
}
