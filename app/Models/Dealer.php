<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Dealer extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'name',
    ];

    /**
     * Dealer belongs to a Tenant
     */
    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Dealer → Device Allocations
     */
    public function allocations()
    {
        return $this->hasMany(DeviceAllocation::class, 'dealer_id', 'id');
    }

    /**
     * Dealer → Devices (ONLY via allocations)
     *
     * IMPORTANT:
     * - devices table has NO dealer_id
     * - device_allocations is the pivot
     */
    public function devices()
    {
        return $this->hasManyThrough(
            Device::class,           // Final model
            DeviceAllocation::class, // Intermediate model
            'dealer_id',             // FK on device_allocations
            'id',                    // PK on devices
            'id',                    // PK on dealers
            'device_id'              // FK on device_allocations
        );
    }
}
