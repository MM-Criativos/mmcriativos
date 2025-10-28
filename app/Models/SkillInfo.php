<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SkillInfo extends Model
{
    use HasFactory;

    protected $fillable = [
        'skill_id',
        'image',
        'subtitle',
        'title',
        'description',
    ];

    public function skill()
    {
        return $this->belongsTo(Skill::class);
    }
}

