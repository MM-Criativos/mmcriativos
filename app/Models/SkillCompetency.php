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
}

