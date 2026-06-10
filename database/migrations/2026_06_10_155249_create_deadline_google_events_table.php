<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('deadline_google_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('deadline_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('date_type'); // 'fatal' ou 'internal'
            $table->string('google_event_id');
            $table->timestamps();

            $table->unique(['deadline_id', 'user_id', 'date_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('deadline_google_events');
    }
};
