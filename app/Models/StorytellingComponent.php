<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StorytellingComponent extends Model
{
    use HasFactory;

    protected $table = 'storytelling_components';

    protected $fillable = [
        'name',
        'slug',
        'layer',
        'component_type',
        'description',
        'props',
    ];

    protected $casts = [
        'props' => 'array',
    ];

    /**
     * 🔗 Relacionamento: páginas globais que usam esse componente.
     *
     * Mesmo que a tabela global_page_component seja opcional, manter essa relação
     * permite reaproveitar o conceito de “modelos de página” dentro da biblioteca.
     */
    public function globalPages()
    {
        return $this->belongsToMany(
            GlobalPage::class,
            'global_page_component', // opcional, se existir
            'component_id',
            'page_id'
        )
            ->withPivot('order', 'settings')
            ->orderBy('global_page_component.order');
    }

    /**
     * 🔗 Relacionamento: páginas reais de projeto que utilizam este componente.
     */
    public function projectPages()
    {
        return $this->belongsToMany(
            ProjectPage::class,
            'project_page_component',
            'component_id',
            'project_page_id'
        )
            ->withPivot('order', 'settings', 'is_visible', 'global_component_id')
            ->withTimestamps()
            ->orderBy('project_page_component.order');
    }

    /**
     * 🧩 Relacionamento reverso (para rastrear importações):
     *
     * Se um componente global foi clonado para um projeto,
     * o campo project_page_component.global_component_id guarda essa referência.
     */
    public function importedInstances()
    {
        return $this->hasMany(
            ProjectPageComponent::class,
            'global_component_id'
        );
    }
}
