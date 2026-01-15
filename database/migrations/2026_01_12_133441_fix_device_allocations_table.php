<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('device_allocations', function (Blueprint $table) {

            // Remove incorrect column
            if (Schema::hasColumn('device_allocations', 'name')) {
                $table->dropColumn('name');
            }

            // Required columns
            if (!Schema::hasColumn('device_allocations', 'device_id')) {
                $table->unsignedBigInteger('device_id')->after('tenant_id');
            }

            if (!Schema::hasColumn('device_allocations', 'dealer_id')) {
                $table->unsignedBigInteger('dealer_id')->after('device_id');
            }

            if (!Schema::hasColumn('device_allocations', 'import_file_id')) {
                $table->unsignedBigInteger('import_file_id')->nullable()->after('dealer_id');
            }

            if (!Schema::hasColumn('device_allocations', 'allocated_at')) {
                $table->timestamp('allocated_at')->nullable()->after('import_file_id');
            }

            // Indexes
            $table->index(['device_id']);
            $table->index(['dealer_id']);
        });
    }

    public function down(): void
    {
        // intentionally irreversible
    }
};
