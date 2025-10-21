<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Service;
use Illuminate\Http\Request;

class ServiceInfoController extends Controller
{
    public function update(Request $request, Service $service)
    {
        $data = $request->validate([
            'subtitle' => ['nullable', 'string', 'max:255'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
        ]);

        $service->info()->updateOrCreate([], $data);

        return back()->with('status', 'Informações salvas.');
    }
}

