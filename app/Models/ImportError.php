<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ImportError extends Model
{
    protected $table = 'import_errors';

    /**
     * Only created_at exists, no updated_at
     */
    public $timestamps = false;

    /**
     * Mass assignable fields
     */
    protected $fillable = [
        'import_file_id',
        'row_number',
        'error_message',
    ];

    /**
     * Each error belongs to an import file
     */
    public function importFile()
    {
        return $this->belongsTo(ImportFile::class);
    }
}