<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Models\ServiceFeature;
use Illuminate\Http\Request;

class ServiceFeatureController extends Controller
{
    public function store(Request $request, Service $service)
    {
        if ($service->features()->count() >= 5) {
            return back()->with('status', 'Limite de 5 características atingido.');
        }
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'subtitle' => ['nullable', 'string', 'max:255'],
        ]);
        $data['order'] = (int) $service->features()->max('order') + 1;
        $service->features()->create($data);
        return back()->with('status', 'Característica adicionada.');
    }

    public function update(Request $request, ServiceFeature $feature)
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'subtitle' => ['nullable', 'string', 'max:255'],
            'order' => ['nullable', 'integer'],
        ]);
        $feature->update($data);
        return back()->with('status', 'Característica atualizada.');
    }

    public function destroy(ServiceFeature $feature)
    {
        $feature->delete();
        return back()->with('status', 'Característica removida.');
    }
}
