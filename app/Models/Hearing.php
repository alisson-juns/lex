<?php

namespace App\Models;

use App\Enums\HearingStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Promethys\Revive\Concerns\Recyclable;

class Hearing extends Model
{
    use SoftDeletes;
    use Recyclable;

    protected $fillable = [
        'legal_case_id',
        'lawyer_id',
        'description',
        'date',
        'time',
        'location',
        'status',
        'note',
    ];

    protected $casts = [
        'date'   => 'date',
        'status' => HearingStatus::class,
    ];

    public function legalCase(): BelongsTo
    {
        return $this->belongsTo(LegalCase::class);
    }

    public function lawyer(): BelongsTo
    {
        return $this->belongsTo(Lawyer::class);
    }
}
