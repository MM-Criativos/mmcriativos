<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class ExtraService extends Pivot
{
    protected $table = 'extra_service';

    protected $fillable = [
        'extra_id',
        'service_id',
        'custom_price',
        'custom_discount',
        'is_default',
        'sort',
    ];

    protected $casts = [
        'custom_price' => 'decimal:2',
        'custom_discount' => 'decimal:2',
        'is_default' => 'boolean',
        'sort' => 'integer',
    ];
}

