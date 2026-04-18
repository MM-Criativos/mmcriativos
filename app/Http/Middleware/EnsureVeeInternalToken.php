<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureVeeInternalToken
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $expectedToken = trim((string) config('services.vee.internal_token'));
        if ($expectedToken === '') {
            return response()->json([
                'message' => 'VEE internal token is not configured.',
            ], 503);
        }

        $providedToken = (string) ($request->header('X-VEE-Internal-Token') ?? '');

        if (! hash_equals($expectedToken, $providedToken)) {
            return response()->json([
                'message' => 'Unauthorized.',
            ], 401);
        }

        return $next($request);
    }
}
