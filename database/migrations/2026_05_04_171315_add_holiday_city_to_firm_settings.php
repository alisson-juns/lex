<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('firm_settings', function (Blueprint $table) {
            $table->json('holiday_cities')->nullable()->after('firm_lawyers');
            $table->json('holiday_states')->nullable()->after('firm_lawyers');
        });
    }

    public function down(): void
    {
        Schema::table('firm_settings', function (Blueprint $table) {
            $table->dropColumn('holiday_cities');
            $table->dropColumn('holiday_states');
        });
    }
};