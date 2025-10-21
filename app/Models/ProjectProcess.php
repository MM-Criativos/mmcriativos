<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectProcess extends Model
{
    use HasFactory;

    protected $table = 'project_process';

    protected $fillable = [
        'project_id',
        'process_id',
        'description',
        'order',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function process()
    {
        return $this->belongsTo(Process::class);
    }

    public function images()
    {
        return $this->hasMany(ProjectImage::class);
    }
}

