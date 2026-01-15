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
       Schema::create('devices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('brand')->nullable();
            $table->string('model');
            $table->char('imei_1', 15)->unique();
            $table->char('imei_2', 15)->unique();
            $table->enum('status', ['in_stock', 'allocated', 'activated'])->default('in_stock');
            $table->timestamps();

            $table->index(['tenant_id', 'status']);
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('devices');
    }
};
