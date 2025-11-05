<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlanningKickoff extends Model
{
    use HasFactory;

    protected $table = 'planning_kickoffs';

    protected $fillable = [
        'project_id',
        'client_id',
        'titulo',
        'objetivo',
        'resumo_alinhamento',
        'tarefas_iniciais',
        'responsaveis',
        'materiais_apresentados',
        'status',
        'data_reuniao',
        'approved_at',
    ];

    protected $casts = [
        'data_reuniao' => 'datetime',
        'approved_at' => 'datetime',
    ];

    /* 🔗 RELACIONAMENTOS */

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }
}
