<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Models\ServiceProcess;
use Illuminate\Http\Request;

class ServiceProcessController extends Controller
{
    public function store(Request $request, Service $service)
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'image' => ['nullable', 'image'],
        ]);
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('services/processes', 'public');
            $data['image'] = 'storage/' . $path;
        }
        $data['order'] = (int) $service->processes()->max('order') + 1;
        $service->processes()->create($data);
        return back()->with('status', 'Processo adicionado.');
    }

    public function update(Request $request, ServiceProcess $process)
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'image' => ['nullable', 'image'],
            'order' => ['nullable', 'integer'],
        ]);
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('services/processes', 'public');
            $data['image'] = 'storage/' . $path;
        }
        $process->update($data);
        return back()->with('status', 'Processo atualizado.');
    }

    public function destroy(ServiceProcess $process)
    {
        $process->delete();
        return back()->with('status', 'Processo removido.');
    }
}
