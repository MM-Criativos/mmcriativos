<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureCanAccessProspeccao
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || ! in_array($user->role, ['admin', 'comercial', 'user'], true)) {
            abort(403, 'Acesso restrito a area de prospeccao.');
        }

        return $next($request);
    }
}
