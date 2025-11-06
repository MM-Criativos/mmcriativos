<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GlobalPageComponent extends Model
{
    use HasFactory;

    protected $table = 'global_page_component';

    protected $fillable = [
        'page_id',
        'component_id',
        'order',
        'settings',
    ];

    protected $casts = [
        'settings' => 'array',
    ];

    // 🔗 Pertence a uma página global
    public function page()
    {
        return $this->belongsTo(GlobalPage::class, 'page_id');
    }

    // 🔗 Pertence a um componente narrativo
    public function component()
    {
        return $this->belongsTo(StorytellingComponent::class, 'component_id');
    }
}
