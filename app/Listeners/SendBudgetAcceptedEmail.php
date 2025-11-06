<?php

namespace App\Listeners;

use App\Events\BudgetAccepted;
use App\Services\EmailTemplateService;

class SendBudgetAcceptedEmail
{
    public function handle(BudgetAccepted $event): void
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
            'accepted_at' => now()->format('d/m/Y'),
            'public_link' => $publicLink,
            'valid_until' => optional($budget->valid_until)->format('d/m/Y'),
            'company_name' => config('app.name', 'MM Criativos'),
        ];

        EmailTemplateService::send('budget_accepted', $to, $vars);
    }
}
