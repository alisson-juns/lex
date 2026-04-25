<?php
// 2026_04_24_000004_add_unique_to_client_spouses.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('client_spouses', function (Blueprint $table) {
            $table->unique('client_id');
        });
    }

    public function down(): void
    {
        Schema::table('client_spouses', function (Blueprint $table) {
            $table->dropUnique(['client_id']);
        });
    }
};