<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Promethys\Revive\Concerns\Recyclable;
use App\Traits\LogsActivityInPortuguese;

class Lawyer extends Model
{
    use SoftDeletes;
    use Recyclable;
    use LogsActivityInPortuguese;

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

    protected array $activitylogFields = [
        'name',
        'oab',
        'oab_state',
        'oab_subsection',
        'active',
    ];

    protected function activitylogEventDescriptions(): array
    {
        return [
            'created'  => 'Advogado criado',
            'updated'  => 'Advogado atualizado',
            'deleted'  => 'Advogado excluído',
            'restored' => 'Advogado restaurado',
        ];
    }

    public function getActivitylogSubjectLabel(): string
    {
        return "{$this->name} (OAB {$this->oab}/{$this->oab_state})";
    }

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

    public function powersOfAttorney(): BelongsToMany
    {
        return $this->belongsToMany(PowerOfAttorney::class, 'lawyer_power_of_attorney');
    }

    public function enterprisePowersOfAttorney(): BelongsToMany
    {
        return $this->belongsToMany(EnterprisePowerOfAttorney::class, 'enterprise_power_of_attorney_lawyer');
    }

    public function feeAgreements(): BelongsToMany
    {
        return $this->belongsToMany(FeeAgreement::class, 'fee_agreement_lawyer');
    }
}
