<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\ProjectTask;
use App\Models\ProjectSkillCompetency;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProjectTaskController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function store(Request $request, Project $project): RedirectResponse
    {
        $data = $request->validateWithBag('projectTasksStore', $this->rules($request, $project));

        $this->ensureSkillLink($project, (int) $data['skill_id'], (int) $data['skill_competency_id']);

        $project->tasks()->create($this->decorateData($data));

        return redirect()
            ->route('admin.projects.steps.show', [$project, 'tab' => 'development'])
            ->with('status', 'Tarefa criada com sucesso.');
    }

    public function update(Request $request, ProjectTask $projectTask): RedirectResponse
    {
        $project = $projectTask->project ?? abort(404);
        $bag = 'projectTasksUpdate_' . $projectTask->id;
        $data = $request->validateWithBag($bag, $this->rules($request, $project));

        $this->ensureSkillLink($project, (int) $data['skill_id'], (int) $data['skill_competency_id']);

        $projectTask->update($this->decorateData($data, $projectTask));

        return redirect()
            ->route('admin.projects.steps.show', [$project, 'tab' => 'development'])
            ->with('status', 'Tarefa atualizada com sucesso.');
    }

    public function destroy(ProjectTask $projectTask): RedirectResponse
    {
        $project = $projectTask->project ?? abort(404);
        $projectTask->delete();

        return redirect()
            ->route('admin.projects.steps.show', [$project, 'tab' => 'development'])
            ->with('status', 'Tarefa removida.');
    }

    protected function rules(Request $request, Project $project): array
    {
        $skillId = $request->input('skill_id');

        return [
            'skill_id' => [
                'required',
                'integer',
                Rule::exists('skills', 'id'),
            ],
            'skill_competency_id' => [
                'required',
                'integer',
                Rule::exists('skill_competencies', 'id')
                    ->where(function ($query) use ($skillId) {
                        if ($skillId) {
                            $query->where('skill_id', $skillId);
                        }
                    }),
            ],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'status' => ['required', Rule::in(array_keys(ProjectTask::STATUSES))],
            'assigned_to' => ['nullable', 'integer', 'exists:users,id'],
            'progress_notes' => ['nullable', 'string'],
        ];
    }

    protected function decorateData(array $data, ?ProjectTask $task = null): array
    {
        $status = $data['status'] ?? ProjectTask::STATUS_PENDING;
        $data['completed_at'] = $status === ProjectTask::STATUS_DONE
            ? ($task?->completed_at ?? now())
            : null;

        return $data;
    }

    protected function ensureSkillLink(Project $project, int $skillId, int $competencyId): void
    {
        ProjectSkillCompetency::firstOrCreate(
            [
                'project_id' => $project->id,
                'skill_id' => $skillId,
                'skill_competency_id' => $competencyId,
            ],
            [
                'order' => (int) ProjectSkillCompetency::where('project_id', $project->id)->max('order') + 1,
            ]
        );
    }
}
