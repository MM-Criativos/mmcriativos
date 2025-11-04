<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BudgetItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'budget_id',
        'line_type',
        'ref_id',
        'ref_type',
        'name',
        'description',
        'qty',
        'unit_price',
        'price_type',
        'billing_period',
        'discount_amount',
        'total',
        'sort',
    ];

    protected $casts = [
        'qty' => 'integer',
        'unit_price' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    public function budget()
    {
        return $this->belongsTo(Budget::class);
    }
}

