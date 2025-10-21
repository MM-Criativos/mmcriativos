<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\ProjectSolution;
use Illuminate\Http\Request;

class ProjectSolutionController extends Controller
{
    public function store(Request $request, Project $project)
    {
        $data = $request->validate([
            'title' => ['required','string','max:255'],
            'description' => ['nullable','string'],
        ]);
        $solution = $project->solutions()->create($data);
        if ($request->ajax()) {
            return response()->json(['status' => 'ok', 'solution' => $solution]);
        }
        return back()->with('status', 'Solução adicionada.');
    }

    public function update(Request $request, ProjectSolution $solution)
    {
        $data = $request->validate([
            'title' => ['required','string','max:255'],
            'description' => ['nullable','string'],
        ]);
        $solution->update($data);
        if ($request->ajax()) {
            return response()->json(['status' => 'ok', 'solution' => $solution]);
        }
        return back()->with('status', 'Solução atualizada.');
    }

    public function destroy(ProjectSolution $solution)
    {
        $id = $solution->id;
        $solution->delete();
        if (request()->ajax()) {
            return response()->json(['status' => 'ok', 'removed' => true, 'id' => $id]);
        }
        return back()->with('status', 'Solução removida.');
    }
}
