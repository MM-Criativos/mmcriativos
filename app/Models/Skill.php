<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Skill extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'icon',
        'description',
        'thumb',
        'cover',
    ];

    public function competencies()
    {
        return $this->hasMany(SkillCompetency::class);
    }

    public function projects()
    {
        // Via pivot project_skill_competency (com campo extra skill_competency_id)
        return $this->belongsToMany(Project::class, 'project_skill_competency')
            ->withPivot(['skill_competency_id', 'order'])
            ->withTimestamps();
    }

    public function info()
    {
        return $this->hasOne(SkillInfo::class);
    }

    // Normaliza o ícone para classes CSS, caso o campo tenha sido salvo como tag <i>
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
