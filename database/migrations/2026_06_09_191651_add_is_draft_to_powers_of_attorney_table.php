<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('powers_of_attorney', function (Blueprint $table) {
            $table->boolean('is_draft')->default(true)->after('pdf_path');
        });

        \App\Models\PowerOfAttorney::query()->update(['is_draft' => false]);
    }

    public function down(): void
    {
        Schema::table('powers_of_attorney', function (Blueprint $table) {
            $table->dropColumn('is_draft');
        });
    }
};
