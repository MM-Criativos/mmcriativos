<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BusinessHour extends Model
{
    use HasFactory;

    protected $fillable = [
        'days',
        'open_time',
        'close_time',
        'is_closed',
    ];
}

