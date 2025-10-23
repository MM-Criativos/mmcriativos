<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SkillCompetency extends Model
{
    use HasFactory;

    protected $fillable = [
        'skill_id',
        'competency',
        'icon',
        'description',
    ];

    public function skill()
    {
        return $this->belongsTo(Skill::class);
    }

    public function projects()
    {
        return $this->belongsToMany(Project::class, 'project_skill_competency')
            ->withPivot(['skill_id', 'order'])
            ->withTimestamps();
    }

    // Normaliza o ícone para classes CSS, caso esteja salvo como tag <i>
    public function getIconClassAttribute(): string
    {
        $raw = (string) ($this->icon ?? '');
        $raw = trim($raw);
        if ($raw === '') return '';
        if (strpos($raw, '<') !== false) {
            if (preg_match('/class\s*=\s*"([^"]+)"/i', $raw, $m)) {
                return trim($m[1]);
            }
            return trim(strip_tags($raw));
        }
        return $raw;
    }
}
