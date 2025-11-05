<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlanningBriefingQualitativeResponse extends Model
{
    use HasFactory;

    protected $table = 'planning_briefing_qualitative_responses';

    protected $fillable = [
        'briefing_id',
        'project_id',
        'client_id',
        'template_id',
        'type',
        'answer',
        'file_path',
        'is_completed',
        'answered_at',
    ];

    protected $casts = [
        'answer' => 'array',
        'is_completed' => 'boolean',
        'answered_at' => 'datetime',
    ];

    /* 🔗 RELACIONAMENTOS */

    public function briefing()
    {
        return $this->belongsTo(PlanningBriefingQualitative::class, 'briefing_id');
    }

    public function template()
    {
        return $this->belongsTo(QualitativeTemplate::class, 'template_id');
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }
}
