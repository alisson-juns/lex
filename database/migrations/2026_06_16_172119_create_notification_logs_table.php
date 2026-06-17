<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {

        Schema::create('notification_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('notifiable_type');   // App\Models\Deadline, Hearing, Task
            $table->unsignedBigInteger('notifiable_id');
            $table->string('date_type')->nullable();  // 'fatal' | 'internal' (prazos)
            $table->unsignedTinyInteger('window_hours'); // 24 ou 48
            $table->timestamp('sent_at');
            $table->timestamps();

            $table->unique(
                ['user_id', 'notifiable_type', 'notifiable_id', 'date_type', 'window_hours'],
                'notif_unique'
            );
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notification_logs');
    }
};
