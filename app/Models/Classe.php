<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Classe extends Model
{
    use HasFactory;

    protected $table = 'classes';

    protected $fillable = [
        'hierarquia',
        'classe',
        'description',
        'skills',
        'build',
    ];

    protected $casts = [
        'skills' => 'array',
        'hierarquia' => 'integer',
    ];

    public function users()
    {
        return $this->belongsToMany(User::class, 'class_user', 'class_id', 'user_id')
            ->withTimestamps();
    }
}
