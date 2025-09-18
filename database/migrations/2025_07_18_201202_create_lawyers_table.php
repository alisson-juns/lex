<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('lawyers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('oab')->unique();
            $table->string('oab_state')->nullable(); // Estado da OAB
            $table->string('oab_subsection')->nullable(); // Subsecção da OAB
            $table->date('oab_date')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();
            
            $table->index('oab');
            $table->index(['oab', 'oab_state']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('lawyers');
    }
};