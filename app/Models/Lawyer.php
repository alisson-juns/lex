<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Lawyer extends Model
{
    use SoftDeletes;

    protected $fillable = [
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
}