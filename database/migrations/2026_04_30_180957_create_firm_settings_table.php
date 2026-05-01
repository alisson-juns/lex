<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('firm_settings', function (Blueprint $table) {
            $table->id();
            $table->string('firm_name')->nullable();
            $table->string('firm_address')->nullable();
            $table->string('firm_city')->nullable();
            $table->string('firm_state', 2)->nullable();
            $table->string('firm_zipcode', 10)->nullable();
            $table->string('firm_phone')->nullable();
            $table->string('firm_email')->nullable();
            $table->string('firm_logo')->nullable(); // path no storage
            $table->text('firm_lawyers')->nullable(); // parágrafo dos advogados
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('firm_settings');
    }
};