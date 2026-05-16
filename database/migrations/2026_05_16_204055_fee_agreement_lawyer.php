<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('fee_agreement_lawyer', function (Blueprint $table) {
            $table->foreignId('fee_agreement_id')->constrained('fee_agreements')->cascadeOnDelete();
            $table->foreignId('lawyer_id')->constrained('lawyers')->cascadeOnDelete();
            $table->primary(['fee_agreement_id', 'lawyer_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fee_agreement_lawyer');
    }
};
