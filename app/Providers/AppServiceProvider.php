<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Event;
use App\Events\BudgetSent;
use App\Events\BudgetOpened;
use App\Events\BudgetAccepted;
use App\Events\BudgetDeclined;
use App\Events\BudgetExpired;
use App\Listeners\RegisterBudgetEvent;
use App\Listeners\UpdateBudgetStatus;
use App\Listeners\CreateProjectOnBudgetAccepted;
use App\Listeners\SendBudgetAcceptedEmail;
use App\Listeners\SendBudgetExpiredEmail;
use App\Listeners\SendBudgetDeclinedEmail;
use App\Listeners\SendBudgetOpenedNotification;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
        // Força HTTPS em desenvolvimento local e produção
        if (app()->environment('production') || (bool) env('FORCE_HTTPS', false)) {
            URL::forceScheme('https');
        }

        // Garantir esquema do APP_URL e forçar root apenas em produção
        if (config('app.url')) {
            $appUrl = config('app.url');

            // Forçar a raiz completa somente em produção (ou quando explicitamente habilitado)
            if (app()->environment('production') || (bool) env('FORCE_ROOT_URL', false)) {
                URL::forceRootUrl($appUrl);
            }
        }

        // Register event listeners for budget lifecycle
        foreach ([BudgetSent::class, BudgetAccepted::class, BudgetDeclined::class, BudgetExpired::class] as $evt) {
            Event::listen($evt, [RegisterBudgetEvent::class, 'handle']);
            Event::listen($evt, [UpdateBudgetStatus::class, 'handle']);
        }
        Event::listen(BudgetOpened::class, [RegisterBudgetEvent::class, 'handle']);
        Event::listen(BudgetOpened::class, [UpdateBudgetStatus::class, 'handle']);
        Event::listen(BudgetOpened::class, [SendBudgetOpenedNotification::class, 'handle']);

        Event::listen(BudgetAccepted::class, [CreateProjectOnBudgetAccepted::class, 'handle']);
        Event::listen(BudgetAccepted::class, [SendBudgetAcceptedEmail::class, 'handle']);
        Event::listen(BudgetDeclined::class, [SendBudgetDeclinedEmail::class, 'handle']);
        Event::listen(BudgetExpired::class, [SendBudgetExpiredEmail::class, 'handle']);
    }
}
