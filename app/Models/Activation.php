<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Activation extends Model
{
    use HasFactory;

    /**
     * Explicit table declaration (defensive)
     */
    protected $table = 'activations';

    /**
     * Mass-assignable columns
     */
    protected $fillable = [
        'tenant_id',
        'device_id',
        'activated_imei',
        'activation_date',
        'province',
        'city',
        'import_file_id',
    ];

    /**
     * Casts
     */
    protected $casts = [
        'activation_date' => 'datetime',
    ];

    /**
     * IMPORTANT:
     * ✔ TRUE  → if activations table HAS created_at & updated_at
     * ✖ FALSE → if activations table DOES NOT have them
     */
    public $timestamps = true;

    /* -------------------------------------------------
     | RELATIONSHIPS
     * ------------------------------------------------- */

    /**
     * Activation → Device
     */
    public function device()
    {
        return $this->belongsTo(Device::class, 'device_id');
    }

    /**
     * Activation → Device Allocation
     *
     * Used for:
     * - Dealer filtering
     * - Dealer-wise activation counts
     *
     * Relationship path:
     * activations.device_id → device_allocations.device_id
     */
    public function deviceAllocation()
    {
        return $this->hasOne(
            DeviceAllocation::class,
            'device_id',   // FK on device_allocations
            'device_id'    // FK on activations
        );
    }

    /**
     * Activation → Tenant
     */
    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }
}
