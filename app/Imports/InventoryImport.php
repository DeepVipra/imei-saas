<?php

namespace App\Imports;

use App\Models\ImportFile;
use App\Models\Device;
use App\Models\ImportError;

use Illuminate\Support\Collection;

use Maatwebsite\Excel\Concerns\{
    ToCollection,
    WithHeadingRow,
    WithChunkReading
};

class InventoryImport implements ToCollection, WithHeadingRow, WithChunkReading
{
    protected ImportFile $importFile;

    public function __construct(ImportFile $importFile)
    {
        $this->importFile = $importFile;
    }

    /**
     * Handle each chunk
     */
    public function collection(Collection $rows): void
    {
        $processed = $this->importFile->processed_rows;

        foreach ($rows as $index => $row) {
            try {
                // REQUIRED FIELDS (Phase-3 format)
                $imei1 = trim((string) ($row['imei_1'] ?? ''));
                $imei2 = trim((string) ($row['imei_2'] ?? ''));
                $model = trim((string) ($row['model'] ?? ''));

                if ($imei1 === '' || $model === '') {
                    continue;
                }

                Device::updateOrCreate(
                    [
                        'tenant_id' => $this->importFile->tenant_id,
                        'imei_1'    => $imei1,
                    ],
                    [
                        'imei_2' => $imei2 ?: null,
                        'model'  => $model,
                        'status' => 'stock', // ✅ ENUM SAFE
                    ]
                );

            } catch (\Throwable $e) {
                ImportError::create([
                    'import_file_id' => $this->importFile->id,
                    'row_number'     => $processed + $index + 2,
                    'error_message'  => $e->getMessage(),
                    'created_by'     => $this->importFile->uploaded_by, // 🔒 REQUIRED
                ]);
            }

            $processed++;
        }

        // 🔒 Persist progress ONCE per chunk
        $this->importFile->update([
            'processed_rows' => $processed,
        ]);
    }

    /**
     * Chunk size (safe for memory + UI)
     */
    public function chunkSize(): int
    {
        return 1000;
    }
}
