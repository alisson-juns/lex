<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HearingGoogleEvent extends Model
{
    protected $fillable = ['hearing_id', 'user_id', 'google_event_id'];

    public function hearing(): BelongsTo
    {
        return $this->belongsTo(Hearing::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
