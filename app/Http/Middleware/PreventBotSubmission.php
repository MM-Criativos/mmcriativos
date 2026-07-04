<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Honeypot + time-trap contra bots de cadastro automatizado.
 * Campo "website" só é preenchido por bots (fica fora da tela pro usuário).
 * "form_ts" registra quando o form foi renderizado; submit abaixo do
 * mínimo humano de preenchimento é tratado como bot.
 */
class PreventBotSubmission
{
    private const HONEYPOT_FIELD = 'website';

    private const TIMESTAMP_FIELD = 'form_ts';

    private const MIN_SECONDS = 3;

    public function handle(Request $request, Closure $next): Response
    {
        if (filled($request->input(self::HONEYPOT_FIELD))) {
            return $this->reject($request);
        }

        $renderedAt = (int) $request->input(self::TIMESTAMP_FIELD);

        if ($renderedAt <= 0 || (time() - $renderedAt) < self::MIN_SECONDS) {
            return $this->reject($request);
        }

        return $next($request);
    }

    private function reject(Request $request): Response
    {
        return redirect()
            ->back()
            ->withInput($request->except('password', 'password_confirmation'))
            ->withErrors(['email' => 'Não foi possível processar sua solicitação. Tente novamente.']);
    }
}
