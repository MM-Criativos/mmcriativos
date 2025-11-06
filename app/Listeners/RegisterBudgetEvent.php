<?php

namespace App\Listeners;

use App\Events\BudgetAccepted;
use App\Events\BudgetDeclined;
use App\Events\BudgetExpired;
use App\Events\BudgetOpened;
use App\Events\BudgetSent;
use App\Models\BudgetEvent as BudgetEventModel;
use App\Services\ExchangeRateService;

class RegisterBudgetEvent
{
    public function handle(object $event): void
    {
        $budget = $event->budget ?? null;
        if (!$budget) {
            return;
        }

        $name = match (true) {
            $event instanceof BudgetSent => 'sent',
            $event instanceof BudgetOpened => 'opened',
            $event instanceof BudgetAccepted => 'accepted',
            $event instanceof BudgetDeclined => 'declined',
            $event instanceof BudgetExpired => 'expired',
            default => 'unknown',
        };

        /** @var ExchangeRateService $fx */
        $fx = app(ExchangeRateService::class);
        $rate = $fx->rateToBRL($budget->currency ?? 'BRL');

        $meta = [
            'currency' => $budget->currency,
            'rate_to_brl' => $rate,
        ];

        // Para eventos chaves, armazena totais para referência histórica
        if (in_array($name, ['sent','accepted','declined','opened'])) {
            $meta['total_one_time'] = (float) $budget->total_one_time;
            $meta['total_one_time_brl'] = (float) round($budget->total_one_time * $rate, 2);
        }

        // Se aceito e houver parcelamento selecionado, persiste também o total com juros
        if ($name === 'accepted') {
            $selected = $budget->selectedPayment()->first();
            if ($selected) {
                $meta['selected_installments'] = (int) $selected->installments;
                $meta['interest_rate'] = (float) $selected->interest_rate;
                $meta['total_with_interest'] = (float) $selected->total_with_interest;
                $meta['installment_value'] = (float) $selected->installment_value;
                $meta['total_with_interest_brl'] = (float) round($selected->total_with_interest * $rate, 2);
            }
        }

        BudgetEventModel::create([
            'budget_id' => $budget->id,
            'event' => $name,
            'meta' => $meta,
        ]);
    }
}
