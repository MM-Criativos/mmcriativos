<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SocialMedia extends Model
{
    use HasFactory;

    protected $table = 'social_medias';

    protected $fillable = [
        'name',
        'slug',
        'icon',
    ];

    public function clients()
    {
        return $this->belongsToMany(Client::class, 'client_social_media')
            ->withPivot(['user'])
            ->withTimestamps();
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'social_media_user')
            ->withPivot('url')
            ->withTimestamps();
    }
}
