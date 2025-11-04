<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BudgetPayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'budget_id',
        'installments',
        'interest_rate',
        'total_with_interest',
        'installment_value',
        'is_selected',
    ];

    protected $casts = [
        'installments' => 'integer',
        'interest_rate' => 'decimal:2',
        'total_with_interest' => 'decimal:2',
        'installment_value' => 'decimal:2',
        'is_selected' => 'boolean',
    ];

    public function budget()
    {
        return $this->belongsTo(Budget::class);
    }
}

