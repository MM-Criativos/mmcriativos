<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Http\Request;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Support\Facades\Log;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        api: __DIR__ . '/../routes/api.php',
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Confia em todos os proxies (Traefik/EasyPanel).
        // Sem isso o Laravel nÃ£o lÃª X-Forwarded-Proto e detecta scheme como
        // HTTP, o que pode invalidar o cookie de sessÃ£o e causar erro 419.
        $middleware->trustProxies(at: '*');
        $middleware->validateCsrfTokens(except: [
            'mmcloud/tenants',
            'mmcloud/tenants/*',
            'mmcloud/tenants/*/instances',
            'admin/mmcloud/prospeccao',
            'admin/mmcloud/prospeccao/*',
        ]);

        // Aliases customizados
        $middleware->alias([
            'approved'       => \App\Http\Middleware\EnsureUserIsApproved::class,
            'vee.internal'   => \App\Http\Middleware\EnsureVeeInternalToken::class,
            'can.commercial' => \App\Http\Middleware\EnsureCanAccessCommercial::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->report(function (TokenMismatchException $exception): void {
            /** @var Request|null $request */
            $request = request();

            if (! $request instanceof Request) {
                return;
            }

            $sessionCookieName = (string) config('session.cookie');
            $sessionCookieValue = (string) $request->cookie($sessionCookieName, '');
            $inputToken = (string) $request->input('_token', '');
            $headerCsrfToken = (string) $request->header('X-CSRF-TOKEN', '');
            $headerXsrfToken = (string) $request->header('X-XSRF-TOKEN', '');
            $sessionToken = $request->hasSession() ? (string) $request->session()->token() : '';

            $tokenHash = static function (string $token): ?string {
                return $token !== '' ? hash('sha256', $token) : null;
            };

            Log::warning('CSRF token mismatch detected', [
                'method' => $request->method(),
                'full_url' => $request->fullUrl(),
                'host' => $request->getHost(),
                'scheme' => $request->getScheme(),
                'referer' => $request->headers->get('referer'),
                'origin' => $request->headers->get('origin'),
                'user_id' => optional($request->user())->id,
                'session_cookie_name' => $sessionCookieName,
                'has_session_cookie' => $sessionCookieValue !== '',
                'session_cookie_hash' => $tokenHash($sessionCookieValue),
                'has_session' => $request->hasSession(),
                'session_id' => $request->hasSession() ? $request->session()->getId() : null,
                'session_token_hash' => $tokenHash($sessionToken),
                'input_token_hash' => $tokenHash($inputToken),
                'header_csrf_token_hash' => $tokenHash($headerCsrfToken),
                'header_xsrf_token_hash' => $tokenHash($headerXsrfToken),
                'app_url' => config('app.url'),
                'session_domain' => config('session.domain'),
                'session_secure' => config('session.secure'),
                'session_same_site' => config('session.same_site'),
                'x_forwarded_proto' => $request->headers->get('x-forwarded-proto'),
                'x_forwarded_host' => $request->headers->get('x-forwarded-host'),
                'x_forwarded_for' => $request->headers->get('x-forwarded-for'),
            ]);
        });
    })
    ->withSchedule(function (Schedule $schedule) {
        $schedule->command('exchange:update')->dailyAt('06:00');
        $schedule->command('vee:platform-health --suite=daily')->dailyAt('06:10');
        $schedule->command('vee:platform-health --suite=weekly')->sundays()->at('06:30');
    })
    ->create();

