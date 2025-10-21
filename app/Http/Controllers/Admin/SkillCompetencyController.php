<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Skill;
use App\Models\SkillCompetency;
use Illuminate\Http\Request;

class SkillCompetencyController extends Controller
{
    public function store(Request $request, Skill $skill)
    {
        $data = $request->validate([
            'competency' => ['required', 'string', 'max:255'],
        ]);
        $skill->competencies()->create($data);
        return back()->with('status', 'Competência adicionada.');
    }

    public function update(Request $request, SkillCompetency $competency)
    {
        $data = $request->validate([
            'competency' => ['required', 'string', 'max:255'],
        ]);
        $competency->update($data);
        return back()->with('status', 'Competência atualizada.');
    }

    public function destroy(SkillCompetency $competency)
    {
        $competency->delete();
        return back()->with('status', 'Competência removida.');
    }
}
