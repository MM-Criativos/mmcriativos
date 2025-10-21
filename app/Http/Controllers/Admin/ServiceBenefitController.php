<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Models\ServiceBenefit;
use Illuminate\Http\Request;

class ServiceBenefitController extends Controller
{
    public function store(Request $request, Service $service)
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'subtitle' => ['nullable', 'string', 'max:255'],
        ]);
        $data['order'] = (int) $service->benefits()->max('order') + 1;
        $service->benefits()->create($data);
        return back()->with('status', 'Benefício adicionado.');
    }

    public function update(Request $request, ServiceBenefit $benefit)
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'subtitle' => ['nullable', 'string', 'max:255'],
            'order' => ['nullable', 'integer'],
        ]);
        $benefit->update($data);
        return back()->with('status', 'Benefício atualizado.');
    }

    public function destroy(ServiceBenefit $benefit)
    {
        $benefit->delete();
        return back()->with('status', 'Benefício removido.');
    }
}
