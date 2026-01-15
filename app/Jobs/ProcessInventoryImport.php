<?php

namespace App\Jobs;

use App\Models\ImportFile;
use App\Imports\InventoryImport;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

use Maatwebsite\Excel\Facades\Excel;
use Throwable;

class ProcessInventoryImport implements ShouldQueue
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

        // -----------------------------
        // SAFETY CHECK
        // -----------------------------
        if (!$importFile->file_path) {
            $importFile->update(['status' => 'failed']);
            return;
        }

        // -----------------------------
        // LOCK STATE (IMMEDIATELY)
        // -----------------------------
        $importFile->update([
            'status'         => 'processing',
            'processed_rows' => 0,
        ]);

        // -----------------------------
        // PROCESS WITH HARD SAFEGUARD
        // -----------------------------
        try {
            Excel::import(
                new InventoryImport($importFile),
                $importFile->file_path,
                'local'
            );

            // ✅ FINALIZE ONLY IF EVERYTHING SUCCEEDS
            $importFile->update([
                'status' => 'completed',
            ]);

        } catch (Throwable $e) {

            // ❌ MARK FAILED IF ANY EXCEPTION OCCURS
            $importFile->update([
                'status' => 'failed',
            ]);

            // Re-throw so queue worker logs & fails correctly
            throw $e;
        }
    }
}
