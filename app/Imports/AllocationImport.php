<?php

namespace App\Imports;

use App\Models\{
    Dealer,
    Device,
    DeviceAllocation,
    ImportError,
    ImportFile
};

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\{
    ToCollection,
    WithHeadingRow,
    WithChunkReading
};

class AllocationImport implements ToCollection, WithHeadingRow, WithChunkReading
{
    protected ImportFile $importFile;

    public function __construct(ImportFile $importFile)
    {
        $this->importFile = $importFile;
    }

    public function collection(Collection $rows): void
    {
        foreach ($rows as $index => $row) {

            try {
                /* -----------------------------
                 | VALIDATE DEALER
                 * ----------------------------- */
                $dealerName = trim((string) ($row['dealer_name'] ?? ''));

                if ($dealerName === '') {
                    throw new \Exception('Dealer name missing');
                }

                $dealer = Dealer::where('tenant_id', $this->importFile->tenant_id)
                    ->where('name', $dealerName)
                    ->first();

                if (!$dealer) {
                    throw new \Exception("Dealer '{$dealerName}' not found");
                }

                /* -----------------------------
                 | VALIDATE DEVICE
                 * ----------------------------- */
                $imei = trim((string) ($row['imei'] ?? ''));

                if ($imei === '') {
                    throw new \Exception('IMEI missing');
                }

                $device = Device::where('tenant_id', $this->importFile->tenant_id)
                    ->where(function ($q) use ($imei) {
                        $q->where('imei_1', $imei)
                          ->orWhere('imei_2', $imei);
                    })
                    ->first();

                if (!$device) {
                    throw new \Exception("IMEI '{$imei}' not found");
                }

                if ($device->status === 'active') {
                    throw new \Exception('Device already activated');
                }

                if ($device->status === 'allocated') {
                    throw new \Exception('Device already allocated');
                }

                /* -----------------------------
                 | ALLOCATE
                 * ----------------------------- */
                DeviceAllocation::create([
                    'tenant_id'      => $this->importFile->tenant_id,
                    'device_id'      => $device->id,
                    'dealer_id'      => $dealer->id,
                    'import_file_id' => $this->importFile->id,
                    'allocated_at'   => now(),
                ]);

                $device->update([
                    'status' => 'allocated',
                ]);

            } catch (\Throwable $e) {

                ImportError::create([
                    'import_file_id' => $this->importFile->id,
                    'row_number'     => $index + 2, // header offset
                    'error_message'  => $e->getMessage(),
                    'created_by'     => $this->importFile->uploaded_by,
                ]);
            }

            /* -----------------------------
             | PROGRESS UPDATE (CRITICAL)
             * ----------------------------- */
            $this->importFile->increment('processed_rows');
        }
    }

    public function chunkSize(): int
    {
        return 1000;
    }
}
