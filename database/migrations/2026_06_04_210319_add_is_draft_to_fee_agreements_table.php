<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('fee_agreements', function (Blueprint $table) {
            $table->boolean('is_draft')->default(true)->after('pdf_path');
        });

        // Registros antigos já existentes deixam de ser rascunho
        \App\Models\FeeAgreement::query()->update(['is_draft' => false]);
    }

    public function down(): void
    {
        Schema::table('fee_agreements', function (Blueprint $table) {
            $table->dropColumn('is_draft');
        });
    }
};
