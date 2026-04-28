<?php

namespace App\Models;

use App\Enums\CaseStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LegalCase extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'folder_number',
        'case_number',
        'client_id',
        'forum_id',
        'court_name_id',
        'court_number_id',
        'lawyer_id',
        'registered_by',
        'opponent_name',
        'status',
        'note',
    ];

    protected $casts = [
        'status' => CaseStatus::class,
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function forum(): BelongsTo
    {
        return $this->belongsTo(Forum::class);
    }

    public function courtName(): BelongsTo
    {
        return $this->belongsTo(CourtName::class);
    }

    public function courtNumber(): BelongsTo
    {
        return $this->belongsTo(CourtNumber::class);
    }

    public function lawyer(): BelongsTo
    {
        return $this->belongsTo(Lawyer::class);
    }

    public function registeredBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'registered_by');
    }
}