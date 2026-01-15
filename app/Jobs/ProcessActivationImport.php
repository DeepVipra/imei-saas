<?php

namespace App\Jobs;

use App\Models\{
    ImportFile,
    ImportError,
    Device,
    Activation
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
use Carbon\Carbon;

class ProcessActivationImport implements ShouldQueue
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
         | HARD LOCK (CRITICAL)
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

                /* ---------------------------------
                 | IMEI (Column C) — SAFE PARSING
                 * --------------------------------- */
                $imei = trim(
                    preg_replace(
                        '/\D/',
                        '',
                        (string) $sheet->getCell("C{$row}")->getFormattedValue()
                    )
                );

                if ($imei === '') {
                    continue;
                }

                /* ---------------------------------
                 | ACTIVATION DATE (G → H fallback)
                 * --------------------------------- */
                $rawDate =
                    $sheet->getCell("G{$row}")->getValue()
                    ?: $sheet->getCell("H{$row}")->getValue();

                try {
                    $activationDate = $rawDate
                        ? Carbon::parse($rawDate)
                        : now();
                } catch (\Throwable $e) {
                    $activationDate = now();
                }

                $province = trim((string) $sheet->getCell("L{$row}")->getValue());
                $city     = trim((string) $sheet->getCell("M{$row}")->getValue());

                try {
                    DB::transaction(function () use (
                        $importFile,
                        $imei,
                        $activationDate,
                        $province,
                        $city
                    ) {

                        $device = Device::where('tenant_id', $importFile->tenant_id)
                            ->where(function ($q) use ($imei) {
                                $q->where('imei_1', $imei)
                                  ->orWhere('imei_2', $imei);
                            })
                            ->first();

                        if (!$device) {
                            throw new \Exception("Device not found for IMEI {$imei}");
                        }

                        if ($device->status === 'active') {
                            throw new \Exception('Device already activated');
                        }

                        if (
                            Activation::where('device_id', $device->id)->exists()
                        ) {
                            throw new \Exception('Activation already exists');
                        }

                        Activation::create([
                            'tenant_id'       => $importFile->tenant_id,
                            'device_id'       => $device->id,
                            'activated_imei'  => $imei,
                            'activation_date' => $activationDate,
                            'province'        => $province,
                            'city'            => $city,
                            'import_file_id'  => $importFile->id,
                        ]);

                        $device->update([
                            'status' => 'active',
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
