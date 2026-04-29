<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('legal_cases', function (Blueprint $table) {
            $table->foreignId('client_id')
                ->nullable()
                ->change();

            $table->foreignId('enterprise_id')
                ->nullable()
                ->after('client_id')
                ->constrained('enterprises')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('legal_cases', function (Blueprint $table) {
            $table->dropForeign(['enterprise_id']);
            $table->dropColumn('enterprise_id');

            $table->foreignId('client_id')
                ->nullable(false)
                ->change();
        });
    }
};