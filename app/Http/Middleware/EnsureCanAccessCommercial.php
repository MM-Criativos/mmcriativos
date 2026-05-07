<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureCanAccessCommercial
{
    /**
     * Permite acesso apenas a usuários com role 'admin' ou 'comercial'.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || ! in_array($user->role, ['admin', 'comercial'], true)) {
            abort(403, 'Acesso restrito à área comercial.');
        }

        return $next($request);
    }
}
