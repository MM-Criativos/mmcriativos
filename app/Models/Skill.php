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
}
