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

    // 🔗 Pertence a um projeto
    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    // 🔗 Referência ao blueprint global
    public function globalPage()
    {
        return $this->belongsTo(GlobalPage::class, 'global_page_id');
    }

    // 🔗 Componentes personalizados dessa página
    public function components()
    {
        return $this->belongsToMany(StorytellingComponent::class, 'project_page_component')
            ->withPivot('order', 'settings', 'is_visible')
            ->orderBy('order');
    }
}
