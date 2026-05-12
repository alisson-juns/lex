<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('enterprise_powers_of_attorney', function (Blueprint $table) {
            $table->id();
            $table->foreignId('enterprise_id')->constrained()->cascadeOnDelete();

            // Nomes explícitos curtos para evitar o limite do MySQL
            $table->unsignedBigInteger('enterprise_power_of_attorney_template_id');
            $table->foreign('enterprise_power_of_attorney_template_id', 'epo_template_fk')
                ->references('id')
                ->on('enterprise_power_of_attorney_templates')
                ->cascadeOnDelete();

            $table->unsignedBigInteger('enterprise_representative_id');
            $table->foreign('enterprise_representative_id', 'epo_representative_fk')
                ->references('id')
                ->on('enterprise_representatives')
                ->cascadeOnDelete();

            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->text('specific_text');
            $table->longText('rendered_body')->nullable();
            $table->string('pdf_path')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('enterprise_powers_of_attorney');
    }
};
