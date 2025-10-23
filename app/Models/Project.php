<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    use HasFactory;

    protected $fillable = [
        'cover',
        'thumb',
        'skill_cover',
        'name',
        'slug',
        'summary',
        'client_id',
        'service_id',
        'video',
        'finished_at',
    ];

    protected $casts = [
        'finished_at' => 'date',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function challenges()
    {
        return $this->hasMany(ProjectChallenge::class);
    }

    public function solutions()
    {
        return $this->hasMany(ProjectSolution::class);
    }

    public function processes()
    {
        // Pivot com dados extras (description, order)
        return $this->belongsToMany(Process::class, 'project_process')
            ->withPivot(['description', 'order'])
            ->withTimestamps();
    }

    public function projectProcesses()
    {
        return $this->hasMany(ProjectProcess::class);
    }

    public function images()
    {
        // Todas as imagens via etapas de processo
        return $this->hasManyThrough(
            ProjectImage::class,
            ProjectProcess::class,
            'project_id',        // Foreign key on project_process
            'project_process_id' // Foreign key on project_images
        );
    }

    public function skills()
    {
        return $this->belongsToMany(Skill::class, 'project_skill_competency')
            ->withPivot(['skill_competency_id', 'order'])
            ->withTimestamps();
    }

    public function skillLinks()
    {
        return $this->hasMany(ProjectSkillCompetency::class);
    }
}
