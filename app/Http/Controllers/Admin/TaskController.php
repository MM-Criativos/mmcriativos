<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\ProjectTask;
use App\Models\Skill;
use Illuminate\Http\Request;
use Illuminate\View\View;

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
            'search' => $search,
            'selectedProject' => $projectId,
            'selectedSkill' => $skillId,
            'selectedStatus' => $status,
            'availableStatuses' => $availableStatuses,
        ]);
    }

    public function completed(Request $request): View
    {
        $search = trim((string) $request->query('q', ''));
        $projectId = $request->query('project_id');
        $skillId = $request->query('skill_id');

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
            'search' => $search,
            'selectedProject' => $projectId,
            'selectedSkill' => $skillId,
        ]);
    }
}
