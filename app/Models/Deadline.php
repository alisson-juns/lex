<?php

namespace App\Models;

use App\Enums\DeadlineStatus;
use App\Enums\DeadlineType;
use App\Traits\LogsActivityInPortuguese;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Deadline extends Model
{
    use HasFactory;
    use SoftDeletes;
    use LogsActivityInPortuguese;

    protected $fillable = [
        'legal_case_id',
        'deadline_type',
        'fatal_date',
        'internal_date',
        'status',
        'note',
        'created_by',
    ];

    protected $casts = [
        'deadline_type' => DeadlineType::class,
        'status'        => DeadlineStatus::class,
        'fatal_date'    => 'date',
        'internal_date' => 'date',
    ];

    public function legalCase(): BelongsTo
    {
        return $this->belongsTo(LegalCase::class);
    }

    public function lawyers(): BelongsToMany
    {
        return $this->belongsToMany(Lawyer::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getActivitylogSubjectLabel(): string
    {
        return $this->deadline_type?->label()
            . ' — ' . ($this->legalCase?->case_number ?? 'sem processo');
    }
}
