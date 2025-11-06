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
        'is_completed' => 'boolean',
        'answered_at' => 'datetime',
    ];

    protected $appends = [
        'answer_value',
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

    /**
     * Accessor that returns the normalized answer value.
     *
     * @return array|string|null
     */
    public function getAnswerValueAttribute()
    {
        $raw = $this->attributes['answer'] ?? null;

        if (is_null($raw)) {
            return null;
        }

        if (is_array($raw)) {
            return $raw;
        }

        // Attempt to decode JSON; if it fails, fall back to the raw string.
        $decoded = json_decode($raw, true);

        if (json_last_error() === JSON_ERROR_NONE) {
            return $decoded;
        }

        return $raw;
    }
}
