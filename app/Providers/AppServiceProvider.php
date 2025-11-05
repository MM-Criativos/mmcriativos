<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Event;
use App\Events\BudgetSent;
use App\Events\BudgetOpened;
use App\Events\BudgetAccepted;
use App\Events\BudgetDeclined;
use App\Listeners\RegisterBudgetEvent;
use App\Listeners\UpdateBudgetStatus;
use App\Listeners\CreateProjectOnBudgetAccepted;

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
        // Garantir que o gerador de URL use o APP_URL (evita assinatura inválida por host/esquema)
        if (config('app.url')) {
            URL::forceRootUrl(config('app.url'));
        }

        // Register event listeners for budget lifecycle
        foreach ([BudgetSent::class, BudgetOpened::class, BudgetAccepted::class, BudgetDeclined::class] as $evt) {
            Event::listen($evt, [RegisterBudgetEvent::class, 'handle']);
            Event::listen($evt, [UpdateBudgetStatus::class, 'handle']);
        }

        Event::listen(BudgetAccepted::class, [CreateProjectOnBudgetAccepted::class, 'handle']);
    }
}
