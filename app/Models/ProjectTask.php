<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectTask extends Model
{
    use HasFactory;

    public const STATUS_PENDING = 'pending';
    public const STATUS_IN_PROGRESS = 'in_progress';
    public const STATUS_DONE = 'done';

    public const STATUSES = [
        self::STATUS_PENDING => 'Pendente',
        self::STATUS_IN_PROGRESS => 'Em andamento',
        self::STATUS_DONE => 'Concluído',
    ];

    public const STATUS_BADGES = [
        self::STATUS_PENDING => [
            'label' => 'Não iniciado',
            'classes' => 'badge-pendent',
        ],
        self::STATUS_IN_PROGRESS => [
            'label' => 'Em progresso',
            'classes' => 'badge-inprogress',
        ],
        self::STATUS_DONE => [
            'label' => 'Completo',
            'classes' => 'badge-completed',
        ],
    ];

    protected $fillable = [
        'project_id',
        'skill_id',
        'skill_competency_id',
        'title',
        'description',
        'status',
        'assigned_to',
        'planned_at',
        'completed_at',
    ];

    protected $casts = [
        'completed_at' => 'datetime',
        'planned_at' => 'datetime',
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

    public function items()
    {
        return $this->hasMany(ProjectTaskItem::class, 'project_task_id')
            ->orderBy('order')
            ->orderBy('id');
    }

    /*
    |--------------------------------------------------------------------------
    | 🧠 MÉTODOS AUXILIARES
    |--------------------------------------------------------------------------
    */

    public function isCompleted(): bool
    {
        return $this->status === 'done';
    }

    public function markAsCompleted(): void
    {
        $this->update([
            'status' => 'done',
            'completed_at' => now(),
        ]);
    }

    public function markInProgress(): void
    {
        $this->update([
            'status' => 'in_progress',
            'completed_at' => null,
        ]);
    }

    public function markPending(): void
    {
        $this->update([
            'status' => 'pending',
            'completed_at' => null,
        ]);
    }
}
