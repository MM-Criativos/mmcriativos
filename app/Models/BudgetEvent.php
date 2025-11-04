<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BudgetEvent extends Model
{
    use HasFactory;

    protected $fillable = [
        'budget_id',
        'event',
        'meta',
        'user_id',
        'user_type',
    ];

    protected $casts = [
        'meta' => 'array',
    ];

    public function budget()
    {
        return $this->belongsTo(Budget::class);
    }
}

