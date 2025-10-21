<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClientInfo extends Model
{
    use HasFactory;

    protected $table = 'clients_info';

    protected $fillable = [
        'client_id',
        'cep',
        'street',
        'number',
        'complement',
        'neighborhood',
        'city',
        'state',
        'state_code',
        'country',
        'email_commercial',
        'phone',
        'phone_alt',
        'whatsapp',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }
}

