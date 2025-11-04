<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Extra extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'price',
        'price_type',
        'billing_period',
        'default_discount',
        'category',
        'is_active',
        'sort',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'default_discount' => 'decimal:2',
        'is_active' => 'boolean',
        'sort' => 'integer',
    ];

    public function services()
    {
        return $this->belongsToMany(Service::class, 'extra_service')
            ->using(ExtraService::class)
            ->withPivot(['custom_price', 'custom_discount'])
            ->withTimestamps();
    }

    // Helper: retorna o valor do extra para um serviço específico
    public function priceFor(Service $service): float
    {
        $serviceModel = $this->services()
            ->where('service_id', $service->id)
            ->first();

        $basePrice = $serviceModel?->pivot?->custom_price ?? (float) $this->price;
        $discount = $serviceModel?->pivot?->custom_discount ?? (float) $this->default_discount;

        $final = max($basePrice - $discount, 0.0);
        return round($final, 2);
    }
}

