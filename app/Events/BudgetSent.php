<?php

namespace App\Events;

use App\Models\Budget;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class BudgetSent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Budget $budget)
    {
    }
}
