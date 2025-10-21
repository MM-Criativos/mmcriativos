<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectSkillCompetency extends Model
{
    use HasFactory;

    protected $table = 'project_skill_competency';

    protected $fillable = [
        'project_id',
        'skill_id',
        'skill_competency_id',
        'order',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function skill()
    {
        return $this->belongsTo(Skill::class);
    }

    public function competency()
    {
        return $this->belongsTo(SkillCompetency::class, 'skill_competency_id');
    }
}

