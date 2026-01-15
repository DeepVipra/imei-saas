<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tenant extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'subscription_plan',
        'imei_limit',
    ];

    /**
     * A tenant has many users
     */
    public function users()
    {
        return $this->hasMany(User::class);
    }

    /**
     * A tenant has many dealers
     */
    public function dealers()
    {
        return $this->hasMany(Dealer::class);
    }

    /**
     * A tenant has many devices
     */
    public function devices()
    {
        return $this->hasMany(Device::class);
    }
}
