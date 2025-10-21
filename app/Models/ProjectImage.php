<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectImage extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_process_id',
        'image',
        'title',
        'description',
        'solution',
        'order',
    ];

    public function projectProcess()
    {
        return $this->belongsTo(ProjectProcess::class);
    }
}

