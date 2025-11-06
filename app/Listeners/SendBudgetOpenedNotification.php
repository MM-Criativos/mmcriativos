<?php

namespace App\Listeners;

use App\Events\BudgetOpened;
use App\Models\EmailTemplate;
use App\Services\EmailTemplateService;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;

class SendBudgetOpenedNotification
{
    public function handle(BudgetOpened $event): void
    {
        $budget = $event->budget;

        $to = Config::get('mail.sales_notification', 'comercial@mmcriativos.com.br');
        if (!$to) {
            return;
        }

        $template = EmailTemplate::where('key', 'budget_opened')->first();
        if (!$template) {
            Log::warning('Email template "budget_opened" não encontrado.');
            return;
        }

        $vars = [
            'client_name' => $budget->client->name ?? $budget->client_name,
            'budget_id' => $budget->id,
            'public_link' => route('budget.public', ['token' => $budget->public_token]),
            'admin_link' => route('admin.commercial.budgets.edit', $budget),
            'company_name' => config('app.name', 'MM Criativos'),
        ];

        EmailTemplateService::send('budget_opened', $to, $vars);
    }
}
