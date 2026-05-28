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
        Schema::table('client_spouses', function (Blueprint $table) {

            $table->string('state', 2)->nullable()->after('place_of_birth');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('client_spouses', function (Blueprint $table) {

            $table->dropColumn('state');
        });
    }
};
