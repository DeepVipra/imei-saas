<?php

namespace App\Imports;

use App\Models\{
    Activation,
    Device,
    ImportError
};
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\{
    ToCollection,
    WithHeadingRow,
    WithChunkReading
};

class ActivationImport implements ToCollection, WithHeadingRow, WithChunkReading
{
    protected int $tenantId;
    protected int $importFileId;

    public function __construct(int $tenantId, int $importFileId)
    {
        $this->tenantId = $tenantId;
        $this->importFileId = $importFileId;
    }

    public function collection(Collection $rows)
    {
        foreach ($rows as $index => $row) {
            try {
                $imei = trim($row['imei']);

                $device = Device::where('tenant_id', $this->tenantId)
                    ->where(fn ($q) =>
                        $q->where('imei_1', $imei)
                          ->orWhere('imei_2', $imei)
                    )->first();

                if (!$device) {
                    throw new \Exception('IMEI not found');
                }

                if ($device->status === 'activated') {
                    throw new \Exception('Device already activated');
                }

                Activation::create([
                    'tenant_id'        => $this->tenantId,
                    'device_id'        => $device->id,
                    'activated_imei'   => $imei,
                    'activation_date'  => $row['activation_date'],
                    'province'         => $row['province'],
                    'city'             => $row['city'],
                    'import_file_id'   => $this->importFileId,
                ]);

                $device->update(['status' => 'activated']);
            } catch (\Throwable $e) {
                ImportError::create([
                    'import_file_id' => $this->importFileId,
                    'row_number'     => $index + 2,
                    'error_message'  => $e->getMessage(),
                ]);
            }
        }
    }

    public function chunkSize(): int
    {
        return 1000;
    }
}
