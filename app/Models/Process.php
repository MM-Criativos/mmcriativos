<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Process extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'icon',
        'order',
    ];

    public function projects()
    {
        return $this->belongsToMany(Project::class, 'project_process')
            ->withPivot(['description', 'order'])
            ->withTimestamps();
    }

    public function projectProcesses()
    {
        return $this->hasMany(ProjectProcess::class);
    }
}

