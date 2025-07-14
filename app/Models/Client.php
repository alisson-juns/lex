<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Client extends Model
{
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

    public function documents(): HasOne
    {
        return $this->hasOne(ClientDocument::class);
    }

    public function address(): HasOne
    {
        return $this->hasOne(ClientAddress::class);
    }

    public function contacts(): HasOne
    {
        return $this->hasOne(ClientContact::class);
    }

    public function spouse(): HasOne
    {
        return $this->hasOne(ClientSpouse::class);
    }

    public function wards(): HasMany
    {
        return $this->hasMany(ClientWard::class);
    }
}