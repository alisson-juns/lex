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
        Schema::table('enterprise_fee_agreements', function (Blueprint $table) {
            $table->boolean('is_draft')->default(true)->after('pdf_path');
        });

        // Registros antigos já existentes deixam de ser rascunho
        \App\Models\EnterpriseFeeAgreement::query()->update(['is_draft' => false]);

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('enterprise_fee_agreements', function (Blueprint $table) {
            $table->dropColumn('is_draft');
        });
    }
};
