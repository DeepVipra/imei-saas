<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('import_files', function (Blueprint $table) {

            // 🔴 RENAME WRONG COLUMN
            if (Schema::hasColumn('import_files', 'original_name')) {
                $table->renameColumn('original_name', 'original_filename');
            }

            // 🔴 ADD MISSING COLUMNS
            if (!Schema::hasColumn('import_files', 'file_path')) {
                $table->string('file_path')->after('original_filename');
            }

            if (!Schema::hasColumn('import_files', 'uploaded_by')) {
                $table->foreignId('uploaded_by')
                      ->after('tenant_id')
                      ->constrained('users')
                      ->cascadeOnDelete();
            }

            if (!Schema::hasColumn('import_files', 'processed_rows')) {
                $table->unsignedInteger('processed_rows')->default(0)->after('total_rows');
            }

            // 🔴 FIX STATUS ENUM
            $table->enum('status', [
                'queued',
                'processing',
                'completed',
                'failed'
            ])->default('queued')->change();

            // 🔴 REMOVE UNUSED COLUMNS
            if (Schema::hasColumn('import_files', 'success_rows')) {
                $table->dropColumn('success_rows');
            }

            if (Schema::hasColumn('import_files', 'failed_rows')) {
                $table->dropColumn('failed_rows');
            }
        });
    }

    public function down(): void
    {
        // no rollback — structural correction
    }
};
