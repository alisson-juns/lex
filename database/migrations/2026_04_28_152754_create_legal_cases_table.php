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
        Schema::create('legal_cases', function (Blueprint $table) {
            $table->id();
            $table->string('folder_number')->nullable();
            $table->string('case_number')->nullable();
            $table->foreignId('client_id')->constrained('clients')->cascadeOnDelete();
            $table->foreignId('forum_id')->nullable()->constrained('forums')->nullOnDelete();
            $table->foreignId('court_name_id')->nullable()->constrained('court_names')->nullOnDelete();
            $table->foreignId('court_number_id')->nullable()->constrained('court_numbers')->nullOnDelete();
            $table->foreignId('lawyer_id')->nullable()->constrained('lawyers')->nullOnDelete();
            $table->foreignId('registered_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('opponent_name')->nullable();
            $table->enum('status', [
                'open',
                'in_progress',
                'suspended',
                'closed',
                'archived',
                'cancelled',
            ])->default('open');
            $table->text('note')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('status');
            $table->index('case_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('legal_cases');
    }
};