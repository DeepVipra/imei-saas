<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
{
    // 🔥 FORCE DROP ORDER (SAFE)
    Schema::disableForeignKeyConstraints();

    Schema::dropIfExists('import_errors');
    Schema::dropIfExists('import_files');

    Schema::enableForeignKeyConstraints();

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


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('import_files');
    }
};
