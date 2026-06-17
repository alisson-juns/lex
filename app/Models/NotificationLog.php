<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NotificationLog extends Model
{
    protected $fillable = [
        'user_id', 'notifiable_type', 'notifiable_id',
        'date_type', 'window_hours', 'sent_at',
    ];

    protected $casts = ['sent_at' => 'datetime'];
}
