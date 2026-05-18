<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('enterprise_fee_agreements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('enterprise_id')->constrained('enterprises')->cascadeOnDelete();


            $table->unsignedBigInteger('enterprise_fee_agreement_template_id');
            $table->foreign('enterprise_fee_agreement_template_id', 'efa_template_fk')
                ->references('id')
                ->on('enterprise_fee_agreement_templates')
                ->cascadeOnDelete();


            $table->unsignedBigInteger('enterprise_representative_id');
            $table->foreign('enterprise_representative_id', 'efa_representative_fk')
                ->references('id')->on('enterprise_representatives')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->text('specific_text');
            $table->decimal('fee_percentage', 5, 2);
            $table->longText('rendered_body')->nullable();
            $table->string('pdf_path')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('enterprise_fee_agreements');
    }
};
