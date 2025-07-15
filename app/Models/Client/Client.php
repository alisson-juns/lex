<?php

namespace App\Models\Client;

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
        return $this->hasOne(Document::class);
    }

    public function address(): HasOne
    {
        return $this->hasOne(Address::class);
    }

    public function contacts(): HasOne
    {
        return $this->hasOne(Contact::class);
    }

    public function spouse(): HasOne
    {
        return $this->hasOne(Spouse::class);
    }

    public function wards(): HasMany
    {
        return $this->hasMany(Ward::class);
    }
}