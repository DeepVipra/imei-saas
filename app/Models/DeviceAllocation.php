<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeviceAllocation extends Model
{
    use HasFactory;

    // 🔒 CRITICAL: force correct table
    protected $table = 'device_allocations';

    protected $fillable = [
        'tenant_id',
        'device_id',
        'dealer_id',
        'import_file_id',
        'allocated_at',
        'created_by',
    ];

    public $timestamps = true;

    /* -----------------------------
     | RELATIONSHIPS
     * ----------------------------- */

    public function device()
    {
        return $this->belongsTo(Device::class);
    }

    public function dealer()
    {
        return $this->belongsTo(Dealer::class);
    }

    public function importFile()
    {
        return $this->belongsTo(ImportFile::class);
    }
}
