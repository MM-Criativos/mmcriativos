<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Project;
use App\Models\ProjectSkillCompetency;
use App\Models\ProjectTask;
use App\Models\ProjectTaskItem;
use App\Models\Skill;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\Rule;
use Carbon\Carbon;

class TaskController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request): View
    {
        $search = trim((string) $request->query('q', ''));
        $projectId = $request->query('project_id');
        $skillId = $request->query('skill_id');
        $clientId = $request->query('client');
        $professionalId = $request->query('professional');
        $deadline = $request->query('deadline');
        $status = $request->query('status');

        $availableStatuses = [
            ProjectTask::STATUS_IN_PROGRESS => 'Em andamento',
            ProjectTask::STATUS_PENDING => 'Pendente',
        ];

        $query = ProjectTask::query()
            ->with([
                'project',
                'skill',
                'assignedUser',
                'items.assignedUser',
                'items.competency',
            ])
            ->whereIn('status', array_keys($availableStatuses));

        if ($status && array_key_exists($status, $availableStatuses)) {
            $query->where('status', $status);
        }

        if ($projectId) {
            $query->where('project_id', $projectId);
        }

        if ($skillId) {
            $query->where('skill_id', $skillId);
        }

        if ($clientId) {
            $query->whereHas('project', function ($projectQuery) use ($clientId) {
                $projectQuery->where('client_id', $clientId);
            });
        }

        if ($professionalId) {
            $query->where('assigned_to', $professionalId);
        }

        if ($deadline) {
            try {
                $deadlineDate = Carbon::parse($deadline);
                $query->whereDate('planned_at', $deadlineDate);
            } catch (\Throwable $e) {
                //
            }
        }

        if ($search !== '') {
            $query->where(function ($sub) use ($search) {
                $sub->where('title', 'like', '%' . $search . '%')
                    ->orWhereHas('items', function ($itemQuery) use ($search) {
                        $itemQuery->where('title', 'like', '%' . $search . '%');
                    });
            });
        }

        $query->orderByRaw("
            CASE status
                WHEN '" . ProjectTask::STATUS_IN_PROGRESS . "' THEN 0
                WHEN '" . ProjectTask::STATUS_PENDING . "' THEN 1
                ELSE 2
            END
        ")
        ->orderByRaw("
            CASE
                WHEN planned_at IS NULL THEN 2
                WHEN planned_at < NOW() THEN 0
                ELSE 1
            END
        ")
        ->orderBy('planned_at')
        ->orderByDesc('updated_at');

        $tasks = $query->paginate(10)->withQueryString();

        return view('admin.tasks.index', [
            'tasks' => $tasks,
            'projects' => Project::orderBy('name')->get(['id', 'name']),
            'skills' => Skill::orderBy('name')->get(['id', 'name']),
            'clientOptions' => Client::orderBy('name')->get(['id', 'name']),
            'professionalOptions' => User::where('is_approved', true)->orderBy('name')->get(['id', 'name']),
            'search' => $search,
            'selectedProject' => $projectId,
            'selectedSkill' => $skillId,
            'selectedStatus' => $status,
            'selectedClient' => $clientId,
            'selectedProfessional' => $professionalId,
            'deadlineFilter' => $deadline,
            'availableStatuses' => $availableStatuses,
        ]);
    }

    public function completed(Request $request): View
    {
        $search = trim((string) $request->query('q', ''));
        $projectId = $request->query('project_id');
        $skillId = $request->query('skill_id');
        $clientId = $request->query('client');
        $professionalId = $request->query('professional');
        $deadline = $request->query('deadline');

        $query = ProjectTask::query()
            ->with([
                'project',
                'skill',
                'assignedUser',
                'items.assignedUser',
                'items.competency',
            ])
            ->where('status', ProjectTask::STATUS_DONE);

        if ($projectId) {
            $query->where('project_id', $projectId);
        }

        if ($skillId) {
            $query->where('skill_id', $skillId);
        }

        if ($clientId) {
            $query->whereHas('project', function ($projectQuery) use ($clientId) {
                $projectQuery->where('client_id', $clientId);
            });
        }

        if ($professionalId) {
            $query->where('assigned_to', $professionalId);
        }

        if ($deadline) {
            try {
                $deadlineDate = Carbon::parse($deadline);
                $query->whereDate('planned_at', $deadlineDate);
            } catch (\Throwable $e) {
                //
            }
        }

        if ($search !== '') {
            $query->where(function ($sub) use ($search) {
                $sub->where('title', 'like', '%' . $search . '%')
                    ->orWhereHas('items', function ($itemQuery) use ($search) {
                        $itemQuery->where('title', 'like', '%' . $search . '%');
                    });
            });
        }

        $tasks = $query
            ->orderByDesc('completed_at')
            ->orderByDesc('updated_at')
            ->paginate(10)
            ->withQueryString();

        return view('admin.tasks.completed', [
            'tasks' => $tasks,
            'projects' => Project::orderBy('name')->get(['id', 'name']),
            'skills' => Skill::orderBy('name')->get(['id', 'name']),
            'clientOptions' => Client::orderBy('name')->get(['id', 'name']),
            'professionalOptions' => User::where('is_approved', true)->orderBy('name')->get(['id', 'name']),
            'search' => $search,
            'selectedProject' => $projectId,
            'selectedSkill' => $skillId,
            'selectedClient' => $clientId,
            'selectedProfessional' => $professionalId,
            'deadlineFilter' => $deadline,
        ]);
    }

    public function calendar(): View
    {
        $projects = Project::orderBy('name')->get(['id', 'name']);
        $skills = Skill::with('competencies')->orderBy('name')->get();
        $teamMembers = User::where('is_approved', true)->orderBy('name')->get(['id', 'name']);

        $statusOptions = ProjectTask::STATUSES;

        $upcomingTasks = ProjectTask::query()
            ->with(['project', 'skill', 'assignedUser', 'items'])
            ->whereNotNull('planned_at')
            ->whereIn('status', [ProjectTask::STATUS_PENDING, ProjectTask::STATUS_IN_PROGRESS])
            ->orderBy('planned_at')
            ->limit(8)
            ->get();

        $calendarTasks = ProjectTask::query()
            ->with('project')
            ->whereNotNull('planned_at')
            ->whereIn('status', [ProjectTask::STATUS_PENDING, ProjectTask::STATUS_IN_PROGRESS])
            ->orderBy('planned_at')
            ->get();

        $now = now();
        $events = $calendarTasks->map(fn($task) => [
            'id' => $task->id,
            'title' => $task->title,
            'start' => optional($task->planned_at)?->format('Y-m-d'),
            'allDay' => true,
            'url' => $task->project ? route('admin.projects.steps.show', [$task->project, 'tab' => 'development']) : null,
            'backgroundColor' => !$task->isCompleted() && optional($task->planned_at)?->isPast()
                ? '#ff4d4f'
                : ($task->status === ProjectTask::STATUS_IN_PROGRESS ? '#ff8800' : '#0069ff'),
            'borderColor' => '#000',
            'extendedProps' => [
                'status' => $task->status,
                'project' => $task->project?->name,
            ],
        ])->filter(fn($event) => filled($event['start']))->values()->all();

        return view('admin.tasks.calendar', [
            'projects' => $projects,
            'skills' => $skills,
            'teamMembers' => $teamMembers,
            'statusOptions' => $statusOptions,
            'upcomingTasks' => $upcomingTasks,
            'events' => $events,
        ]);
    }

    public function kanban(): View
    {
        $kanbanStatuses = [
            ProjectTask::STATUS_PENDING => 'Pendente',
            ProjectTask::STATUS_IN_PROGRESS => 'Em andamento',
            ProjectTask::STATUS_DONE => 'Finalizadas',
        ];

        $projects = Project::orderBy('name')->get(['id', 'name']);
        $skills = Skill::with('competencies')->orderBy('name')->get(['id', 'name']);
        $teamMembers = User::where('is_approved', true)->orderBy('name')->get(['id', 'name']);
        $statusOptions = ProjectTask::STATUSES;

        $tasks = ProjectTask::query()
            ->with(['skill', 'competency', 'items.competency'])
            ->orderByRaw("
                CASE status
                    WHEN '" . ProjectTask::STATUS_PENDING . "' THEN 0
                    WHEN '" . ProjectTask::STATUS_IN_PROGRESS . "' THEN 1
                    WHEN '" . ProjectTask::STATUS_DONE . "' THEN 2
                    ELSE 3
                END
            ")
            ->orderBy('planned_at')
            ->orderByDesc('updated_at')
            ->get();

        $recentlyCompleted = $tasks
            ->where('status', ProjectTask::STATUS_DONE)
            ->filter(fn($task) => $task->completed_at && $task->completed_at->greaterThanOrEqualTo(now()->subDays(3)));

        return view('admin.tasks.kanban', [
            'kanbanStatuses' => $kanbanStatuses,
            'kanbanTasks' => $tasks->groupBy('status'),
            'recentlyCompleted' => $recentlyCompleted,
            'projects' => $projects,
            'skills' => $skills,
            'teamMembers' => $teamMembers,
            'statusOptions' => $statusOptions,
        ]);
    }

    public function updateStatus(Request $request, ProjectTask $projectTask): JsonResponse
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(array_keys(ProjectTask::STATUSES))],
        ]);

        if ($projectTask->status !== $data['status']) {
            $projectTask->update(['status' => $data['status']]);
        }

        return response()->json(['status' => 'success']);
    }

    public function storeCalendar(Request $request): RedirectResponse
    {
        $data = $request->validate($this->calendarRules($request));
        $items = $data['items'] ?? [];
        unset($data['items']);
        $project = Project::findOrFail($data['project_id']);
        $this->ensureSkillLink($project, (int) $data['skill_id'], (int) $data['skill_competency_id']);

        $task = $project->tasks()->create($this->decorateCalendarData($data));
        $this->syncCalendarItems($project, $task, $items);

        return redirect()
            ->route('admin.tasks.calendar')
            ->with('status', 'Tarefa criada com sucesso.');
    }

    public function updateCalendar(Request $request, ProjectTask $projectTask): RedirectResponse
    {
        $data = $request->validate($this->calendarRules($request));
        $items = $data['items'] ?? [];
        unset($data['items']);
        $project = Project::findOrFail($data['project_id']);
        $this->ensureSkillLink($project, (int) $data['skill_id'], (int) $data['skill_competency_id']);

        $projectTask->update($this->decorateCalendarData($data, $projectTask));
        $this->syncCalendarItems($project, $projectTask, $items);

        return redirect()
            ->route('admin.tasks.calendar')
            ->with('status', 'Tarefa atualizada com sucesso.');
    }

    public function destroyCalendar(ProjectTask $projectTask): RedirectResponse
    {
        $projectTask->delete();

        return redirect()
            ->route('admin.tasks.calendar')
            ->with('status', 'Tarefa removida.');
    }

    protected function calendarRules(Request $request): array
    {
        $skillId = $request->input('skill_id');

        return [
            'project_id' => ['required', 'integer', Rule::exists('projects', 'id')],
            'skill_id' => ['required', 'integer', Rule::exists('skills', 'id')],
            'skill_competency_id' => [
                'required',
                'integer',
                Rule::exists('skill_competencies', 'id')->where(function ($query) use ($skillId) {
                    if ($skillId) {
                        $query->where('skill_id', $skillId);
                    }
                }),
            ],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'status' => ['required', Rule::in(array_keys(ProjectTask::STATUSES))],
            'assigned_to' => ['nullable', 'integer', 'exists:users,id'],
            'planned_at' => ['nullable', 'date_format:d/m/Y H:i'],
            'items' => ['nullable', 'array'],
            'items.*.id' => ['nullable', 'integer'],
            'items.*.title' => ['required', 'string', 'max:255'],
            'items.*.description' => ['nullable', 'string'],
            'items.*.skill_competency_id' => [
                'nullable',
                'integer',
                Rule::exists('skill_competencies', 'id')->where(function ($query) use ($skillId) {
                    if ($skillId) {
                        $query->where('skill_id', $skillId);
                    }
                }),
            ],
            'items.*.assigned_to' => ['nullable', 'integer', 'exists:users,id'],
        ];
    }

    protected function decorateCalendarData(array $data, ?ProjectTask $task = null): array
    {
        $status = $data['status'] ?? ProjectTask::STATUS_PENDING;
        $data['completed_at'] = $status === ProjectTask::STATUS_DONE
            ? ($task?->completed_at ?? now())
            : null;

        $data['planned_at'] = $this->parseCalendarPlannedAt($data['planned_at'] ?? null);

        return $data;
    }

    protected function parseCalendarPlannedAt(?string $plannedAt): ?Carbon
    {
        $raw = trim((string) ($plannedAt ?? ''));
        if ($raw === '') {
            return null;
        }

        foreach (['d/m/Y H:i', 'Y-m-d H:i', 'Y-m-d', 'Y-m-d H:i:s'] as $format) {
            try {
                return Carbon::createFromFormat($format, $raw);
            } catch (\Throwable $e) {
                //
            }
        }

        try {
            return Carbon::parse($raw);
        } catch (\Throwable $e) {
            return null;
        }
    }

    protected function syncCalendarItems(Project $project, ProjectTask $task, array $items = []): void
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
