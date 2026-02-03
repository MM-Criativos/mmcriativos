<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
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
        /**
         * ⚠️ IMPORTANTE
         * HTTPS, esquema e domínio são responsabilidade do PROXY (Traefik).
         * Não force URL::forceScheme nem URL::forceRootUrl aqui.
         */

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
