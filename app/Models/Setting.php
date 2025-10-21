<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasFactory;

    protected $fillable = [
        'address_street', 'address_number', 'address_complement', 'address_neighborhood', 'address_city', 'address_state', 'address_country', 'address_zipcode',
        'phone', 'email_support', 'email_contact', 'email_commercial',
        'facebook', 'instagram', 'tiktok', 'x', 'linkedin', 'youtube', 'behance', 'dribbble', 'github', 'whatsapp',
    ];
}

