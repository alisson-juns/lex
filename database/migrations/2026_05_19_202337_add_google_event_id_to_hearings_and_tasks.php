<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('hearings', function (Blueprint $table) {
            // ID do evento correspondente no Google Calendar do advogado da audiência
            $table->string('google_event_id')->nullable()->after('note');
        });

        Schema::table('tasks', function (Blueprint $table) {
            // ID do evento correspondente no Google Calendar do criador da tarefa
            $table->string('google_event_id')->nullable()->after('note');
        });
    }

    public function down(): void
    {
        Schema::table('hearings', function (Blueprint $table) {
            $table->dropColumn('google_event_id');
        });

        Schema::table('tasks', function (Blueprint $table) {
            $table->dropColumn('google_event_id');
        });
    }
};
