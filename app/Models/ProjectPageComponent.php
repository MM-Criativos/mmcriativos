<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectPageComponent extends Model
{
    use HasFactory;

    protected $table = 'project_page_component';

    protected $fillable = [
        'project_page_id',
        'component_id',
        'global_component_id',
        'order',
        'settings',
        'is_visible',
    ];

    protected $casts = [
        'settings' => 'array',
        'is_visible' => 'boolean',
    ];

    /**
     * 🔗 Página do projeto à qual este componente pertence.
     */
    public function page()
    {
        return $this->belongsTo(ProjectPage::class, 'project_page_id');
    }

    /**
     * 🔗 Componente narrativo efetivamente usado nesta página.
     */
    public function component()
    {
        return $this->belongsTo(StorytellingComponent::class, 'component_id');
    }

    /**
     * 🧩 Referência opcional ao componente da biblioteca global.
     *
     * Serve para rastrear a origem (modelo base) deste bloco importado.
     */
    public function globalComponent()
    {
        return $this->belongsTo(StorytellingComponent::class, 'global_component_id');
    }
}
