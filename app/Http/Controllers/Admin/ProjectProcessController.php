<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Process;
use App\Models\Project;
use App\Models\ProjectProcess;
use Illuminate\Http\Request;

class ProjectProcessController extends Controller
{
    public function store(Request $request, Project $project)
    {
        $data = $request->validate([
            'process_id' => ['required','exists:processes,id'],
        ]);

        $order = (int) $project->projectProcesses()->max('order') + 1;

        $pp = $project->projectProcesses()->create([
            'process_id' => $data['process_id'],
            'order' => $order,
            'description' => null,
        ]);

        if ($request->ajax()) {
            $pp->load('process');
            return response()->json([
                'status' => 'ok',
                'project_process' => [
                    'id' => $pp->id,
                    'order' => $pp->order,
                    'description' => $pp->description,
                    'process' => [
                        'id' => $pp->process->id,
                        'name' => $pp->process->name,
                    ],
                ],
            ]);
        }

        return back()->with('status', 'Processo adicionado ao projeto.');
    }

    public function update(Request $request, ProjectProcess $projectProcess)
    {
        $data = $request->validate([
            'description' => ['nullable','string'],
            'order' => ['nullable','integer'],
        ]);
        $projectProcess->update($data);
        if ($request->ajax()) {
            return response()->json(['status' => 'ok', 'project_process' => [
                'id' => $projectProcess->id,
                'order' => $projectProcess->order,
                'description' => $projectProcess->description,
            ]]);
        }
        return back()->with('status', 'Processo atualizado.');
    }

    public function destroy(ProjectProcess $projectProcess)
    {
        $id = $projectProcess->id;
        $projectProcess->delete();
        if (request()->ajax()) {
            return response()->json(['status' => 'ok', 'removed' => true, 'id' => $id]);
        }
        return back()->with('status', 'Processo removido do projeto.');
    }
}
