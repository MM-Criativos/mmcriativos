<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClientTestimonial extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'contact_id',
        'title',
        'testimonial',
        'rating',
        'photo',
        'position',
        'company',
        'is_featured',
        'is_visible',
    ];

    /*
    |--------------------------------------------------------------------------
    | RELATIONSHIPS
    |--------------------------------------------------------------------------
    */

    /**
     * 🏢 Cliente associado ao depoimento
     */
    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * 👤 Contato (autor do depoimento)
     */
    public function contact()
    {
        return $this->belongsTo(Contact::class);
    }

    /*
    |--------------------------------------------------------------------------
    | ACCESSORS / MUTATORS
    |--------------------------------------------------------------------------
    */

    /**
     * 📸 Retorna a URL da foto (usa a do contato ou uma imagem padrão)
     */
    public function getPhotoUrlAttribute()
    {
        if ($this->photo) {
            return asset($this->photo);
        }

        if ($this->contact && $this->contact->photo) {
            return $this->contact->photo_url;
        }

        return asset('images/defaults/contact-placeholder.png');
    }

    /**
     * 🧑 Retorna o nome do autor do depoimento
     */
    public function getAuthorNameAttribute()
    {
        if ($this->contact) {
            return $this->contact->name;
        }

        return $this->company
            ? "{$this->company}"
            : 'Cliente Anônimo';
    }

    /**
     * 🧾 Retorna o cargo ou posição (prioriza o campo local)
     */
    public function getAuthorPositionAttribute()
    {
        return $this->position
            ?? ($this->contact->role ?? null);
    }

    /*
    |--------------------------------------------------------------------------
    | SCOPES
    |--------------------------------------------------------------------------
    */

    /**
     * 🔍 Mostra apenas depoimentos marcados como visíveis
     */
    public function scopeVisible($query)
    {
        return $query->where('is_visible', true);
    }

    /**
     * 🌟 Mostra apenas depoimentos em destaque
     */
    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true)->where('is_visible', true);
    }

    /*
    |--------------------------------------------------------------------------
    | HELPERS
    |--------------------------------------------------------------------------
    */

    /**
     * ⭐ Retorna a nota formatada em estrelas (ex: ★★★★☆)
     */
    public function getStarsAttribute()
    {
        if (!$this->rating) return null;
        return str_repeat('★', $this->rating) . str_repeat('☆', 5 - $this->rating);
    }

    /**
     * ✨ Retorna o nome + cargo formatado
     */
    public function getDisplayAuthorAttribute()
    {
        $name = $this->author_name;
        $pos = $this->author_position;
        return $pos ? "{$name}, {$pos}" : $name;
    }
}
