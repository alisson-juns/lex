<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('enterprise_power_of_attorney_lawyer', function (Blueprint $table) {
            $table->foreignId('lawyer_id')->constrained()->cascadeOnDelete();

            $table->unsignedBigInteger('enterprise_power_of_attorney_id');
            $table->foreign('enterprise_power_of_attorney_id', 'epo_lawyer_fk')
                ->references('id')
                ->on('enterprise_powers_of_attorney')
                ->cascadeOnDelete();

            $table->primary(['lawyer_id', 'enterprise_power_of_attorney_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('enterprise_power_of_attorney_lawyer');
    }
};
