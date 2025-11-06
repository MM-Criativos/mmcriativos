<?php

namespace App\Listeners;

use App\Events\BudgetExpired;
use App\Services\EmailTemplateService;

class SendBudgetExpiredEmail
{
    public function handle(BudgetExpired $event): void
    {
        $budget = $event->budget;

        $to = $budget->client->email ?? $budget->client_email;
        if (!$to) {
            return;
        }

        $publicLink = route('budget.public', ['token' => $budget->public_token]);

        $vars = [
            'client_name' => $budget->client->name ?? $budget->client_name,
            'budget_id' => $budget->id,
            'public_link' => $publicLink,
            'valid_until' => optional($budget->valid_until)->format('d/m/Y'),
            'company_name' => config('app.name', 'MM Criativos'),
            'declined_at' => now()->format('d/m/Y'),
            'contact_link' => url('/contact'),
        ];

        EmailTemplateService::send('budget_expired', $to, $vars);
    }
}
