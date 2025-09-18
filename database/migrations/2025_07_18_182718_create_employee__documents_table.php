<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('employee_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->onDelete('cascade');
            $table->string('cpf', 14)->unique();
            $table->string('rg')->nullable();
            $table->string('cnh')->nullable();
            $table->string('pis')->nullable();
            $table->string('ctps')->nullable();
            $table->string('rnm')->nullable();
            $table->text('other_documents')->nullable();
            $table->timestamps();
            
            $table->index('cpf');
        });
    }

    public function down()
    {
        Schema::dropIfExists('employee_documents');
    }
};