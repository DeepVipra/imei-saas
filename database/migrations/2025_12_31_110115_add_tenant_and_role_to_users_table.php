<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('tenant_id')
                  ->after('id')
                  ->constrained()
                  ->cascadeOnDelete();

            $table->foreignId('dealer_id')
                  ->nullable()
                  ->after('tenant_id')
                  ->constrained()
                  ->nullOnDelete();

            $table->enum('role', ['admin', 'dealer'])
                  ->after('password')
                  ->default('admin');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['tenant_id']);
            $table->dropForeign(['dealer_id']);
            $table->dropColumn(['tenant_id', 'dealer_id', 'role']);
        });
    }
};
