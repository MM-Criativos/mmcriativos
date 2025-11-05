<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlanningBriefingResponse extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'client_id',
        'briefing_regua_id',
        'value',
        'comment',
        'attachment',
    ];

    /* 🔗 RELACIONAMENTOS */

    // Resposta pertence a uma régua (pergunta)
    public function regua()
    {
        return $this->belongsTo(PlanningBriefingRegua::class, 'briefing_regua_id');
    }

    // Resposta pertence a um projeto
    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    // Resposta pertence a um cliente
    public function client()
    {
        return $this->belongsTo(Client::class);
    }
}
