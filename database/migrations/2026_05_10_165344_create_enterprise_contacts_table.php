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
        Schema::create('enterprise_contacts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('enterprise_id')->constrained('enterprises')->cascadeOnDelete();
            $table->string('email', 50)->nullable();
            $table->string('cellphone', 20)->nullable();
            $table->string('phone', 20)->nullable();
            $table->string('optional_email', 50)->nullable();
            $table->string('message_cell_phone', 20)->nullable();
            $table->string('message_phone', 20)->nullable();
            $table->text('note')->nullable();
            $table->timestamps();
        });
    }
    
    public function down(): void
    {
        Schema::dropIfExists('enterprise_contacts');
    }
};
