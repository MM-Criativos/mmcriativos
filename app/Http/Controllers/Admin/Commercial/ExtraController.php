<?php

namespace App\Http\Controllers\Admin\Commercial;

use App\Http\Controllers\Controller;
use App\Models\Extra;
use App\Models\Service;
use Illuminate\Http\Request;

class ExtraController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'approved']);
    }

    public function index(Request $request)
    {
        $q = Extra::query();
        if ($request->filled('service_id')) {
            $sid = $request->integer('service_id');
            $q->whereHas('services', function ($sq) use ($sid) {
                $sq->where('service_id', $sid);
            });
        }
        $extras = $q->orderBy('sort')->orderBy('name')->paginate(20)->withQueryString();
        $services = Service::orderBy('name')->get();
        return view('admin.commercial.extras.index', compact('extras', 'services'));
    }

    public function create()
    {
        $extra = new Extra(['is_active' => true]);
        $services = Service::orderBy('name')->get();
        return view('admin.commercial.extras.create', compact('extra', 'services'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'price_type' => ['required', 'in:fixed,percent'],
            'billing_period' => ['required', 'in:one_time,monthly,yearly'],
            'default_discount' => ['nullable', 'numeric', 'min:0'],
            'category' => ['nullable', 'string', 'max:255'],
            'is_active' => ['sometimes', 'boolean'],
            'sort' => ['nullable', 'integer', 'min:0'],
        ]);

        $extra = Extra::create($data);

        return redirect()->route('admin.commercial.extras.edit', $extra)
            ->with('status', 'Extra criado.');
    }

    public function edit(Extra $extra)
    {
        $extra->load('services');
        $services = Service::orderBy('name')->get();
        return view('admin.commercial.extras.edit', compact('extra', 'services'));
    }

    public function update(Request $request, Extra $extra)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'price_type' => ['required', 'in:fixed,percent'],
            'billing_period' => ['required', 'in:one_time,monthly,yearly'],
            'default_discount' => ['nullable', 'numeric', 'min:0'],
            'category' => ['nullable', 'string', 'max:255'],
            'is_active' => ['sometimes', 'boolean'],
            'sort' => ['nullable', 'integer', 'min:0'],
        ]);

        $extra->update($data);

        return back()->with('status', 'Extra atualizado.');
    }

    public function destroy(Extra $extra)
    {
        $extra->delete();
        return redirect()->route('admin.commercial.extras.index')
            ->with('status', 'Extra removido.');
    }

    // AJAX: lista extras por serviço com preço efetivo calculado
    public function byService(Request $request)
    {
        $request->validate([
            'service_id' => ['required', 'exists:services,id'],
        ]);
        $service = Service::findOrFail($request->integer('service_id'));
        $extras = Extra::whereHas('services', function ($q) use ($service) {
                $q->where('service_id', $service->id);
            })
            ->where('is_active', true)
            ->orderBy('sort')
            ->get()
            ->map(fn ($e) => [
                'id' => $e->id,
                'name' => $e->name,
                'period' => $e->billing_period,
                'price' => (string) $e->priceFor($service),
            ]);

        return response()->json($extras);
    }
}

