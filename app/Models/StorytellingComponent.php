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

    // 🔗 Relacionamento: em quais páginas globais esse componente aparece
    public function globalPages()
    {
        return $this->belongsToMany(GlobalPage::class, 'global_page_component')
            ->withPivot('order', 'settings');
    }

    // 🔗 Relacionamento: em quais páginas de projeto esse componente foi usado
    public function projectPages()
    {
        return $this->belongsToMany(ProjectPage::class, 'project_page_component')
            ->withPivot('order', 'settings', 'is_visible');
    }
}
