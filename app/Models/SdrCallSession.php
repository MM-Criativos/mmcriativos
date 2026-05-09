<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SdrCallSession extends Model
{
    protected $fillable = [
        'lead_id', 'sdr_name', 'company', 'contact',
        'started_at', 'ended_at', 'path', 'outcome', 'notes',
    ];

    protected $casts = [
        'path'       => 'array',
        'started_at' => 'datetime',
        'ended_at'   => 'datetime',
    ];

    public function durationSeconds(): int
    {
        if (!$this->started_at || !$this->ended_at) return 0;
        return $this->started_at->diffInSeconds($this->ended_at);
    }

    public function outcomeLabel(): string
    {
        return match ($this->outcome) {
            'reuniao_agendada'      => 'Reunião agendada',
            'retorno_agendado'      => 'Retorno agendado',
            'sem_interesse'         => 'Sem interesse',
            'encerrado_manualmente' => 'Encerrado manualmente',
            default                 => '—',
        };
    }
}
