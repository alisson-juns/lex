<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('deadline_lawyer', function (Blueprint $table) {
            $table->id();
            $table->foreignId('deadline_id')
                ->constrained('deadlines')
                ->cascadeOnDelete();
            $table->foreignId('lawyer_id')
                ->constrained('lawyers')
                ->cascadeOnDelete();
            $table->unique(['deadline_id', 'lawyer_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('deadline_lawyer');
    }
};
