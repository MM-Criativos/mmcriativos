<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\ProjectSkillCompetency;
use Illuminate\Http\Request;

class ProjectSkillCompetencyController extends Controller
{
    public function attach(Request $request, Project $project)
    {
        $data = $request->validate([
            'skill_id' => ['required','exists:skills,id'],
            'competencies' => ['required','array'],
            'competencies.*' => ['exists:skill_competencies,id'],
        ]);

        $maxOrder = (int) $project->skillLinks()->max('order');
        $order = $maxOrder;

        foreach ($data['competencies'] as $compId) {
            $exists = ProjectSkillCompetency::where('project_id', $project->id)
                ->where('skill_id', $data['skill_id'])
                ->where('skill_competency_id', $compId)
                ->exists();
            if ($exists) continue;
            $order++;
            ProjectSkillCompetency::create([
                'project_id' => $project->id,
                'skill_id' => $data['skill_id'],
                'skill_competency_id' => $compId,
                'order' => $order,
            ]);
        }

        if ($request->ajax()) {
            $project->load(['skillLinks' => function ($q) { $q->orderBy('order'); }, 'skillLinks.skill', 'skillLinks.competency']);
            $html = view('admin.projects.partials.skill_links', compact('project'))->render();
            return response()->json(['status' => 'ok', 'html' => $html]);
        }
        return back()->with('status', 'Competências vinculadas ao projeto.');
    }

    public function destroy(ProjectSkillCompetency $projectSkillCompetency)
    {
        $project = $projectSkillCompetency->project;
        $projectSkillCompetency->delete();
        if (request()->ajax()) {
            $project->load(['skillLinks' => function ($q) { $q->orderBy('order'); }, 'skillLinks.skill', 'skillLinks.competency']);
            $html = view('admin.projects.partials.skill_links', compact('project'))->render();
            return response()->json(['status' => 'ok', 'html' => $html]);
        }
        return back()->with('status', 'Competência removida do projeto.');
    }
}
