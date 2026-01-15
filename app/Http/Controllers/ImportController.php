<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

use App\Models\ImportFile;
use App\Jobs\ProcessInventoryImport;
use App\Jobs\ProcessAllocationImport;
use App\Jobs\ProcessActivationImport;

class ImportController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | INVENTORY (MASTER) IMPORT  ✅ NEW CLEAN FLOW
    |--------------------------------------------------------------------------
    */
    public function inventory(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv',
        ]);

        // Always store on LOCAL disk
        $storedPath = $request->file('file')->store('imports', 'local');

        DB::beginTransaction();

        try {
            $importFile = ImportFile::create([
                'tenant_id'         => Auth::user()->tenant_id,
                'uploaded_by'       => Auth::id(),
                'type'              => 'inventory',
                'original_filename' => $request->file('file')->getClientOriginalName(),
                'file_path'         => $storedPath,

                // 🔒 SAFE DEFAULTS (NO NULLS)
                'status'            => 'queued',
                'total_rows'        => 0,
                'processed_rows'    => 0,
            ]);

            DB::commit();

            // Dispatch AFTER commit
            ProcessInventoryImport::dispatch($importFile->id);

            return redirect()->route('imports.progress.view', $importFile);

        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /*
    |--------------------------------------------------------------------------
    | SALES TO DEALER (ALLOCATION) IMPORT  ✅ WORKING
    |--------------------------------------------------------------------------
    */
    public function allocation(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv',
        ]);

        $storedPath = $request->file('file')->store('imports', 'local');

        DB::beginTransaction();

        try {
            $importFile = ImportFile::create([
                'tenant_id'         => Auth::user()->tenant_id,
                'uploaded_by'       => Auth::id(),
                'type'              => 'allocation',
                'original_filename' => $request->file('file')->getClientOriginalName(),
                'file_path'         => $storedPath,
                'status'            => 'queued',
                'total_rows'        => 0,
                'processed_rows'    => 0,
            ]);

            DB::commit();

            ProcessAllocationImport::dispatch($importFile->id);

            return redirect()->route('imports.progress.view', $importFile);

        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /*
    |--------------------------------------------------------------------------
    | ACTIVATION IMPORT  ✅ WORKING
    |--------------------------------------------------------------------------
    */
    public function activation(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv',
        ]);

        $storedPath = $request->file('file')->store('imports', 'local');

        DB::beginTransaction();

        try {
            $importFile = ImportFile::create([
                'tenant_id'         => Auth::user()->tenant_id,
                'uploaded_by'       => Auth::id(),
                'type'              => 'activation',
                'original_filename' => $request->file('file')->getClientOriginalName(),
                'file_path'         => $storedPath,
                'status'            => 'queued',
                'total_rows'        => 0,
                'processed_rows'    => 0,
            ]);

            DB::commit();

            ProcessActivationImport::dispatch($importFile->id);

            return redirect()->route('imports.progress.view', $importFile);

        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /*
    |--------------------------------------------------------------------------
    | IMPORT PROGRESS (VIEW + API)
    |--------------------------------------------------------------------------
    */
    public function progressView(ImportFile $importFile)
    {
        return view('imports.progress', compact('importFile'));
    }

    public function progress(ImportFile $importFile)
    {
        return response()->json([
            'status'    => $importFile->status,
            'total'     => (int) $importFile->total_rows,
            'processed' => (int) $importFile->processed_rows,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | UPLOAD SCREENS
    |--------------------------------------------------------------------------
    */
    public function showInventory()
    {
        return view('imports.inventory');
    }

    public function showAllocation()
    {
        return view('imports.allocation');
    }

    public function showActivation()
    {
        return view('imports.activation');
    }
}
