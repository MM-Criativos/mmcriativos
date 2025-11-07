<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectTaskItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'project_task_id',
        'skill_id',
        'skill_competency_id',
        'assigned_to',
        'title',
        'description',
        'is_done',
        'done_at',
        'order',
    ];

    protected $casts = [
        'is_done' => 'boolean',
        'done_at' => 'datetime',
    ];

    /*
    |--------------------------------------------------------------------------
    | 🔗 RELACIONAMENTOS
    |--------------------------------------------------------------------------
    */

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function task()
    {
        return $this->belongsTo(ProjectTask::class, 'project_task_id');
    }

    public function skill()
    {
        return $this->belongsTo(Skill::class);
    }

    public function competency()
    {
        return $this->belongsTo(SkillCompetency::class, 'skill_competency_id');
    }

    public function assignedUser()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    /*
    |--------------------------------------------------------------------------
    | 🧠 MÉTODOS AUXILIARES
    |--------------------------------------------------------------------------
    */

    public function markAsDone(): void
    {
        $this->update([
            'is_done' => true,
            'done_at' => now(),
        ]);
    }

    public function markAsUndone(): void
    {
        $this->update([
            'is_done' => false,
            'done_at' => null,
        ]);
    }

    public function isCompleted(): bool
    {
        return $this->is_done === true;
    }
}
