<?php
// 2026_04_24_000001_restructure_lawyers_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lawyers', function (Blueprint $table) {
            // Remove vínculo com employees
            $table->dropForeign(['employee_id']);
            $table->dropColumn('employee_id');

            // Dados pessoais (espelhando employees)
            $table->date('date_of_birth')->nullable()->after('name');
            $table->string('gender', 20)->nullable()->after('date_of_birth');
            $table->string('father')->nullable()->after('gender');
            $table->string('mother')->nullable()->after('father');
            $table->string('place_of_birth')->nullable()->after('mother');
            $table->string('nationality')->default('Brasileira')->after('place_of_birth');
            $table->string('marital_status')->nullable()->after('nationality');
            $table->text('note')->nullable()->after('marital_status');

            // Soft delete
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::table('lawyers', function (Blueprint $table) {
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->dropColumn([
                'date_of_birth', 'gender', 'father', 'mother',
                'place_of_birth', 'nationality', 'marital_status', 'note',
                'deleted_at',
            ]);
        });
    }
};