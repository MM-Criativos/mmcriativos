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

        $headers = $response->headers->getCookies();
        $names = array_map(fn($cookie) => $cookie->getName(), $headers);
        Log::debug('Response cookies', ['count' => count($headers), 'names' => $names]);

        return $response;
    }
}
