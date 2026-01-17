<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('import_files', function (Blueprint $table) {

            if (!Schema::hasColumn('import_files', 'total_rows')) {
                $table->unsignedInteger('total_rows')->default(0)->after('id');
            }

            if (!Schema::hasColumn('import_files', 'processed_rows')) {
                $table->unsignedInteger('processed_rows')->default(0)->after('total_rows');
            }

        });
    }

    public function down(): void
    {
        Schema::table('import_files', function (Blueprint $table) {

            if (Schema::hasColumn('import_files', 'processed_rows')) {
                $table->dropColumn('processed_rows');
            }

            if (Schema::hasColumn('import_files', 'total_rows')) {
                $table->dropColumn('total_rows');
            }

        });
    }
};
