<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlanningBriefingQualitative extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'client_id',
        'template_id',
        'title',
        'status',
        'selected_templates',
        'meta',
        'started_at',
        'completed_at'
    ];

    protected $casts = [
        'meta' => 'array',
        'selected_templates' => 'array',
        'started_at' => 'datetime',
        'completed_at' => 'datetime'
    ];

    /**
     * Get the project that owns the briefing
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Get the client that owns the briefing
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * Get the template this briefing is based on
     */
    public function template(): BelongsTo
    {
        return $this->belongsTo(QualitativeTemplate::class, 'template_id');
    }

    /**
     * Get all responses for this briefing
     */
    public function responses()
    {
        return $this->hasMany(PlanningBriefingQualitativeResponse::class, 'qualitative_id');
    }
}
