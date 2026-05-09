<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('powers_of_attorney', function (Blueprint $table) {
            $table->longText('rendered_body')->nullable()->after('specific_text');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('powers_of_attorney', function (Blueprint $table) {
            //
        });
    }
};
