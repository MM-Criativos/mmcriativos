<?php

namespace App\Http\Controllers\Admin\Commercial;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Models\Service;
use Illuminate\Http\Request;

class PlanController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'approved']);
    }

    public function index(Request $request)
    {
        // Reutiliza o modelo de edição de preços (categorias, vantagens)
        $category = $request->query('category', 'Presença Digital');
        $categories = Plan::query()->select('category')->distinct()->pluck('category')->all();
        if (empty($categories)) {
            $categories = ['Presença Digital', 'Soluções Inteligentes'];
        }

        $plans = Plan::with(['service', 'advantages'])
            ->where('category', $category)
            ->orderBy('price')
            ->get();

        return view('admin.commercial.plans.manage', compact('plans', 'category', 'categories'));
    }

    public function create()
    {
        // Desnecessário no fluxo atual: gestão feita via tela unificada
        return redirect()->route('admin.commercial.plans.index');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'service_id' => ['required', 'exists:services,id'],
            'category' => ['required', 'string', 'max:255'],
            'price' => ['required', 'numeric', 'min:0'],
            'description' => ['nullable', 'string'],
        ]);

        $plan = Plan::create($data);

        return redirect()->route('admin.commercial.plans.edit', $plan)
            ->with('status', 'Plano criado com sucesso.');
    }

    public function edit(Plan $plan)
    {
        // Redireciona para a tela unificada na categoria do plano
        return redirect()->route('admin.commercial.plans.index', ['category' => $plan->category]);
    }

    public function update(Request $request, Plan $plan)
    {
        $data = $request->validate([
            'service_id' => ['required', 'exists:services,id'],
            'category' => ['required', 'string', 'max:255'],
            'price' => ['required', 'numeric', 'min:0'],
            'description' => ['nullable', 'string'],
        ]);

        $plan->update($data);

        return back()->with('status', 'Plano atualizado.');
    }

    public function destroy(Plan $plan)
    {
        $plan->delete();
        return redirect()->route('admin.commercial.plans.index')
            ->with('status', 'Plano removido.');
    }
}
