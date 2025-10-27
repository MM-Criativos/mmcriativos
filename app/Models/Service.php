<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'icon',
        'thumb',
        'cover',
        'description',
        'order',
    ];

    public function projects()
    {
        return $this->hasMany(Project::class);
    }

    public function info()
    {
        return $this->hasOne(ServiceInfo::class);
    }

    public function benefits()
    {
        return $this->hasMany(ServiceBenefit::class)->orderBy('order');
    }

    public function features()
    {
        return $this->hasMany(ServiceFeature::class)->orderBy('order');
    }

    public function processes()
    {
        return $this->hasMany(ServiceProcess::class)->orderBy('order');
    }

    public function ctas()
    {
        return $this->hasMany(ServiceCta::class);
    }
}
