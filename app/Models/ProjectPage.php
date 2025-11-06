<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectPage extends Model
{
    use HasFactory;

    protected $table = 'project_pages';

    protected $fillable = [
        'project_id',
        'global_page_id',
        'name',
        'slug',
        'is_active',
        'order',
    ];

    /**
     * 🔗 Página pertence a um projeto.
     */
    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * 🔗 Referência opcional à página modelo na biblioteca global.
     *
     * Permite saber de qual template global essa página foi importada.
     */
    public function globalPage()
    {
        return $this->belongsTo(GlobalPage::class, 'global_page_id');
    }

    /**
     * 🔗 Componentes reais dessa página de projeto.
     *
     * Agora a relação usa a tabela correta (plural) e considera o novo campo global_component_id.
     */
    public function components()
    {
        return $this->belongsToMany(
            StorytellingComponent::class,
            'project_page_component',
            'project_page_id',
            'component_id'
        )
            ->withPivot('id', 'order', 'settings', 'is_visible', 'global_component_id')
            ->withTimestamps()
            ->orderBy('project_page_component.order');
    }

    /**
     * 🧩 Atalho: lista de instâncias específicas de ProjectPageComponent
     * (caso queira acessar diretamente os registros pivot como modelos).
     */
    public function pageComponents()
    {
        return $this->hasMany(ProjectPageComponent::class, 'project_page_id');
    }
}
