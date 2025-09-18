<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->date('date_of_birth');
            $table->string('gender', 20)->nullable();
            $table->string('father')->nullable();
            $table->string('mother')->nullable();
            $table->string('place_of_birth')->nullable();
            $table->string('nationality')->default('Brasileira');
            $table->string('marital_status')->nullable();
            $table->foreignId('occupation_id')->nullable()->constrained()->onDelete('set null');
            $table->text('note')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();
            $table->softDeletes();
            
            $table->index('name');
            $table->index('active');
        });
    }

    public function down()
    {
        Schema::dropIfExists('employees');
    }
};