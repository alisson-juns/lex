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
        Schema::table('enterprise_representatives', function (Blueprint $table) {
            $table->string('cpf', 14)->nullable()->after('position');
            $table->string('rg', 12)->nullable()->after('cpf');
            $table->string('email', 100)->nullable()->after('rg');
            $table->string('phone', 20)->nullable()->after('email');
        });
    }
    
    public function down(): void
    {
        Schema::table('enterprise_representatives', function (Blueprint $table) {
            $table->dropColumn(['cpf', 'rg', 'email', 'phone']);
        });
    }
};
