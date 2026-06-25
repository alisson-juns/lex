<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('legal_cases', function (Blueprint $table) {
            $table->string('type')->default('judicial')->after('id')->index();
            $table->foreignId('agency_id')->nullable()->after('forum_id')
                ->constrained('agencies')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('legal_cases', function (Blueprint $table) {
            $table->dropConstrainedForeignId('agency_id');
            $table->dropColumn('type');
        });
    }
};
