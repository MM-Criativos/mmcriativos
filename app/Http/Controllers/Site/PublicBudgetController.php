<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Events\BudgetAccepted;
use App\Events\BudgetDeclined;
use App\Events\BudgetOpened;
use App\Events\BudgetExpired;
use App\Models\Budget;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class PublicBudgetController extends Controller
{
    public function show(string $token)
    {
        $budget = Budget::with(['items', 'plan', 'service', 'selectedPayment'])
            ->where('public_token', $token)
            ->firstOrFail();

        $hasExpired = $budget->valid_until && $budget->valid_until->lt(Carbon::today());

        if ($hasExpired && in_array($budget->status, ['sent', 'opened'])) {
            event(new BudgetExpired($budget));
            $budget->refresh();
            $budget->loadMissing(['items', 'plan', 'service', 'selectedPayment']);
        } elseif ($budget->status === 'sent') {
            event(new BudgetOpened($budget));
        }

        $installmentRates = [
            1 => 0, 2 => 0, 3 => 0, 4 => 0,
            5 => 1.5, 6 => 3.0, 7 => 4.5, 8 => 6.0,
            9 => 7.5, 10 => 9.0, 11 => 10.5, 12 => 12.0,
        ];

        $installments = (int) request('installments', 1);
        if ($installments < 1 || $installments > 12) {
            $installments = 1;
        }

        $isExpired = $budget->status === 'expired';

        return view('public.budget', compact('budget', 'installmentRates', 'installments', 'isExpired'));
    }

    public function accept(Request $request, string $token)
    {
        $budget = Budget::where('public_token', $token)->firstOrFail();

        if ($budget->valid_until && $budget->valid_until->lt(Carbon::today())) {
            event(new BudgetExpired($budget));
            return redirect()->route('budget.public', ['token' => $token])
                ->with('status', 'Orçamento expirado. Entre em contato para solicitar uma nova proposta.');
        }

        $installmentRates = [
            1 => 0, 2 => 0, 3 => 0, 4 => 0,
            5 => 1.5, 6 => 3.0, 7 => 4.5, 8 => 6.0,
            9 => 7.5, 10 => 9.0, 11 => 10.5, 12 => 12.0,
        ];

        $installments = (int) $request->input('installments', 1);
        if ($installments < 1 || $installments > 12) {
            $installments = 1;
        }

        $budget->loadMissing('items');
        $servicesTotal = 0.0;
        foreach ($budget->items as $item) {
            $servicesTotal += (float) $item->total * ($item->billing_period === 'monthly' ? 12 : 1);
        }
        $product = max((float) $budget->base_price_snapshot - (float) ($budget->discount_amount ?? 0), 0);
        $grand = $product + $servicesTotal;
        $ratePercent = $installmentRates[$installments] ?? 0.0;
        $grandWithInterest = round($grand * (1 + $ratePercent / 100), 2);
        $perInstallment = round($grandWithInterest / max($installments, 1), 2);

        $budget->payments()->where('is_selected', true)->update(['is_selected' => false]);
        $budget->payments()->create([
            'installments' => $installments,
            'interest_rate' => $ratePercent,
            'total_with_interest' => $grandWithInterest,
            'installment_value' => $perInstallment,
            'is_selected' => true,
        ]);

        event(new BudgetAccepted($budget));

        return redirect()->route('budget.public', ['token' => $token, 'installments' => $installments])
            ->with('status', 'Orçamento aprovado. Entraremos em contato em breve!');
    }

    public function decline(Request $request, string $token)
    {
        $budget = Budget::where('public_token', $token)->firstOrFail();

        if ($budget->valid_until && $budget->valid_until->lt(Carbon::today())) {
            event(new BudgetExpired($budget));
            return redirect()->route('budget.public', ['token' => $token])
                ->with('status', 'Orçamento expirado. Entre em contato para solicitar uma nova proposta.');
        }

        event(new BudgetDeclined($budget));

        return redirect()->route('budget.public', ['token' => $token])
            ->with('status', 'Orçamento recusado. Obrigado pelo retorno!');
    }
}
