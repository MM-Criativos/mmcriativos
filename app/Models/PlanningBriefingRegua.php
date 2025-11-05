<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlanningBriefingRegua extends Model
{
    use HasFactory;

    protected $table = 'planning_briefing_reguas';

    protected $fillable = [
        'category',
        'question',
        'label_left',
        'label_right',
        'emoji_left',
        'emoji_right',
        'min',
        'max',
        'step',
        'default_value',
    ];

    /* 🔗 RELACIONAMENTOS */

    // Uma régua pode ter várias respostas (um para muitos)
    public function responses()
    {
        return $this->hasMany(PlanningBriefingResponse::class, 'briefing_regua_id');
    }
}
