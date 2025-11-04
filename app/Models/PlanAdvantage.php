<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlanAdvantage extends Model
{
    use HasFactory;

    protected $fillable = [
        'plan_id',
        'title',
        'description',
    ];

    /*
    |--------------------------------------------------------------------------
    | 🔗 RELACIONAMENTOS
    |--------------------------------------------------------------------------
    */

    // Cada vantagem pertence a um plano
    public function plan()
    {
        return $this->belongsTo(Plan::class);
    }

    /*
    |--------------------------------------------------------------------------
    | 🧠 MÉTODOS AUXILIARES
    |--------------------------------------------------------------------------
    */

    // Retornar apenas o texto da vantagem
    public function getDisplayTextAttribute(): string
    {
        return $this->description
            ? "{$this->title} — {$this->description}"
            : $this->title;
    }
}
