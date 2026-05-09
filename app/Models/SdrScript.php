<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SdrScript extends Model
{
    protected $fillable = ['name', 'version', 'is_active', 'flow'];

    protected $casts = [
        'flow'      => 'array',
        'is_active' => 'boolean',
    ];

    public static function active(): ?self
    {
        return self::where('is_active', true)->latest()->first();
    }
}
