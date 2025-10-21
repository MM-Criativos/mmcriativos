<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Models\ServiceCta;
use Illuminate\Http\Request;

class ServiceCtaController extends Controller
{
    public function store(Request $request, Service $service)
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:255'],
            'image' => ['nullable', 'image'],
        ]);
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('services/ctas', 'public');
            $data['image'] = 'storage/' . $path;
        }
        $service->ctas()->create($data);
        return back()->with('status', 'CTA adicionada.');
    }

    public function update(Request $request, ServiceCta $cta)
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:255'],
            'image' => ['nullable', 'image'],
        ]);
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('services/ctas', 'public');
            $data['image'] = 'storage/' . $path;
        }
        $cta->update($data);
        return back()->with('status', 'CTA atualizada.');
    }

    public function destroy(ServiceCta $cta)
    {
        $cta->delete();
        return back()->with('status', 'CTA removida.');
    }
}

