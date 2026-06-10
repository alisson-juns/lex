<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeadlineGoogleEvent extends Model
{
    protected $fillable = ['deadline_id', 'user_id', 'date_type', 'google_event_id'];

    public function deadline(): BelongsTo
    {
        return $this->belongsTo(Deadline::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
