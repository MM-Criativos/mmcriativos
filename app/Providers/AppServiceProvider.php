<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Event;
use App\Events\BudgetSent;
use App\Events\BudgetOpened;
use App\Events\BudgetAccepted;
use App\Events\BudgetDeclined;
use App\Listeners\RegisterBudgetEvent;
use App\Listeners\UpdateBudgetStatus;

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
        // Register event listeners for budget lifecycle
        foreach ([BudgetSent::class, BudgetOpened::class, BudgetAccepted::class, BudgetDeclined::class] as $evt) {
            Event::listen($evt, [RegisterBudgetEvent::class, 'handle']);
            Event::listen($evt, [UpdateBudgetStatus::class, 'handle']);
        }
    }
}
