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

    public function index()
    {
        $plans = Plan::with('service')->latest('id')->paginate(20);
        return view('admin.commercial.plans.index', compact('plans'));
    }

    public function create()
    {
        $plan = new Plan();
        $services = Service::orderBy('name')->get();
        return view('admin.commercial.plans.create', compact('plan', 'services'));
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
        $plan->load('service');
        $services = Service::orderBy('name')->get();
        return view('admin.commercial.plans.edit', compact('plan', 'services'));
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

