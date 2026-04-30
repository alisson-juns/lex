<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Tabela pivot para múltiplos advogados por processo
        Schema::create('legal_case_lawyer', function (Blueprint $table) {
            $table->foreignId('legal_case_id')
                ->constrained('legal_cases')
                ->cascadeOnDelete();
            $table->foreignId('lawyer_id')
                ->constrained('lawyers')
                ->cascadeOnDelete();
            $table->primary(['legal_case_id', 'lawyer_id']);
        });

        // Migrar dados existentes antes de remover a coluna
        DB::statement('
            INSERT INTO legal_case_lawyer (legal_case_id, lawyer_id)
            SELECT id, lawyer_id
            FROM legal_cases
            WHERE lawyer_id IS NOT NULL
        ');

        // Remover coluna lawyer_id de legal_cases
        Schema::table('legal_cases', function (Blueprint $table) {
            $table->dropForeign(['lawyer_id']);
            $table->dropColumn('lawyer_id');
        });
    }

    public function down(): void
    {
        // Recriar coluna lawyer_id em legal_cases
        Schema::table('legal_cases', function (Blueprint $table) {
            $table->foreignId('lawyer_id')
                ->nullable()
                ->after('court_number_id')
                ->constrained('lawyers')
                ->nullOnDelete();
        });

        // Restaurar o primeiro advogado de cada processo (best effort)
        DB::statement('
            UPDATE legal_cases lc
            INNER JOIN legal_case_lawyer lcl ON lcl.legal_case_id = lc.id
            SET lc.lawyer_id = lcl.lawyer_id
        ');

        Schema::dropIfExists('legal_case_lawyer');
    }
};