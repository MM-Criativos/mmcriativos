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

    // Accessor que normaliza o campo 'icon' (aceita tag <i> completa ou apenas classes)
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
