<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClientSocialMedia extends Model
{
    use HasFactory;

    protected $table = 'client_social_media';

    protected $fillable = [
        'client_id',
        'social_media_id',
        'user',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function socialMedia()
    {
        return $this->belongsTo(SocialMedia::class);
    }
}

