<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Client extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'logo',
        'website',
        'sector',
        'description',
    ];

    /*
    |--------------------------------------------------------------------------
    | RELATIONSHIPS
    |--------------------------------------------------------------------------
    */

    /**
     * 👥 Todos os contatos relacionados a este cliente
     */
    public function contacts()
    {
        return $this->hasMany(Contact::class);
    }

    /**
     * ⭐ Contato principal (aquele com is_primary = true)
     */
    public function primaryContact()
    {
        return $this->hasOne(Contact::class)->where('is_primary', true);
    }

    /**
     * 💬 Depoimentos vinculados ao cliente
     */
    public function testimonials()
    {
        return $this->hasMany(ClientTestimonial::class);
    }

    /**
     * 💼 Projetos do cliente
     */
    public function projects()
    {
        return $this->hasMany(Project::class);
    }

    /**
     * 🌐 Redes sociais associadas
     */
    public function socialMedias()
    {
        return $this->belongsToMany(SocialMedia::class, 'client_social_media')
            ->withPivot(['user'])
            ->withTimestamps();
    }

    /**
     * 🧩 Relacionamento direto com tabela intermediária (se precisar manipular diretamente)
     */
    public function clientSocialMedia()
    {
        return $this->hasMany(ClientSocialMedia::class);
    }

    /*
    |--------------------------------------------------------------------------
    | ACCESSORS / MUTATORS
    |--------------------------------------------------------------------------
    */

    /**
     * Garante que o slug seja gerado automaticamente a partir do nome.
     */
    protected static function booted()
    {
        static::creating(function ($client) {
            if (empty($client->slug)) {
                $client->slug = Str::slug($client->name);
            }
        });
    }

    /*
    |--------------------------------------------------------------------------
    | HELPERS
    |--------------------------------------------------------------------------
    */

    /**
     * Retorna o logo completo (com fallback se não existir)
     */
    public function getLogoUrlAttribute()
    {
        return $this->logo
            ? asset($this->logo)
            : asset('images/defaults/client-placeholder.png');
    }
}
