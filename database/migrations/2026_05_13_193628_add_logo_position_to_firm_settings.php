<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('firm_settings', function (Blueprint $table) {
            $table->string('firm_logo_position', 10)->default('center')->after('firm_logo');
        });
    }

    public function down(): void
    {
        Schema::table('firm_settings', function (Blueprint $table) {
            $table->dropColumn('firm_logo_position');
        });
    }
};
