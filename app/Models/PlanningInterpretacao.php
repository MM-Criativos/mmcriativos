<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlanningInterpretacao extends Model
{
    use HasFactory;

    protected $table = 'planning_interpretacoes';

    protected $fillable = [
        'project_id',
        'client_id',
        'analise_publico',
        'analise_concorrencia',
        'diretrizes_visuais',
        'definicao_escopo',
        'observacoes_tecnicas',
        'status',
        'approved_at',
    ];

    protected $casts = [
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
