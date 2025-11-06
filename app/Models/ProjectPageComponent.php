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
        'order',
        'settings',
        'is_visible',
    ];

    protected $casts = [
        'settings' => 'array',
        'is_visible' => 'boolean',
    ];

    // 🔗 Pertence a uma página de projeto
    public function page()
    {
        return $this->belongsTo(ProjectPage::class, 'project_page_id');
    }

    // 🔗 Referência ao componente narrativo
    public function component()
    {
        return $this->belongsTo(StorytellingComponent::class, 'component_id');
    }
}
