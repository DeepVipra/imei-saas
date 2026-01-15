<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Device extends Model
{
    use HasFactory;

    /**
     * Mass assignable fields
     */
    protected $fillable = [
        'tenant_id',
        'brand',
        'model',
        'imei_1',
        'imei_2',
        'status',
    ];

    /**
     * Casts
     */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Device belongs to a Tenant
     */
    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * A device has ONE active allocation
     */
    public function allocation()
    {
        return $this->hasOne(DeviceAllocation::class);
    }

    /**
     * A device has ONE activation record
     */
    public function activation()
    {
        return $this->hasOne(Activation::class);
    }

    /**
     * Scope: Current tenant only
     * IMPORTANT for SaaS isolation
     */
    public function scopeForTenant(Builder $query, int $tenantId): Builder
    {
        return $query->where('tenant_id', $tenantId);
    }

    /**
     * Scope: Only allocated devices
     */
    public function scopeAllocated(Builder $query): Builder
    {
        return $query->where('status', 'allocated');
    }

    /**
     * Scope: Only activated devices
     */
    public function scopeActivated(Builder $query): Builder
    {
        return $query->where('status', 'activated');
    }

    /**
     * Find device by IMEI (imei_1 OR imei_2)
     * Used heavily during Excel imports
     */
    public static function findByImei(string $imei): ?self
    {
        return self::where('imei_1', $imei)
            ->orWhere('imei_2', $imei)
            ->first();
    }

    /**
     * Mask IMEI for UI display
     * Example: ***********1234
     */
    public function maskImei(string $imei): string
    {
        return str_repeat('*', 11) . substr($imei, -4);
    }

    /**
     * Accessor: masked IMEI 1
     */
    public function getMaskedImei1Attribute(): string
    {
        return $this->maskImei($this->imei_1);
    }

    /**
     * Accessor: masked IMEI 2
     */
    public function getMaskedImei2Attribute(): string
    {
        return $this->maskImei($this->imei_2);
    }
}
