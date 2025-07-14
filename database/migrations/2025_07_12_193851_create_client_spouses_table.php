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
        Schema::create('client_spouses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('clients')->cascadeOnDelete();
            $table->string('name');
            $table->string('cpf', 20)->nullable();
            $table->string('rg', 20)->nullable();
            $table->string('marital_status', 20)->nullable();
            $table->string('father')->nullable();
            $table->string('mother')->nullable();
            $table->string('pis', 20)->nullable();
            $table->string('ctps', 20)->nullable();
            $table->string('profession', 35)->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('place_of_birth', 35)->nullable();
            $table->string('nationality', 35)->nullable();
            $table->string('phone', 20)->nullable();
            $table->string('mobile', 20)->nullable();
            $table->string('email', 55)->nullable();
            $table->text('note')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('client_spouses');
    }
};