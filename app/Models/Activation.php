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
     * Set this based on DB structure
     *
     * ✔ TRUE  → if activations table HAS created_at & updated_at
     * ✖ FALSE → if activations table DOES NOT have them
     */
    public $timestamps = true;   // ⬅ change to false if columns are missing

    /* -------------------------------------------------
     | RELATIONSHIPS
     * ------------------------------------------------- */

    public function device()
    {
        return $this->belongsTo(Device::class);
    }

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }
}
