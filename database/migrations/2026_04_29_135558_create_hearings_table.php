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
        Schema::create('hearings', function (Blueprint $table) {
         $table->id();
         $table->foreignId('legal_case_id')->constrained('legal_cases')->cascadeOnDelete();
         $table->foreignId('lawyer_id')->nullable()->constrained('lawyers')->nullOnDelete();
         $table->string('description');
         $table->date('date');
         $table->time('time');
         $table->string('location');
         $table->enum('status', [
             'scheduled',
             'completed',
             'cancelled',
             'postponed',
             'suspended',
         ])->default('scheduled');
         $table->text('note')->nullable();
         $table->timestamps();
         $table->softDeletes();
        
         $table->index('date');
         $table->index('status');
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hearings');
    }
};
