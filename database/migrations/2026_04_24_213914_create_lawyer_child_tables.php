<?php
// 2026_04_24_000002_create_lawyer_child_tables.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lawyer_addresses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lawyer_id')->constrained('lawyers')->cascadeOnDelete();
            $table->string('street')->nullable();
            $table->string('number', 50)->nullable();
            $table->string('complement', 50)->nullable();
            $table->string('zipcode', 20)->nullable();
            $table->string('district', 50)->nullable();
            $table->string('city', 50)->nullable();
            $table->string('state', 50)->nullable();
            $table->timestamps();
        });

        Schema::create('lawyer_contacts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lawyer_id')->constrained('lawyers')->cascadeOnDelete();
            $table->string('email', 50)->nullable();
            $table->string('cellphone', 20)->nullable();
            $table->string('phone', 20)->nullable();
            $table->string('optional_email', 50)->nullable();
            $table->string('message_cell_phone', 20)->nullable();
            $table->string('message_phone', 20)->nullable();
            $table->text('note')->nullable();
            $table->timestamps();
        });

        Schema::create('lawyer_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lawyer_id')->constrained('lawyers')->cascadeOnDelete();
            $table->string('cpf', 14)->unique();
            $table->string('rg')->nullable();
            $table->string('cnh')->nullable();
            $table->string('pis')->nullable();
            $table->string('ctps')->nullable();
            $table->string('rnm')->nullable();
            $table->text('other_documents')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lawyer_documents');
        Schema::dropIfExists('lawyer_contacts');
        Schema::dropIfExists('lawyer_addresses');
    }
};