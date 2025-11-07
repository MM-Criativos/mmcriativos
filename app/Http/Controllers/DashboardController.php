<?php

namespace App\Http\Controllers;

use App\Models\Budget;
use App\Models\Project;
use App\Models\ProjectTask;
use App\Models\ProjectTaskItem;
use App\Models\Skill;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __invoke(Request $request)
    {
        $user = $request->user();

        $summary = [
            'projects' => [
                'active' => Project::whereNull('finished_at')->count(),
                'completed' => Project::whereNotNull('finished_at')->count(),
            ],
            'tasks' => [
                'active' => ProjectTask::whereIn('status', [
                    ProjectTask::STATUS_PENDING,
                    ProjectTask::STATUS_IN_PROGRESS,
                ])->count(),
                'completed' => ProjectTask::where('status', ProjectTask::STATUS_DONE)->count(),
            ],
            'team' => [
                'active' => User::where('is_approved', true)->count(),
            ],
            'budgets' => [
                'sent' => Budget::whereIn('status', ['sent', 'opened'])->count(),
                'approved' => Budget::where('status', 'accepted')->count(),
            ],
        ];

        $summaryCards = [
            [
                'title' => 'Projetos',
                'icon' => 'fa-regular fa-folder-open',
                'metrics' => [
                    ['label' => 'Ativos', 'value' => $summary['projects']['active']],
                    ['label' => 'Concluidos', 'value' => $summary['projects']['completed']],
                ],
            ],
            [
                'title' => 'Tarefas',
                'icon' => 'fa-regular fa-square-check',
                'metrics' => [
                    ['label' => 'Em andamento', 'value' => $summary['tasks']['active']],
                    ['label' => 'Concluidas', 'value' => $summary['tasks']['completed']],
                ],
            ],
            [
                'title' => 'Equipe',
                'icon' => 'fa-regular fa-user',
                'metrics' => [
                    ['label' => 'Ativos', 'value' => $summary['team']['active']],
                ],
            ],
            [
                'title' => 'Orcamentos',
                'icon' => 'fa-regular fa-file-lines',
                'metrics' => [
                    ['label' => 'Enviados', 'value' => $summary['budgets']['sent']],
                    ['label' => 'Aprovados', 'value' => $summary['budgets']['approved']],
                ],
            ],
        ];

        $search = trim((string) $request->get('q'));
        $taskStatusFilter = (string) $request->get('status', '');

        $tasksQuery = ProjectTask::query()
            ->with(['project', 'skill', 'assignedUser'])
            ->when($search, function ($query) use ($search) {
                $query->where(function ($nested) use ($search) {
                    $nested->where('title', 'like', '%' . $search . '%')
                        ->orWhereHas('project', function ($projectQuery) use ($search) {
                            $projectQuery->where('name', 'like', '%' . $search . '%');
                        })
                        ->orWhereHas('assignedUser', function ($userQuery) use ($search) {
                            $userQuery->where('name', 'like', '%' . $search . '%');
                        });
                });
            })
            ->when($taskStatusFilter !== '', function ($query) use ($taskStatusFilter) {
                $query->where('status', $taskStatusFilter);
            })
            ->orderByRaw(
                'CASE ' .
                'WHEN status = ? THEN 0 ' .
                'WHEN status = ? THEN 1 ' .
                'WHEN status = ? THEN 2 ' .
                'ELSE 3 END',
                [
                    ProjectTask::STATUS_IN_PROGRESS,
                    ProjectTask::STATUS_PENDING,
                    ProjectTask::STATUS_DONE,
                ]
            )
            ->orderByRaw('COALESCE(planned_at, completed_at, created_at) ASC');

        $tasks = $tasksQuery->paginate(10)->withQueryString();

        $statusBadges = [
            ProjectTask::STATUS_PENDING => [
                'label' => 'Pendente',
                'classes' => 'bg-amber-100 text-amber-700 border border-amber-200',
            ],
            ProjectTask::STATUS_IN_PROGRESS => [
                'label' => 'Em andamento',
                'classes' => 'bg-blue-100 text-blue-700 border border-blue-200',
            ],
            ProjectTask::STATUS_DONE => [
                'label' => 'Concluida',
                'classes' => 'bg-emerald-100 text-emerald-700 border border-emerald-200',
            ],
        ];

        $statusLabels = [
            ProjectTask::STATUS_PENDING => 'Pendente',
            ProjectTask::STATUS_IN_PROGRESS => 'Em andamento',
            ProjectTask::STATUS_DONE => 'Concluidos',
        ];

        $tasksByStatusCounts = ProjectTask::select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status')
            ->all();

        $tasksByStatus = [
            'labels' => array_values($statusLabels),
            'data' => collect($statusLabels)
                ->map(fn ($label, $status) => (int) ($tasksByStatusCounts[$status] ?? 0))
                ->values()
                ->all(),
        ];

        $tasksBySkillRaw = ProjectTask::select('skill_id', DB::raw('count(*) as total'))
            ->groupBy('skill_id')
            ->orderByDesc('total')
            ->limit(6)
            ->get();

        $skillNames = Skill::whereIn('id', $tasksBySkillRaw->pluck('skill_id')->filter())
            ->pluck('name', 'id');

        if ($tasksBySkillRaw->isEmpty()) {
            $tasksBySkill = [
                'labels' => ['Sem dados'],
                'data' => [0],
            ];
        } else {
            $tasksBySkill = [
                'labels' => $tasksBySkillRaw->map(function ($row) use ($skillNames) {
                    if (!$row->skill_id) {
                        return 'Sem skill';
                    }
                    return $skillNames[$row->skill_id] ?? 'Skill #' . $row->skill_id;
                })->all(),
                'data' => $tasksBySkillRaw->pluck('total')->map(fn ($count) => (int) $count)->all(),
            ];
        }

        $weeksLookback = 5;
        $weeklyCompleted = collect();
        $now = Carbon::now();
        for ($i = $weeksLookback; $i >= 0; $i--) {
            $start = $now->copy()->subWeeks($i)->startOfWeek();
            $end = $start->copy()->endOfWeek();
            $weeklyCompleted->push([
                'label' => $start->format('d/m') . ' - ' . $end->format('d/m'),
                'count' => ProjectTask::where('status', ProjectTask::STATUS_DONE)
                    ->whereBetween('completed_at', [$start, $end])
                    ->count(),
            ]);
        }

        $analytics = [
            'projectProgress' => [
                'labels' => ['Em andamento', 'Concluidos'],
                'data' => [
                    (int) $summary['projects']['active'],
                    (int) $summary['projects']['completed'],
                ],
            ],
            'tasksByStatus' => $tasksByStatus,
            'tasksBySkill' => $tasksBySkill,
            'weeklyCompleted' => [
                'labels' => $weeklyCompleted->pluck('label')->all(),
                'data' => $weeklyCompleted->pluck('count')->all(),
            ],
        ];

        $personalFilters = [
            'status' => $request->input('personal_status', ''),
            'end' => $request->input('personal_end', ''),
        ];

        $personalTasksQuery = ProjectTask::query()
            ->with(['project', 'skill'])
            ->where('assigned_to', $user?->id);

        if ($personalFilters['status'] !== '') {
            $personalTasksQuery->where('status', $personalFilters['status']);
        }

        if ($personalFilters['end'] !== '') {
            try {
                $personalEnd = Carbon::parse($personalFilters['end'])->endOfDay();
                $personalTasksQuery->whereDate('planned_at', '<=', $personalEnd);
            } catch (\Throwable $e) {
                //
            }
        }

        $personalTasksQuery->orderByRaw(
            'CASE ' .
            'WHEN status = ? THEN 0 ' .
            'WHEN status = ? THEN 1 ' .
            'WHEN status = ? THEN 2 ' .
            'ELSE 3 END',
            [
                ProjectTask::STATUS_IN_PROGRESS,
                ProjectTask::STATUS_PENDING,
                ProjectTask::STATUS_DONE,
            ]
        )->orderByRaw('COALESCE(planned_at, completed_at, created_at) ASC');

        $personalTasks = $personalTasksQuery
            ->paginate(8, ['*'], 'personal_page')
            ->withQueryString();

        $assignedTasksCount = ProjectTask::where('assigned_to', $user?->id)->count();
        $assignedItemsCount = ProjectTaskItem::where('assigned_to', $user?->id)->count();

        $involvedProjectsCount = Project::query()
            ->where(function ($query) use ($user) {
                $query->whereHas('tasks', function ($taskQuery) use ($user) {
                    $taskQuery->where('assigned_to', $user?->id);
                })->orWhereHas('taskItems', function ($taskItemQuery) use ($user) {
                    $taskItemQuery->where('assigned_to', $user?->id);
                });
            })
            ->distinct('projects.id')
            ->count('projects.id');

        $personalCards = [
            [
                'title' => 'Tarefas atribuídas',
                'value' => $assignedTasksCount,
                'meta' => $assignedItemsCount . ' subtarefas',
            ],
            [
                'title' => 'Projetos envolvidos',
                'value' => $involvedProjectsCount,
                'meta' => 'Projetos com entregas sob sua responsabilidade',
            ],
        ];

        return view('dashboard', [
            'summaryCards' => $summaryCards,
            'tasks' => $tasks,
            'search' => $search,
            'taskStatusFilter' => $taskStatusFilter,
            'statusBadges' => $statusBadges,
            'analytics' => $analytics,
            'personalCards' => $personalCards,
            'personalTasks' => $personalTasks,
            'personalFilters' => $personalFilters,
            'personalStatuses' => ProjectTask::STATUSES,
            'taskStatuses' => ProjectTask::STATUSES,
        ]);
    }
}
