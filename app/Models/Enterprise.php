<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Promethys\Revive\Concerns\Recyclable;
use App\Traits\LogsActivityInPortuguese;

class Enterprise extends Model
{
    use SoftDeletes;
    use Recyclable;
    use LogsActivityInPortuguese;

    protected $fillable = [
        'corporate_reason',
        'trade_name',
        'note',
    ];

    protected array $activitylogFields = [
        'corporate_reason',
        'trade_name',
        'note',
    ];

    protected function activitylogEventDescriptions(): array
    {
        return [
            'created'  => 'Empresa criada',
            'updated'  => 'Empresa atualizada',
            'deleted'  => 'Empresa excluída',
            'restored' => 'Empresa restaurada',
        ];
    }

    public function enterprise_documents(): HasOne
    {
        return $this->hasOne(EnterpriseDocument::class);
    }

    public function enterprise_addresses(): HasOne
    {
        return $this->hasOne(EnterpriseAddress::class);
    }

    public function enterprise_contacts(): HasOne
    {
        return $this->hasOne(EnterpriseContact::class);
    }

    public function enterprise_bank_accounts(): HasOne
    {
        return $this->hasOne(EnterpriseBankAccount::class);
    }

    public function enterprise_representatives(): HasMany
    {
        return $this->hasMany(EnterpriseRepresentative::class);
    }

    public function legalCases(): HasMany
    {
        return $this->hasMany(LegalCase::class);
    }

    public function powersOfAttorney(): HasMany
    {
        return $this->hasMany(EnterprisePowerOfAttorney::class);
    }

    public function feeAgreements(): HasMany
    {
        return $this->hasMany(EnterpriseFeeAgreement::class);
    }
}
