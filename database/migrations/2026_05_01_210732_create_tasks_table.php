<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('legal_case_id')
                ->nullable()
                ->constrained('legal_cases')
                ->nullOnDelete();
            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->date('due_date');
            $table->time('due_time')->nullable();
            $table->string('status')->default('scheduled');
            $table->text('note')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('due_date');
            $table->index('status');
        });

        Schema::create('lawyer_task', function (Blueprint $table) {
            $table->foreignId('task_id')->constrained('tasks')->cascadeOnDelete();
            $table->foreignId('lawyer_id')->constrained('lawyers')->cascadeOnDelete();
            $table->primary(['task_id', 'lawyer_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lawyer_task');
        Schema::dropIfExists('tasks');
    }
};