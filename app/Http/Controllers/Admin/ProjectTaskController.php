<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\ProjectTask;
use App\Models\ProjectTaskItem;
use App\Models\ProjectSkillCompetency;
use App\Models\Skill;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProjectTaskController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function store(Request $request, Project $project): RedirectResponse
    {
        $data = $request->validateWithBag('projectTasksStore', $this->rules($request, $project));
        $items = $data['items'] ?? [];
        unset($data['items']);

        $this->ensureSkillLink($project, (int) $data['skill_id'], (int) $data['skill_competency_id']);

        $task = $project->tasks()->create($this->decorateData($data));

        $this->syncTaskItems($project, $task, $items);

        return redirect()
            ->route('admin.projects.steps.show', [$project, 'tab' => 'development'])
            ->with('status', 'Tarefa criada com sucesso.');
    }

    public function update(Request $request, ProjectTask $projectTask): RedirectResponse
    {
        $project = $projectTask->project ?? abort(404);
        $bag = 'projectTasksUpdate_' . $projectTask->id;
        $data = $request->validateWithBag($bag, $this->rules($request, $project));
        $items = $data['items'] ?? [];
        unset($data['items']);

        $this->ensureSkillLink($project, (int) $data['skill_id'], (int) $data['skill_competency_id']);

        $projectTask->update($this->decorateData($data, $projectTask));
        $this->syncTaskItems($project, $projectTask, $items);

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
            'planned_at' => ['nullable', 'date'],
            'items' => ['nullable', 'array'],
            'items.*.id' => ['nullable', 'integer'],
            'items.*.title' => ['required', 'string', 'max:255'],
            'items.*.description' => ['nullable', 'string'],
            'items.*.skill_competency_id' => [
                'nullable',
                'integer',
                Rule::exists('skill_competencies', 'id')
                    ->where(function ($query) use ($skillId) {
                        if ($skillId) {
                            $query->where('skill_id', $skillId);
                        }
                    }),
            ],
            'items.*.assigned_to' => ['nullable', 'integer', 'exists:users,id'],
        ];
    }

    protected function decorateData(array $data, ?ProjectTask $task = null): array
    {
        $status = $data['status'] ?? ProjectTask::STATUS_PENDING;
        $data['completed_at'] = $status === ProjectTask::STATUS_DONE
            ? ($task?->completed_at ?? now())
            : null;

        $data['planned_at'] = isset($data['planned_at']) && $data['planned_at'] !== ''
            ? Carbon::parse($data['planned_at'])
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

    protected function syncTaskItems(Project $project, ProjectTask $task, array $items = []): void
    {
        $itemsCollection = collect($items)
            ->filter(fn($item) => filled($item['title'] ?? null))
            ->values();

        $existingItems = $task->items()->get()->keyBy('id');
        $retainIds = [];

        foreach ($itemsCollection as $index => $itemData) {
            $competencyId = $itemData['skill_competency_id'] ?? null;
            if (blank($competencyId)) {
                $competencyId = $task->skill_competency_id;
            }

            $payload = [
                'project_id' => $project->id,
                'project_task_id' => $task->id,
                'skill_id' => $task->skill_id,
                'skill_competency_id' => $competencyId ? (int) $competencyId : null,
                'assigned_to' => !empty($itemData['assigned_to']) ? (int) $itemData['assigned_to'] : null,
                'title' => $itemData['title'],
                'description' => $itemData['description'] ?? null,
                'order' => $index + 1,
            ];

            if (!empty($itemData['id'])) {
                $existingItem = $existingItems->get((int) $itemData['id']);
                if ($existingItem) {
                    $existingItem->update($payload);
                    $retainIds[] = $existingItem->id;
                    continue;
                }
            }

            $newItem = $task->items()->create($payload);
            $retainIds[] = $newItem->id;
        }

        if (empty($retainIds)) {
            $task->items()->delete();
            return;
        }

        $task->items()->whereNotIn('id', $retainIds)->delete();
    }

    public function toggleItem(ProjectTaskItem $projectTaskItem): RedirectResponse
    {
        $project = $projectTaskItem->project ?? abort(404);

        if ($projectTaskItem->is_done) {
            $projectTaskItem->markAsUndone();
            $message = 'Subtarefa reaberta.';
        } else {
            $projectTaskItem->markAsDone();
            $message = 'Subtarefa finalizada.';
        }

        return redirect()
            ->route('admin.projects.steps.show', [$project, 'tab' => 'development'])
            ->with('status', $message);
    }

    public function complete(ProjectTask $projectTask): RedirectResponse
    {
        $project = $projectTask->project ?? abort(404);

        if (!$projectTask->isCompleted()) {
            $projectTask->update([
                'status' => ProjectTask::STATUS_DONE,
                'completed_at' => now(),
            ]);
        }

        return redirect()
            ->route('admin.projects.steps.show', ['project' => $project, 'tab' => 'development'])
            ->with('status', 'Tarefa marcada como concluída.');
    }

    public function completed(Request $request): View
    {
        $search = trim((string) $request->query('q', ''));
        $selectedSkill = $request->query('skill_id');
        $projectId = $request->query('project_id');

        $tasksQuery = ProjectTask::query()
            ->with([
                'project',
                'skill',
                'competency',
                'assignedUser',
                'items.assignedUser',
                'items.competency',
            ])
            ->where('status', ProjectTask::STATUS_DONE)
            ->orderByDesc('completed_at')
            ->orderByDesc('updated_at');

        if ($selectedSkill) {
            $tasksQuery->where('skill_id', $selectedSkill);
        }

        if ($search !== '') {
            $tasksQuery->where(function ($query) use ($search) {
                $query->where('title', 'like', '%' . $search . '%')
                    ->orWhereHas('items', function ($itemQuery) use ($search) {
                        $itemQuery->where('title', 'like', '%' . $search . '%');
                    });
            });
        }

        $tasks = $tasksQuery->paginate(10)->withQueryString();

        $skills = Skill::query()->orderBy('name')->get(['id', 'name']);

        return view('admin.projects.tasks.completed', [
            'tasks' => $tasks,
            'skills' => $skills,
            'search' => $search,
            'selectedSkill' => $selectedSkill,
            'projectId' => $projectId,
        ]);
    }
}
