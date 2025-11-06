<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GlobalPage extends Model
{
    use HasFactory;

    protected $table = 'global_pages';

    protected $fillable = [
        'service_id',
        'name',
        'slug',
        'description',
        'meta',
    ];

    protected $casts = [
        'meta' => 'array',
    ];

    /**
     * 🔗 Cada página global pertence a um serviço (Landing, SaaS, Portal etc.)
     */
    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    /**
     * 🔗 Relacionamento opcional com os componentes da biblioteca.
     *
     * Mesmo que você não use global_page_component no momento,
     * esse relacionamento permite reutilizar a estrutura de página modelo
     * se quiser criar layouts de referência.
     */
    public function components()
    {
        return $this->belongsToMany(
            StorytellingComponent::class,
            'global_page_component',   // tabela pivot (opcional, pode não existir ainda)
            'page_id',
            'component_id'
        )
            ->withPivot('order', 'settings')
            ->orderBy('global_page_component.order');
    }

    /**
     * 🔗 Páginas reais de projetos que foram criadas a partir desta página modelo.
     */
    public function projectPages()
    {
        return $this->hasMany(ProjectPage::class, 'global_page_id');
    }
}
