<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    use HasFactory;

    protected $fillable = [
        'service_id',
        'category',
        'price',
        'description',
    ];

    protected $casts = [
        'price' => 'decimal:2',
    ];

    /*
    |--------------------------------------------------------------------------
    | 🔗 RELACIONAMENTOS
    |--------------------------------------------------------------------------
    */

    // Cada plano pertence a um serviço
    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    // Cada plano tem várias vantagens
    public function advantages()
    {
        return $this->hasMany(PlanAdvantage::class);
    }

    // Orçamentos associados a este plano
    public function budgets()
    {
        return $this->hasMany(Budget::class);
    }

    /*
    |--------------------------------------------------------------------------
    | 🔍 SCOPES AUXILIARES
    |--------------------------------------------------------------------------
    */

    // Filtrar planos por categoria (Presença Digital, Soluções Inteligentes, etc.)
    public function scopeByCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    // Buscar planos já com vantagens e serviço carregados
    public function scopeWithRelations($query)
    {
        return $query->with(['service', 'advantages']);
    }

    /*
    |--------------------------------------------------------------------------
    | 🧮 GETTERS / FORMATTERS
    |--------------------------------------------------------------------------
    */

    // Retornar o preço formatado para exibição (ex: R$ 3.000,00)
    public function getFormattedPriceAttribute(): string
    {
        return 'R$ ' . number_format($this->price, 0, ',', '.');
    }
}
