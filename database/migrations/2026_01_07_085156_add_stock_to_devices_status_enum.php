<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        /**
         * IMPORTANT:
         * - ENUM must be redefined fully (MySQL limitation)
         * - Order matters: stock should be default
         */
        DB::statement("
            ALTER TABLE devices
            MODIFY status ENUM(
                'stock',
                'allocated',
                'active',
                'sold'
            ) NOT NULL DEFAULT 'stock'
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        /**
         * Rollback removes `stock`
         * Only run rollback if no rows still use 'stock'
         */
        DB::statement("
            ALTER TABLE devices
            MODIFY status ENUM(
                'allocated',
                'active',
                'sold'
            ) NOT NULL
        ");
    }
};
