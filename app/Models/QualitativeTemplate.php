<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QualitativeTemplate extends Model
{
    use HasFactory;

    protected $table = 'qualitative_templates';

    protected $fillable = [
        'service_type',      // tipo de serviço (global, landing, sistema, etc.)
        'category',          // categoria da pergunta (ex: Público e Segmento)
        'question',          // texto da pergunta
        'type',              // tipo de campo (text, textarea, choice, multi_choice, file)
        'options',           // opções JSON
        'placeholder',       // dica de exemplo
        'sort_order',        // ordem de exibição
        'is_active',         // se está ativa ou não
    ];

    protected $casts = [
        'options' => 'array',
        'is_active' => 'boolean',
    ];

    /* 🔗 RELACIONAMENTOS */

    public function responses()
    {
        return $this->hasMany(PlanningBriefingQualitativeResponse::class, 'template_id');
    }

    /* 💡 SCOPES ÚTEIS */

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    public function scopeByService($query, $serviceType)
    {
        return $query->where('service_type', $serviceType);
    }

    /* 🧠 MÉTODOS AUXILIARES */

    public function formattedOptions()
    {
        // Retorna as opções formatadas (ex: ["Sim", "Não"] ou vazio)
        return $this->options ?? [];
    }

    public function isChoice()
    {
        return in_array($this->type, ['choice', 'multi_choice']);
    }

    public function isFileUpload()
    {
        return $this->type === 'file';
    }
}
