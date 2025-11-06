<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Collection;

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
        'meta' => 'json',
        'selected_templates' => 'json',
        'started_at' => 'timestamp',
        'completed_at' => 'timestamp',
        'created_at' => 'timestamp',
        'updated_at' => 'timestamp'
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
        return $this->hasMany(PlanningBriefingQualitativeResponse::class, 'briefing_id');
    }

    /**
     * Sync the qualitative questions for a project using the provided template IDs.
     */
    public static function syncForProject(Project $project, Collection|array $templateIds): void
    {
        $ids = collect($templateIds)
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->unique()
            ->values();

        static::where('project_id', $project->id)->delete();

        if ($ids->isEmpty()) {
            return;
        }

        $timestamp = now();

        foreach ($ids as $templateId) {
            static::create([
                'project_id' => $project->id,
                'client_id' => $project->client_id,
                'template_id' => $templateId,
                'title' => 'Briefing Qualitativo',
                'status' => 'draft',
                'selected_templates' => null,
                'meta' => null,
                'started_at' => $timestamp,
                'completed_at' => null,
            ]);
        }
    }

    /**
     * Ensure qualitative questions are normalized (one row per template) and return them.
     */
    public static function normalizeForProject(Project $project): Collection
    {
        $qualitatives = static::with('template')
            ->where('project_id', $project->id)
            ->get();

        $needsNormalization = $qualitatives->contains(function ($qualitative) {
            $selected = $qualitative->selected_templates;

            if (!$selected) {
                return false;
            }

            if (is_string($selected)) {
                $decoded = json_decode($selected, true);
                $selected = is_array($decoded) ? $decoded : [];
            }

            return (empty($qualitative->template_id) || !$qualitative->template) && !empty($selected);
        });

        if ($needsNormalization) {
            $templateIds = $qualitatives->flatMap(function ($qualitative) {
                $selected = $qualitative->selected_templates;

                if (!$selected) {
                    return [];
                }

                if (is_string($selected)) {
                    $decoded = json_decode($selected, true);
                    $selected = is_array($decoded) ? $decoded : [];
                }

                return collect($selected)->map(fn ($id) => (int) $id);
            });

            static::syncForProject($project, $templateIds);

            $qualitatives = static::with('template')
                ->where('project_id', $project->id)
                ->get();
        }

        return $qualitatives;
    }
}
