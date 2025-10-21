<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Contact extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'name',
        'role',
        'email',
        'phone',
        'linkedin',
        'website',
        'photo',
        'is_primary',
    ];

    /*
    |--------------------------------------------------------------------------
    | RELATIONSHIPS
    |--------------------------------------------------------------------------
    */

    /**
     * 🏢 Cliente ao qual este contato pertence
     */
    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * 💬 Depoimentos associados a este contato
     */
    public function testimonials()
    {
        return $this->hasMany(ClientTestimonial::class);
    }

    /*
    |--------------------------------------------------------------------------
    | ACCESSORS / MUTATORS
    |--------------------------------------------------------------------------
    */

    /**
     * Retorna o nome formatado (ex: “Marcus Multini” → “Marcus M.”)
     */
    public function getShortNameAttribute()
    {
        $parts = explode(' ', $this->name);
        return count($parts) > 1
            ? "{$parts[0]} " . strtoupper(substr(end($parts), 0, 1)) . '.'
            : $this->name;
    }

    /**
     * Retorna o caminho completo da foto (com fallback)
     */
    public function getPhotoUrlAttribute()
    {
        return $this->photo
            ? asset($this->photo)
            : asset('images/defaults/contact-placeholder.png');
    }

    /*
    |--------------------------------------------------------------------------
    | SCOPES
    |--------------------------------------------------------------------------
    */

    /**
     * 🔍 Facilita buscar apenas contatos principais
     */
    public function scopePrimary($query)
    {
        return $query->where('is_primary', true);
    }

    /**
     * 🔍 Facilita buscar contatos de um cliente específico
     */
    public function scopeForClient($query, $clientId)
    {
        return $query->where('client_id', $clientId);
    }

    /*
    |--------------------------------------------------------------------------
    | HELPERS
    |--------------------------------------------------------------------------
    */

    /**
     * Retorna o nome completo + cargo
     */
    public function getDisplayNameAttribute()
    {
        return $this->role
            ? "{$this->name} ({$this->role})"
            : $this->name;
    }
}
