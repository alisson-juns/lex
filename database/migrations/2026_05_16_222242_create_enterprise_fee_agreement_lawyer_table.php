// database/migrations/2026_05_17_000002_create_enterprise_fee_agreement_lawyer_table.php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('enterprise_fee_agreement_lawyer', function (Blueprint $table) {

            $table->unsignedBigInteger('enterprise_fee_agreement_id');
            $table->foreign('enterprise_fee_agreement_id', 'efa_lawyer_fk')
                ->references('id')
                ->on('enterprise_fee_agreements')
                ->cascadeOnDelete();

            $table->foreignId('lawyer_id')->constrained('lawyers')->cascadeOnDelete();
            $table->primary(['enterprise_fee_agreement_id', 'lawyer_id']);

        });
    }

    public function down(): void
    {
        Schema::dropIfExists('enterprise_fee_agreement_lawyer');
    }
};
