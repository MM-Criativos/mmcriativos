<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Process;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ProcessController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'approved']);
    }

    public function index()
    {
        $processes = Process::query()
            ->orderBy('order')
            ->orderBy('name')
            ->get();

        return view('admin.processes.index', compact('processes'));
    }

    public function create()
    {
        $process = new Process([
            'order' => (int) Process::max('order') + 1,
        ]);

        return view('admin.processes.create', compact('process'));
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);

        if (empty($data['slug'])) {
            $data['slug'] = Str::slug($data['name']);
        }

        if (!isset($data['order']) || $data['order'] === null) {
            $data['order'] = (int) Process::max('order') + 1;
        }

        $process = Process::create($data);

        return redirect()
            ->route('admin.processes.edit', $process)
            ->with('status', 'Processo criado com sucesso.');
    }

    public function edit(Process $process)
    {
        return view('admin.processes.edit', compact('process'));
    }

    public function update(Request $request, Process $process)
    {
        $data = $this->validateData($request, $process);

        if (empty($data['slug'])) {
            $data['slug'] = Str::slug($data['name']);
        }

        $process->update($data);

        return back()->with('status', 'Processo atualizado com sucesso.');
    }

    public function destroy(Process $process)
    {
        $process->delete();

        return redirect()
            ->route('admin.processes.index')
            ->with('status', 'Processo removido.');
    }

    protected function validateData(Request $request, ?Process $process = null): array
    {
        $id = $process?->id;

        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => [
                'nullable',
                'string',
                'max:255',
                'unique:processes,slug' . ($id ? ',' . $id : ''),
            ],
            'icon' => ['nullable', 'string', 'max:255'],
            'order' => ['nullable', 'integer', 'min:0'],
        ]);
    }
}

