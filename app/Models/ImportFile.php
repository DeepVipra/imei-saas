<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;

class ImportFile extends Model
{
    use HasFactory;

    // ✅ PHASE-3 TYPES
    public const TYPE_INVENTORY = 'inventory';
    public const TYPE_ALLOCATION = 'allocation';
    public const TYPE_ACTIVATION = 'activation';

    protected $fillable = [
        'tenant_id',
        'uploaded_by',
        'type',
        'original_filename',
        'file_path',
        'status',
        'total_rows',
        'processed_rows',
    ];

    protected static function booted()
    {
        static::creating(function ($model) {
            if (!in_array($model->type, [
                self::TYPE_INVENTORY,
                self::TYPE_ALLOCATION,
                self::TYPE_ACTIVATION,
            ], true)) {
                throw new InvalidArgumentException(
                    "Invalid import type: {$model->type}"
                );
            }
        });
    }

    /* ----------------------------
     | Relationships
     |---------------------------- */

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function errors()
    {
        return $this->hasMany(ImportError::class);
    }
}
