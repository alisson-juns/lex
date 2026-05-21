<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Promethys\Revive\Concerns\Recyclable;

class Client extends Model
{
    use SoftDeletes;
    use Recyclable;


    protected $fillable = [
        'name',
        'date_of_birth',
        'gender',
        'father',
        'mother',
        'place_of_birth',
        'nationality',
        'marital_status',
        'profession',
        'note',
    ];

    protected $casts = [
    'date_of_birth' => 'date',
    ];

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
