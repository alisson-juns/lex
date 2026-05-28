<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Promethys\Revive\Concerns\Recyclable;
use App\Traits\LogsActivityInPortuguese;

class Client extends Model
{
    use SoftDeletes;
    use Recyclable;
    use LogsActivityInPortuguese;

    protected $fillable = [
        'name',
        'date_of_birth',
        'gender',
        'father',
        'mother',
        'place_of_birth',
        'state',
        'nationality',
        'marital_status',
        'profession',
        'note',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
    ];

    protected array $activitylogFields = [
        'name',
        'date_of_birth',
        'gender',
        'marital_status',
        'profession',
        'note',
    ];

    protected function activitylogEventDescriptions(): array
    {
        return [
            'created'  => 'Cliente criado',
            'updated'  => 'Cliente atualizado',
            'deleted'  => 'Cliente excluído',
            'restored' => 'Cliente restaurado',
        ];
    }

    public function getActivitylogSubjectLabel(): string
    {
        return $this->name;
    }

    public function client_documents(): HasOne
    {
        return $this->hasOne(ClientDocument::class);
    }

    public function client_addresses(): HasOne
    {
        return $this->hasOne(ClientAddress::class);
    }

    public function client_contacts(): HasOne
    {
        return $this->hasOne(ClientContact::class);
    }

    public function client_bank_accounts(): HasMany
    {
        return $this->hasMany(ClientBankAccount::class);
    }

    public function spouse(): HasOne
    {
        return $this->hasOne(ClientSpouse::class);
    }

    public function wards(): HasMany
    {
        return $this->hasMany(ClientWard::class);
    }

    public function legalCases(): HasMany
    {
        return $this->hasMany(LegalCase::class);
    }

    public function powersOfAttorney(): HasMany
    {
        return $this->hasMany(PowerOfAttorney::class);
    }

    public function gratuityDeclarations(): HasMany
    {
        return $this->hasMany(GratuityDeclaration::class);
    }
}
