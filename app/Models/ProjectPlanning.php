<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectPlanning extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'client_id',
        'status',
        'started_at',
        'completed_at',
    ];

    /* 🔗 RELACIONAMENTOS PRINCIPAIS */

    // Cada planejamento pertence a um projeto
    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    // Cada planejamento pertence a um cliente
    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    // Respostas do briefing (régua perceptiva)
    public function briefingResponses()
    {
        return $this->hasMany(PlanningBriefingResponse::class, 'project_id', 'project_id');
    }

    // (futuro) Briefing qualitativo
    public function briefingQualitativos()
    {
        return $this->hasMany(PlanningBriefingQualitative::class, 'project_id', 'project_id');
    }

    // (futuro) Interpretação e Escopo
    public function interpretacao()
    {
        return $this->hasOne(PlanningInterpretacao::class, 'project_id', 'project_id');
    }

    // (futuro) Kickoff
    public function kickoff()
    {
        return $this->hasOne(PlanningKickoff::class, 'project_id', 'project_id');
    }
}
