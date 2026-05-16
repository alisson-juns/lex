<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('firm_settings', function (Blueprint $table) {
            // Texto que identifica o escritório/advogado como CONTRATADA no contrato
            // Ex: "TULA CAROLINA CAMPANA JUNS, brasileira, casada, advogada, OAB/SP 431.326,
            //      com escritório na Rua Benjamin Constant, 61 – sala 1411, São Vicente/SP"
            $table->text('firm_contract_party')->nullable()->after('firm_lawyers');
        });
    }

    public function down(): void
    {
        Schema::table('firm_settings', function (Blueprint $table) {
            $table->dropColumn('firm_contract_party');
        });
    }
};
