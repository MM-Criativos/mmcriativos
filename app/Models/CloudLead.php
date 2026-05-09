<?php

namespace App\Models;

use App\Enums\CloudLeadEtapa;
use App\Enums\CloudLeadSegmento;
use App\Enums\CloudLeadStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class CloudLead extends Model
{
    use HasFactory;

    protected $table = 'cloud_leads';

    protected $fillable = [
        'empresa',
        'segmento',
        'status',
        'etapa',
        'responsavel_id',
        'created_by_id',
        'contato',
        'seguidores',
        'tomador_de_decisao',
        'instagram_url',
        'descricao',
    ];

    protected $casts = [
        'status'   => CloudLeadStatus::class,
        'etapa'    => CloudLeadEtapa::class,
        'segmento' => CloudLeadSegmento::class,
    ];

    // ─── Relationships ───────────────────────────────────────────

    public function responsavel()
    {
        return $this->belongsTo(User::class, 'responsavel_id');
    }

    public function criador()
    {
        return $this->belongsTo(User::class, 'created_by_id');
    }

    // ─── Scopes ──────────────────────────────────────────────────

    /**
     * Aplica filtros da request: etapa, status, responsavel_id, busca.
     */
    public function scopeFilter(Builder $query, Request $request): Builder
    {
        if ($request->filled('etapa')) {
            $query->where('etapa', $request->string('etapa'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        if ($request->filled('responsavel_id')) {
            $query->where('responsavel_id', $request->integer('responsavel_id'));
        }

        if ($request->filled('segmento')) {
            $query->where('segmento', $request->string('segmento'));
        }

        if ($request->filled('busca')) {
            $busca = $request->string('busca');
            $query->where(function (Builder $q) use ($busca) {
                $q->where('empresa', 'like', "%{$busca}%")
                  ->orWhere('tomador_de_decisao', 'like', "%{$busca}%");
            });
        }

        return $query;
    }
}
