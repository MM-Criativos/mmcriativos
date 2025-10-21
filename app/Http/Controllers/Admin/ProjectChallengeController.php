<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\ProjectChallenge;
use Illuminate\Http\Request;

class ProjectChallengeController extends Controller
{
    public function store(Request $request, Project $project)
    {
        $data = $request->validate([
            'title' => ['required','string','max:255'],
            'description' => ['nullable','string'],
        ]);
        $challenge = $project->challenges()->create($data);
        if ($request->ajax()) {
            return response()->json(['status' => 'ok', 'challenge' => $challenge]);
        }
        return back()->with('status', 'Desafio adicionado.');
    }

    public function update(Request $request, ProjectChallenge $challenge)
    {
        $data = $request->validate([
            'title' => ['required','string','max:255'],
            'description' => ['nullable','string'],
        ]);
        $challenge->update($data);
        if ($request->ajax()) {
            return response()->json(['status' => 'ok', 'challenge' => $challenge]);
        }
        return back()->with('status', 'Desafio atualizado.');
    }

    public function destroy(ProjectChallenge $challenge)
    {
        $id = $challenge->id;
        $challenge->delete();
        if (request()->ajax()) {
            return response()->json(['status' => 'ok', 'removed' => true, 'id' => $id]);
        }
        return back()->with('status', 'Desafio removido.');
    }
}
