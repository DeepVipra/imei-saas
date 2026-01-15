<?php

namespace App\Jobs;

use App\Models\{
    ImportFile,
    ImportError,
    Device,
    Dealer,
    DeviceAllocation
};

use App\Imports\ChunkReadFilter;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ProcessAllocationImport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected int $importFileId;

    public function __construct(int $importFileId)
    {
        $this->importFileId = $importFileId;
    }

    public function handle(): void
    {
        $importFile = ImportFile::findOrFail($this->importFileId);

        if (!$importFile->file_path) {
            $importFile->update(['status' => 'failed']);
            return;
        }

        $filePath = Storage::disk('local')->path($importFile->file_path);

        if (!file_exists($filePath)) {
            $importFile->update(['status' => 'failed']);
            return;
        }

        /* -------------------------------------------------
         | HARD LOCK IMPORT STATE
         * ------------------------------------------------- */
        $importFile->update([
            'status'         => 'processing',
            'processed_rows' => 0,
        ]);

        $reader = IOFactory::createReaderForFile($filePath);
        $reader->setReadDataOnly(true);

        $info = $reader->listWorksheetInfo($filePath);
        $totalRows = max(((int) ($info[0]['totalRows'] ?? 0)) - 1, 0);

        $importFile->update(['total_rows' => $totalRows]);

        if ($totalRows === 0) {
            $importFile->update(['status' => 'completed']);
            return;
        }

        $chunkSize = 1000;
        $filter = new ChunkReadFilter();
        $reader->setReadFilter($filter);

        $processed = 0;

        /* -------------------------------------------------
         | PROCESS FILE
         * ------------------------------------------------- */
        for ($startRow = 2; $startRow <= ($totalRows + 1); $startRow += $chunkSize) {

            $filter->setRows($startRow, $chunkSize);
            $spreadsheet = $reader->load($filePath);
            $sheet = $spreadsheet->getActiveSheet();

            for ($row = $startRow; $row < ($startRow + $chunkSize); $row++) {

                if ($row > ($totalRows + 1)) {
                    break;
                }

                // Column D = Dealer Name
                $dealerName = trim((string) $sheet->getCell("D{$row}")->getValue());

                // Column H = IMEI (string-safe)
                $imei = trim(
                    preg_replace(
                        '/\D/',
                        '',
                        (string) $sheet->getCell("H{$row}")->getFormattedValue()
                    )
                );

                if ($dealerName === '' || $imei === '') {
                    continue;
                }

                try {
                    DB::transaction(function () use ($importFile, $dealerName, $imei) {

                        $dealer = Dealer::where('tenant_id', $importFile->tenant_id)
                            ->where('name', $dealerName)
                            ->firstOrFail();

                        $device = Device::where('tenant_id', $importFile->tenant_id)
                            ->where(function ($q) use ($imei) {
                                $q->where('imei_1', $imei)
                                  ->orWhere('imei_2', $imei);
                            })
                            ->lockForUpdate()
                            ->firstOrFail();

                        if ($device->status === 'active') {
                            throw new \Exception('Device already activated');
                        }

                        if (
                            DeviceAllocation::where('device_id', $device->id)->exists()
                        ) {
                            throw new \Exception('Device already allocated');
                        }

                        DeviceAllocation::create([
                            'tenant_id'      => $importFile->tenant_id,
                            'device_id'      => $device->id,
                            'dealer_id'      => $dealer->id,
                            'import_file_id' => $importFile->id,
                            'allocated_at'   => now(),
                        ]);

                        /* -------------------------------------------------
                         | HARD DEVICE STATUS UPDATE (CRITICAL FIX)
                         * ------------------------------------------------- */
                        Device::where('id', $device->id)->update([
                            'status' => 'allocated',
                        ]);
                    });

                } catch (\Throwable $e) {
                    ImportError::create([
                        'import_file_id' => $importFile->id,
                        'row_number'     => $row,
                        'error_message'  => $e->getMessage(),
                        'created_by'     => $importFile->uploaded_by,
                    ]);
                }

                $processed++;

                if ($processed % 100 === 0) {
                    ImportFile::where('id', $importFile->id)
                        ->update(['processed_rows' => $processed]);
                }
            }

            $spreadsheet->disconnectWorksheets();
            unset($spreadsheet);
        }

        /* -------------------------------------------------
         | FINALIZE (ONLY HERE)
         * ------------------------------------------------- */
        ImportFile::where('id', $importFile->id)->update([
            'processed_rows' => $processed,
            'status'         => 'completed',
        ]);
    }
}
