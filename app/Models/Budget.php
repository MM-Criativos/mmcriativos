<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Budget extends Model
{
    use HasFactory;

    protected $fillable = [
        'service_id',
        'plan_id',
        'client_id',
        'base_price_snapshot',
        'client_name',
        'client_email',
        'client_phone',
        'currency',
        'discount_amount',
        'total_discount_amount',
        'tax_percent',
        'subtotal_one_time',
        'subtotal_monthly',
        'subtotal_yearly',
        'total_one_time',
        'total_monthly',
        'total_yearly',
        'status',
        'valid_until',
        'public_token',
        'notes',
    ];

    protected $casts = [
        'base_price_snapshot' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total_discount_amount' => 'decimal:2',
        'tax_percent' => 'decimal:2',
        'subtotal_one_time' => 'decimal:2',
        'subtotal_monthly' => 'decimal:2',
        'subtotal_yearly' => 'decimal:2',
        'total_one_time' => 'decimal:2',
        'total_monthly' => 'decimal:2',
        'total_yearly' => 'decimal:2',
        'valid_until' => 'date',
    ];

    // Relationships
    public function plan()
    {
        return $this->belongsTo(Plan::class);
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function project()
    {
        return $this->hasOne(\App\Models\Project::class);
    }

    public function items()
    {
        return $this->hasMany(BudgetItem::class)->orderBy('sort');
    }

    public function events()
    {
        return $this->hasMany(BudgetEvent::class)->latest();
    }

    public function payments()
    {
        return $this->hasMany(BudgetPayment::class);
    }

    public function selectedPayment()
    {
        return $this->hasOne(BudgetPayment::class)->where('is_selected', true);
    }

    // Helpers
    public function calculateTotals(bool $save = true): self
    {
        $this->loadMissing('items');

        $subtotalOneTime = 0.0;
        $subtotalMonthly = 0.0;
        $subtotalYearly = 0.0;
        $itemDiscounts = 0.0;

        foreach ($this->items as $item) {
            $unit = $item->price_type === 'percent'
                ? (float) $this->base_price_snapshot * ((float) $item->unit_price / 100)
                : (float) $item->unit_price;

            $qty = max((int) $item->qty, 1);
            $lineBeforeDiscount = $unit * $qty;
            $discount = (float) $item->discount_amount;
            $lineTotal = max($lineBeforeDiscount - $discount, 0);

            if ($save) {
                if (round((float) $item->total, 2) !== round($lineTotal, 2)) {
                    $item->total = $lineTotal;
                    $item->save();
                }
            } else {
                $item->total = $lineTotal;
            }

            $itemDiscounts += $discount;

            switch ($item->billing_period) {
                case 'monthly':
                    $subtotalMonthly += $lineTotal;
                    break;
                case 'yearly':
                    $subtotalYearly += $lineTotal;
                    break;
                default:
                    $subtotalOneTime += $lineTotal;
            }
        }

        $this->subtotal_one_time = round($subtotalOneTime, 2);
        $this->subtotal_monthly = round($subtotalMonthly, 2);
        $this->subtotal_yearly = round($subtotalYearly, 2);
        $this->total_discount_amount = round($itemDiscounts, 2);

        $sumSubtotal = $subtotalOneTime + $subtotalMonthly + $subtotalYearly;
        $globalDiscount = min((float) $this->discount_amount, $sumSubtotal);
        $taxPercent = (float) ($this->tax_percent ?? 0);

        $computeNet = function (float $subtotal) use ($sumSubtotal, $globalDiscount, $taxPercent): float {
            if ($subtotal <= 0) {
                return 0.0;
            }
            $discountShare = $sumSubtotal > 0 ? ($globalDiscount * ($subtotal / $sumSubtotal)) : 0.0;
            $net = max($subtotal - $discountShare, 0.0);
            if ($taxPercent > 0) {
                $net = $net * (1 + ($taxPercent / 100));
            }
            return round($net, 2);
        };

        $this->total_one_time = $computeNet($subtotalOneTime);
        $this->total_monthly = $computeNet($subtotalMonthly);
        $this->total_yearly = $computeNet($subtotalYearly);

        if ($save) {
            $this->save();
        }

        return $this;
    }

    public function applyDiscounts(?float $discount = null, ?float $taxPercent = null, bool $save = true): self
    {
        if ($discount !== null) {
            $this->discount_amount = max((float) $discount, 0.0);
        }
        if ($taxPercent !== null) {
            $this->tax_percent = max((float) $taxPercent, 0.0);
        }
        return $this->calculateTotals($save);
    }

    public function statusBadge(): string
    {
        $status = (string) $this->status;
        $map = [
            'draft' => ['secondary', 'Rascunho'],
            'sent' => ['info', 'Enviado'],
            'opened' => ['primary', 'Aberto'],
            'accepted' => ['success', 'Aceito'],
            'declined' => ['danger', 'Recusado'],
            'expired' => ['warning', 'Expirado'],
        ];
        [$class, $label] = $map[$status] ?? ['secondary', ucfirst($status)];
        return '<span class="badge bg-' . $class . '">' . e($label) . '</span>';
    }

    public function getGrandTotalAttribute(): float
    {
        $oneTime = (float) $this->total_one_time;
        $monthly = (float) $this->total_monthly;
        $yearly = (float) $this->total_yearly;
        $base = (float) $this->base_price_snapshot;

        return round($base + $oneTime + ($monthly * 12) + $yearly, 2);
    }
}
