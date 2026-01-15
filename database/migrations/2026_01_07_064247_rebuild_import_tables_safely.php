<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        /**
         * DROP DEPENDENT TABLE FIRST
         */
        Schema::dropIfExists('import_errors');

        /**
         * DROP SOURCE TABLE
         */
        Schema::dropIfExists('import_files');

        /**
         * RECREATE import_files
         */
        Schema::create('import_files', function (Blueprint $table) {
            $table->id();

            $table->foreignId('tenant_id')
                  ->constrained()
                  ->cascadeOnDelete();

            $table->foreignId('uploaded_by')
                  ->constrained('users')
                  ->cascadeOnDelete();

            $table->enum('type', [
                'inventory',
                'allocation',
                'activation',
            ]);

            $table->string('original_filename');
            $table->string('file_path');

            $table->unsignedInteger('total_rows')->default(0);
            $table->unsignedInteger('processed_rows')->default(0);

            $table->enum('status', [
                'queued',
                'processing',
                'completed',
                'failed',
            ])->default('queued');

            $table->timestamps();
        });

        /**
         * RECREATE import_errors
         */
        Schema::create('import_errors', function (Blueprint $table) {
            $table->id();

            $table->foreignId('import_file_id')
                  ->constrained('import_files')
                  ->cascadeOnDelete();

            $table->unsignedInteger('row_number')->nullable();
            $table->text('error_message');

            $table->foreignId('created_by')
                  ->constrained('users')
                  ->cascadeOnDelete();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('import_errors');
        Schema::dropIfExists('import_files');
    }
};
