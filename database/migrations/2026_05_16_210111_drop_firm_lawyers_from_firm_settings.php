<?php


use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('firm_settings', function (Blueprint $table) {
            $table->dropColumn('firm_lawyers');
        });
    }

    public function down(): void
    {
        Schema::table('firm_settings', function (Blueprint $table) {
            $table->string('firm_lawyers')->nullable();
        });
    }
};
