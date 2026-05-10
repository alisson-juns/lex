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
        Schema::create('lawyer_power_of_attorney', function (Blueprint $table) {
            $table->foreignId('lawyer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('power_of_attorney_id')->constrained('powers_of_attorney')->cascadeOnDelete();
            $table->primary(['lawyer_id', 'power_of_attorney_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lawyer_power_of_attorney');
    }
};
