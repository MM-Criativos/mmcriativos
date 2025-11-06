<?php

namespace App\Http\Controllers\Admin\Commercial;

use App\Http\Controllers\Controller;
use App\Models\Budget;
use App\Models\Client;
use App\Models\Plan;
use App\Models\Service;
use App\Models\EmailTemplate;
use App\Services\EmailTemplateService;
use App\Events\BudgetSent;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Route as RouteFacade;

class BudgetController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'approved']);
    }

    public function index(Request $request)
    {
        $q = Budget::query()->with(['client', 'plan', 'service', 'selectedPayment'])->latest();

        if ($request->filled('status')) {
            $q->where('status', $request->string('status'));
        }
        if ($request->filled('client_id')) {
            $q->where('client_id', $request->integer('client_id'));
        }
        if ($request->filled('service_id')) {
            $q->where('service_id', $request->integer('service_id'));
        }

        $budgets = $q->paginate(20)->withQueryString();
        $clients = Client::orderBy('name')->get();
        $services = Service::orderBy('name')->get();

        return view('admin.commercial.budgets.index', compact('budgets', 'clients', 'services'));
    }

    public function create(Request $request)
    {
        $budget = new Budget([
            'currency' => 'BRL',
            'status' => 'draft',
        ]);
        $clients = Client::orderBy('name')->get();
        $services = Service::orderBy('name')->get();
        $plans = Plan::orderBy('category')->get();

        return view('admin.commercial.budgets.create', compact('budget', 'clients', 'services', 'plans'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'service_id' => ['nullable', 'exists:services,id'],
            'plan_id' => ['nullable', 'exists:plans,id'],
            'client_id' => ['nullable', 'exists:clients,id'],
            'client_name' => ['required', 'string', 'max:255'],
            'client_email' => ['required', 'email', 'max:255'],
            'client_phone' => ['nullable', 'string', 'max:255'],
            'currency' => ['required', 'in:BRL,USD,EUR'],
            'discount_amount' => ['nullable', 'numeric', 'min:0'],
            'base_price_snapshot' => ['nullable', 'numeric', 'min:0'],
            'valid_until' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
        ]);

        $plan = null;
        if (!empty($data['plan_id'])) {
            $plan = Plan::find($data['plan_id']);
        }

        $budget = new Budget();
        $budget->fill($data);
        $budget->base_price_snapshot = isset($data['base_price_snapshot'])
            ? (float) $data['base_price_snapshot']
            : (float) ($plan->price ?? 0);
        $budget->currency = $data['currency'] ?? 'BRL';
        $budget->public_token = Str::random(64);
        $budget->status = 'draft';
        $budget->save();

        $budget->calculateTotals();

        return redirect()->route('admin.commercial.budgets.edit', $budget)
            ->with('status', 'Orçamento criado.');
    }

    public function edit(Budget $budget)
    {
        $budget->load(['items', 'client', 'plan', 'service', 'events']);
        $clients = Client::orderBy('name')->get();
        $services = Service::orderBy('name')->get();
        $plans = Plan::orderBy('category')->get();

        // Extras disponíveis para o serviço do orçamento
        $extras = [];
        if ($budget->service_id) {
            $extras = \App\Models\Extra::whereHas('services', function ($q) use ($budget) {
                $q->where('service_id', $budget->service_id);
            })->where('is_active', true)->orderBy('sort')->get();
        }

        // Tabela de taxas para parcelamento
        $installmentRates = [
            1 => 0, 2 => 0, 3 => 0, 4 => 0,
            5 => 1.5, 6 => 3.0, 7 => 4.5, 8 => 6.0,
            9 => 7.5, 10 => 9.0, 11 => 10.5, 12 => 12.0,
        ];
        $installments = (int) request('installments', 1);
        if ($installments < 1 || $installments > 12) {
            $installments = 1;
        }

        return view('admin.commercial.budgets.edit', compact('budget', 'clients', 'services', 'plans', 'extras', 'installmentRates', 'installments'));
    }

    public function update(Request $request, Budget $budget)
    {
        $data = $request->validate([
            'service_id' => ['nullable', 'exists:services,id'],
            'plan_id' => ['nullable', 'exists:plans,id'],
            'client_id' => ['nullable', 'exists:clients,id'],
            'client_name' => ['required', 'string', 'max:255'],
            'client_email' => ['required', 'email', 'max:255'],
            'client_phone' => ['nullable', 'string', 'max:255'],
            'currency' => ['required', 'in:BRL,USD,EUR'],
            'discount_amount' => ['nullable', 'numeric', 'min:0'],
            'base_price_snapshot' => ['nullable', 'numeric', 'min:0'],
            'status' => ['nullable', 'string', 'max:20'],
            'valid_until' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
        ]);

        $oldPlanId = $budget->plan_id;
        $budget->fill($data);

        if (!empty($data['plan_id']) && $data['plan_id'] != $oldPlanId) {
            $plan = Plan::find($data['plan_id']);
            $budget->base_price_snapshot = (float) ($plan->price ?? 0);
        }

        if (isset($data['base_price_snapshot'])) {
            $budget->base_price_snapshot = (float) $data['base_price_snapshot'];
        }

        $budget->save();
        $budget->calculateTotals();

        return back()->with('status', 'Orçamento atualizado.');
    }

    public function destroy(Budget $budget)
    {
        $budget->delete();
        return redirect()->route('admin.commercial.budgets.index')
            ->with('status', 'Orçamento removido.');
    }

    public function sendEmail(Request $request, Budget $budget)
    {
        $data = $request->validate([
            'to' => ['nullable', 'email'],
        ]);

        $to = $data['to']
            ?? ($budget->client->email ?? null)
            ?? $budget->client_email;

        $publicLink = RouteFacade::has('budget.public')
            ? route('budget.public', $budget->public_token)
            : url('/budget/' . $budget->public_token);

        $vars = [
            'client_name' => $budget->client->name ?? $budget->client_name,
            'budget_id' => $budget->id,
            'valid_until' => optional($budget->valid_until)->format('d/m/Y'),
            'public_link' => $publicLink,
            'company_name' => config('app.name', 'MM Criativos'),
        ];

        EmailTemplateService::send('budget_sent', $to, $vars);

        event(new BudgetSent($budget));

        return back()->with('status', 'Orçamento enviado com sucesso!');
    }

    public function preview(Budget $budget)
    {
        $budget->load(['items','service','plan']);

        $installmentRates = [
            1 => 0, 2 => 0, 3 => 0, 4 => 0,
            5 => 1.5, 6 => 3.0, 7 => 4.5, 8 => 6.0,
            9 => 7.5, 10 => 9.0, 11 => 10.5, 12 => 12.0,
        ];
        $installments = (int) request('installments', 1);
        if ($installments < 1 || $installments > 12) {
            $installments = 1;
        }

        return view('admin.commercial.budgets.preview', compact('budget','installmentRates','installments'));
    }

    public function addExtra(Request $request, Budget $budget)
    {
        $data = $request->validate([
            'extra_id' => ['required','exists:extras,id'],
            'qty' => ['nullable','integer','min:1'],
        ]);

        $qty = max((int) ($data['qty'] ?? 1), 1);
        $extra = \App\Models\Extra::with(['services' => function($q) use ($budget){
            $q->where('service_id', $budget->service_id);
        }])->findOrFail($data['extra_id']);

        $unitPrice = $extra->price;
        $discount = $extra->default_discount ?? 0;
        if ($extra->services->isNotEmpty()) {
            $pivot = $extra->services->first()->pivot;
            if (!is_null($pivot->custom_price)) $unitPrice = $pivot->custom_price;
            if (!is_null($pivot->custom_discount)) $discount = $pivot->custom_discount;
        }

        $lineTotal = max(($unitPrice * $qty) - $discount, 0);

        $item = new \App\Models\BudgetItem([
            'budget_id' => $budget->id,
            'line_type' => 'extra',
            'ref_id' => $extra->id,
            'ref_type' => 'extra',
            'name' => $extra->name,
            'description' => $extra->description,
            'qty' => $qty,
            'unit_price' => $unitPrice,
            'price_type' => $extra->price_type,
            'billing_period' => $extra->billing_period,
            'discount_amount' => $discount,
            'total' => $lineTotal,
            'sort' => (int) $budget->items()->max('sort') + 1,
        ]);
        $item->save();

        $budget->calculateTotals();

        return back()->with('status','Extra adicionado.');
    }

    public function updateItem(Request $request, \App\Models\BudgetItem $budgetItem)
    {
        $data = $request->validate([
            'discount_amount' => ['nullable','numeric','min:0'],
            'qty' => ['nullable','integer','min:1'],
        ]);

        if (isset($data['discount_amount'])) {
            $budgetItem->discount_amount = (float) $data['discount_amount'];
        }
        if (isset($data['qty'])) {
            $budgetItem->qty = max((int) $data['qty'], 1);
        }
        $budgetItem->save();

        $budgetItem->budget->calculateTotals();

        return back()->with('status','Item atualizado.');
    }

    public function destroyItem(\App\Models\BudgetItem $budgetItem)
    {
        $budget = $budgetItem->budget;
        $budgetItem->delete();
        $budget->calculateTotals();
        return back()->with('status','Item removido.');
    }
}
