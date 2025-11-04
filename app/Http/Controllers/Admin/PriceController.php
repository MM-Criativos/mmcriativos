<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Models\PlanAdvantage;
use Illuminate\Http\Request;

class PriceController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('approved');
    }

    public function edit(Request $request)
    {
        $category = $request->query('category', 'Presença Digital');
        $categories = Plan::query()->select('category')->distinct()->pluck('category')->all();
        if (empty($categories)) {
            $categories = ['Presença Digital', 'Soluções Inteligentes'];
        }

        $plans = Plan::with(['service', 'advantages'])
            ->where('category', $category)
            ->orderBy('price')
            ->get();

        return view('admin.layout.price.edit', compact('plans', 'category', 'categories'));
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'plans' => ['required', 'array'],
            'plans.*.price' => ['nullable', 'string'],
            'plans.*.description' => ['nullable', 'string'],
            'advantages' => ['nullable', 'array'],
            'advantages.*' => ['array'],
            'advantages.*.*' => ['nullable', 'string', 'max:255'],
        ]);

        foreach ($data['plans'] as $planId => $p) {
            /** @var Plan $plan */
            $plan = Plan::with('advantages')->find((int) $planId);
            if (!$plan) continue;

            $priceRaw = (string) ($p['price'] ?? '');
            // normaliza: remove pontos de milhar e troca vírgula por ponto
            $priceSan = str_replace(['.', ','], ['', '.'], $priceRaw);
            $price = is_numeric($priceSan) ? (float) $priceSan : $plan->price;

            $plan->update([
                'price' => $price,
                'description' => $p['description'] ?? null,
            ]);

            // Vantagens
            $list = collect($data['advantages'][$plan->id] ?? [])
                ->map(fn($t) => trim((string) $t))
                ->filter();

            // Estratégia simples: substituir conjunto
            $plan->advantages()->delete();
            foreach ($list as $text) {
                $plan->advantages()->create(['title' => $text]);
            }
        }

        return back()->with('status', 'Planos atualizados.');
    }
}

