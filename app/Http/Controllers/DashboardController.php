<?php

namespace App\Http\Controllers;

use App\Models\Budget;
use App\Models\Client;
use App\Models\Project;
use App\Models\ProjectTask;
use App\Models\ProjectTaskItem;
use App\Models\Skill;
use App\Models\SkillCompetency;
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

        $recentPeriodStart = Carbon::now()->subDays(30)->startOfDay();
        $budgetsLast30 = Budget::where('created_at', '>=', $recentPeriodStart)->count();
        $projectsLast30 = Project::where('created_at', '>=', $recentPeriodStart)->count();
        $tasksLast30 = ProjectTask::where('created_at', '>=', $recentPeriodStart)->count();
        $clientsLast30 = Client::where('created_at', '>=', $recentPeriodStart)->count();

        $projectsCompletedTotal = $summary['projects']['completed'];
        $tasksCompletedTotal = $summary['tasks']['completed'];
        $clientsTotal = Client::count();
        $budgetApprovedTotal = $summary['budgets']['approved'];

        $highlightCards = [
            [
                'title' => 'Orçamentos',
                'icon' => 'fa-duotone fa-message-dollar',
                'value' => $budgetsLast30,
                'meta' => $budgetApprovedTotal . ' aprovados',
            ],
            [
                'title' => 'Projetos',
                'icon' => 'fa-duotone fa-grid-2',
                'value' => $projectsLast30,
                'meta' => $projectsCompletedTotal . ' concluídos',
            ],
            [
                'title' => 'Tarefas',
                'icon' => 'fa-duotone fa-list-check',
                'value' => $tasksLast30,
                'meta' => $tasksCompletedTotal . ' concluídas',
            ],
            [
                'title' => 'Clientes',
                'icon' => 'fa-duotone fa-user-tie-hair',
                'value' => $clientsLast30,
                'meta' => $clientsTotal . ' no total',
            ],
        ];

        $clientFilter = $request->input('client');
        $professionalFilter = $request->input('professional');
        $skillFilter = $request->input('skill');
        $deadlineFilter = $request->input('deadline');

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
            ->when($clientFilter, function ($query, $clientFilter) {
                $query->whereHas('project', function ($projectQuery) use ($clientFilter) {
                    $projectQuery->where('client_id', $clientFilter);
                });
            })
            ->when($professionalFilter, function ($query, $professionalFilter) {
                $query->where('assigned_to', $professionalFilter);
            })
            ->when($skillFilter, function ($query, $skillFilter) {
                $query->where('skill_id', $skillFilter);
            })
            ->when($deadlineFilter, function ($query, $deadlineFilter) {
                try {
                    $deadlineDate = Carbon::parse($deadlineFilter);
                    $query->whereDate('planned_at', $deadlineDate);
                } catch (\Throwable $e) {
                    // ignore invalid dates
                }
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

        $today = Carbon::today();
        $selectedDayValue = $request->input('day', $today->toDateString());
        try {
            $selectedDate = Carbon::parse($selectedDayValue);
        } catch (\Throwable $e) {
            $selectedDate = $today->copy();
        }

        $windowStart = $today->copy()->subDays(3);
        $windowEnd = $today->copy()->addDays(3);
        if (!$selectedDate->between($windowStart, $windowEnd)) {
            $selectedDate = $today->copy();
        }

        $dayNames = ['Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sab', 'Dom'];
        $calendarDays = collect(range(-3, 3))
            ->map(function ($offset) use ($today, $selectedDate, $dayNames) {
                $date = $today->copy()->addDays($offset);
                $isoWeekday = $date->isoWeekday();
                return [
                    'date' => $date->toDateString(),
                    'label' => $dayNames[$isoWeekday - 1] ?? $date->format('D'),
                    'day' => $date->format('d'),
                    'is_active' => $date->isSameDay($selectedDate),
                ];
            })
            ->all();

        $dailyTasks = $this->getDailyTasksForDate($user, $selectedDate);

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

        $clientOptions = Client::orderBy('name')->get(['id', 'name']);
        $professionalOptions = User::where('is_approved', true)->orderBy('name')->get(['id', 'name']);
        $skillOptions = Skill::orderBy('name')->get(['id', 'name']);

        $topSkillRow = ProjectTask::query()
            ->where('assigned_to', $user?->id)
            ->where('status', ProjectTask::STATUS_DONE)
            ->whereNotNull('skill_id')
            ->select('skill_id', DB::raw('count(*) as total'))
            ->groupBy('skill_id')
            ->orderByDesc('total')
            ->first();

        $topSkillId = $topSkillRow->skill_id ?? null;
        $topSkillName = $topSkillId ? Skill::find($topSkillId)?->name : null;

        $topSkill = [
            'skill' => $topSkillName ?? 'Sem skill definida',
            'count' => (int) ($topSkillRow->total ?? 0),
        ];

        $competencyRows = ProjectTask::query()
            ->where('assigned_to', $user?->id)
            ->where('status', ProjectTask::STATUS_DONE)
            ->whereNotNull('skill_competency_id')
            ->when($topSkillId, fn($query) => $query->where('skill_id', $topSkillId))
            ->select('skill_competency_id', DB::raw('count(*) as total'))
            ->groupBy('skill_competency_id')
            ->orderByDesc('total')
            ->limit(3)
            ->get();

        $competencyNames = SkillCompetency::whereIn('id', $competencyRows->pluck('skill_competency_id')->filter())
            ->pluck('competency', 'id');

        $topCompetencies = $competencyRows->map(function ($row) use ($competencyNames) {
            return [
                'name' => $competencyNames[$row->skill_competency_id] ?? 'Competência #' . $row->skill_competency_id,
                'count' => (int) $row->total,
            ];
        })->all();

        $chartYear = (int) $request->input('chart_year', Carbon::now()->year);
        $availableYears = Project::selectRaw('YEAR(created_at) as year')
            ->whereNotNull('created_at')
            ->distinct()
            ->orderByDesc('year')
            ->pluck('year')
            ->filter()
            ->values()
            ->all();

        if (empty($availableYears)) {
            $availableYears = [Carbon::now()->year];
        }

        if (!in_array($chartYear, $availableYears, true)) {
            $chartYear = $availableYears[0];
        }

        $monthLabels = [];
        $projectStartedSeries = [];
        $projectFinishedSeries = [];

        for ($month = 1; $month <= 12; $month++) {
            $monthLabels[] = Carbon::create($chartYear, $month, 1)->format('M');
            $projectStartedSeries[] = Project::whereYear('created_at', $chartYear)
                ->whereMonth('created_at', $month)
                ->count();
            $projectFinishedSeries[] = Project::whereYear('finished_at', $chartYear)
                ->whereMonth('finished_at', $month)
                ->count();
        }

        $projectMonthChart = [
            'labels' => $monthLabels,
            'series' => [
                ['name' => 'Projetos iniciados', 'data' => $projectStartedSeries],
                ['name' => 'Projetos finalizados', 'data' => $projectFinishedSeries],
            ],
        ];

        $statusWeekStart = Carbon::now()->startOfWeek();
        $weekLabels = [];
        $statusWeekSeries = [
            ProjectTask::STATUS_PENDING => [],
            ProjectTask::STATUS_IN_PROGRESS => [],
            ProjectTask::STATUS_DONE => [],
        ];

        foreach (range(0, 6) as $offset) {
            $date = $statusWeekStart->copy()->addDays($offset);
            $weekLabels[] = $date->format('D');
            foreach ($statusWeekSeries as $status => $_) {
                $statusWeekSeries[$status][] = ProjectTask::where('status', $status)
                    ->whereDate('planned_at', $date)
                    ->count();
            }
        }

        $weeklyStatusChart = [
            'labels' => $weekLabels,
            'series' => collect($statusWeekSeries)
                ->map(function ($data, $status) use ($statusLabels) {
                    return [
                        'name' => $statusLabels[$status] ?? ucfirst($status),
                        'data' => $data,
                    ];
                })
                ->values()
                ->all(),
        ];

        $tasksByStatusCounts = ProjectTask::select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status')
            ->all();

        $tasksByStatus = [
            'labels' => array_values($statusLabels),
            'data' => collect($statusLabels)
                ->map(fn($label, $status) => (int) ($tasksByStatusCounts[$status] ?? 0))
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
                'data' => $tasksBySkillRaw->pluck('total')->map(fn($count) => (int) $count)->all(),
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

        /**
         * 🔸 Projetos Envolvidos (total)
         */
        $involvedProjectsQuery = Project::query()
            ->where(function ($query) use ($user) {
                $query->whereHas('tasks', function ($taskQuery) use ($user) {
                    $taskQuery->where('assigned_to', $user?->id);
                })->orWhereHas('taskItems', function ($taskItemQuery) use ($user) {
                    $taskItemQuery->where('assigned_to', $user?->id);
                });
            });

        $involvedProjectsCount = (clone $involvedProjectsQuery)
            ->distinct('projects.id')
            ->count('projects.id');

        /**
         * 🔸 Projetos Concluídos (subconjunto)
         * Considera concluído quando finished_at NÃO é nulo
         */
        $completedProjectsCount = (clone $involvedProjectsQuery)
            ->whereNotNull('finished_at')
            ->distinct('projects.id')
            ->count('projects.id');



        /**
         * 🔸 Cards
         */
        $personalCards = [
            [
                'title' => 'Tarefas Atribuídas',
                'value' => $assignedTasksCount,
                'meta' => $assignedItemsCount . ' Concluídas',
                'icon' => 'fa-duotone fa-check-to-slot',
            ],
            [
                'title' => 'Projetos Envolvidos',
                'value' => $involvedProjectsCount,
                'meta' => $completedProjectsCount . ' Concluídos',
                'icon' => 'fa-duotone fa-diagram-project',
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
            'highlightCards' => $highlightCards,
            'clientOptions' => $clientOptions,
            'professionalOptions' => $professionalOptions,
            'skillOptions' => $skillOptions,
            'clientFilter' => $clientFilter,
            'professionalFilter' => $professionalFilter,
            'skillFilter' => $skillFilter,
            'deadlineFilter' => $deadlineFilter,
            'calendarDays' => $calendarDays,
            'selectedDate' => $selectedDate,
            'dailyTasks' => $dailyTasks,
            'topSkill' => $topSkill,
            'topCompetencies' => $topCompetencies,
            'chartYear' => $chartYear,
            'availableYears' => $availableYears,
            'projectMonthChart' => $projectMonthChart,
            'weeklyStatusChart' => $weeklyStatusChart,
        ]);
    }

    public function dayTasks(Request $request)
    {
        $user = $request->user();
        $today = Carbon::today();
        $selectedDay = $request->query('day');
        try {
            $selectedDate = $selectedDay ? Carbon::parse($selectedDay) : $today->copy();
        } catch (\Throwable $e) {
            $selectedDate = $today->copy();
        }

        $windowStart = $today->copy()->subDays(3);
        $windowEnd = $today->copy()->addDays(3);
        if (!$selectedDate->between($windowStart, $windowEnd)) {
            $selectedDate = $today->copy();
        }

        $dailyTasks = $this->getDailyTasksForDate($user, $selectedDate);
        $html = view('dashboard.partials.daily-tasks', [
            'dailyTasks' => $dailyTasks,
            'selectedDate' => $selectedDate,
        ])->render();

        return response()->json([
            'html' => $html,
            'date' => $selectedDate->toDateString(),
        ]);
    }

    private function getDailyTasksForDate(?User $user, Carbon $selectedDate)
    {
        if (!$user) {
            return collect();
        }

        $tasks = ProjectTask::query()
            ->with(['project', 'skill'])
            ->where('assigned_to', $user->id)
            ->where(function ($query) use ($selectedDate) {
                $query->whereDate('planned_at', $selectedDate)
                    ->orWhere(function ($overdue) use ($selectedDate) {
                        $overdue->whereDate('planned_at', '<', $selectedDate)
                            ->where('status', '!=', ProjectTask::STATUS_DONE);
                    });
            })
            ->get();

        $sorted = $tasks->sort(function ($a, $b) use ($selectedDate) {
            $priorityA = $this->calculateTaskPriority($a, $selectedDate);
            $priorityB = $this->calculateTaskPriority($b, $selectedDate);

            if ($priorityA === $priorityB) {
                $timeA = $a->planned_at?->timestamp ?? PHP_INT_MAX;
                $timeB = $b->planned_at?->timestamp ?? PHP_INT_MAX;
                return $timeA <=> $timeB;
            }

            return $priorityA <=> $priorityB;
        })->values();

        return $sorted->take(3);
    }

    private function calculateTaskPriority(ProjectTask $task, Carbon $selectedDate): int
    {
        $planned = $task->planned_at;
        if ($planned && $planned->isBefore($selectedDate) && $task->status !== ProjectTask::STATUS_DONE) {
            return 0;
        }
        if ($planned && $planned->isSameDay($selectedDate) && $task->status !== ProjectTask::STATUS_DONE) {
            return 1;
        }
        if ($task->status === ProjectTask::STATUS_PENDING) {
            return 2;
        }
        if ($task->status === ProjectTask::STATUS_IN_PROGRESS) {
            return 3;
        }
        return 4;
    }
}
