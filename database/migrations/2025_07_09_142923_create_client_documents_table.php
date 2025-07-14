<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('client_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('clients')->cascadeOnDelete();
            $table->char('cpf', 11)->unique()->nullable();
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
        Schema::dropIfExists('client_documents');
    }
};
