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

    // 🔗 Relacionamento: uma página global pertence a um serviço (Landing, SaaS, etc.)
    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    // 🔗 Relacionamento: componentes padrão da página (layout default)
    public function components()
    {
        return $this->belongsToMany(StorytellingComponent::class, 'global_page_component')
            ->withPivot('order', 'settings')
            ->orderBy('order');
    }

    // 🔗 Relacionamento: páginas reais baseadas nesse blueprint
    public function projectPages()
    {
        return $this->hasMany(ProjectPage::class, 'global_page_id');
    }
}
