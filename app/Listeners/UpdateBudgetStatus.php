<?php

namespace App\Listeners;

use App\Events\BudgetAccepted;
use App\Events\BudgetDeclined;
use App\Events\BudgetExpired;
use App\Events\BudgetOpened;
use App\Events\BudgetSent;

class UpdateBudgetStatus
{
    public function handle(object $event): void
    {
        $budget = $event->budget ?? null;
        if (!$budget) {
            return;
        }

        $status = match (true) {
            $event instanceof BudgetSent => 'sent',
            $event instanceof BudgetOpened => 'opened',
            $event instanceof BudgetAccepted => 'accepted',
            $event instanceof BudgetDeclined => 'declined',
            $event instanceof BudgetExpired => 'expired',
            default => $budget->status,
        };

        if ($status !== $budget->status) {
            $budget->status = $status;
            $budget->save();
        }
    }
}
