<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlanningBriefingQualitative extends Model
{
    use HasFactory;

    protected $table = 'planning_briefing_qualitatives';

    protected $fillable = [
        'project_id',
        'client_id',
        'title',
        'status',
        'selected_templates',
        'meta',
        'started_at',
        'completed_at',
    ];

    protected $casts = [
        'selected_templates' => 'array',
        'meta' => 'array',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    /* 🔗 RELACIONAMENTOS */

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function responses()
    {
        return $this->hasMany(PlanningBriefingQualitativeResponse::class, 'briefing_id');
    }
}
