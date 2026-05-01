<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Lawyer extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'name',
        'oab',
        'oab_state',
        'oab_subsection',
        'oab_date',
        'date_of_birth',
        'gender',
        'father',
        'mother',
        'place_of_birth',
        'nationality',
        'marital_status',
        'note',
        'active',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'oab_date'      => 'date',
        'active'        => 'boolean',
    ];

    // Usuário do sistema vinculado (nullable — advogados externos não têm login)
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function legalCases(): BelongsToMany
    {
        return $this->belongsToMany(LegalCase::class, 'legal_case_lawyer');
    }

    public function hearings(): HasMany
    {
        return $this->hasMany(Hearing::class);
    }

    public function lawyer_addresses(): HasOne
    {
        return $this->hasOne(LawyerAddress::class);
    }

    public function lawyer_contacts(): HasOne
    {
        return $this->hasOne(LawyerContact::class);
    }

    public function lawyer_documents(): HasOne
    {
        return $this->hasOne(LawyerDocument::class);
    }

    public function tasks(): BelongsToMany
    {
        return $this->belongsToMany(Task::class, 'lawyer_task');
    }
}