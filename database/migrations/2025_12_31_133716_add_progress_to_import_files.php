<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('import_files', function (Blueprint $table) {
            $table->unsignedInteger('total_rows')->default(0);
            $table->unsignedInteger('processed_rows')->default(0);
        });
    }

    public function down(): void
    {
        Schema::table('import_files', function (Blueprint $table) {
            $table->dropColumn(['total_rows', 'processed_rows']);
        });
    }
};
