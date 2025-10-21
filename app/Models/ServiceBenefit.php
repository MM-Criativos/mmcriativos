<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceBenefit extends Model
{
    use HasFactory;

    protected $fillable = [
        'service_id',
        'title',
        'subtitle',
        'order',
    ];

    public function service()
    {
        return $this->belongsTo(Service::class);
    }
}

