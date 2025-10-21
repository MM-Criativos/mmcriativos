<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceCta extends Model
{
    use HasFactory;

    protected $fillable = [
        'service_id',
        'image',
        'title',
        'phone',
    ];

    public function service()
    {
        return $this->belongsTo(Service::class);
    }
}

