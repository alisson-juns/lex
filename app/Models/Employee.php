<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Promethys\Revive\Concerns\Recyclable;

class Employee extends Model
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
        'occupation_id',
        'note',
        'active',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'active'        => 'boolean',
    ];

    public function occupation(): BelongsTo
    {
        return $this->belongsTo(Occupation::class);
    }

    public function employee_addresses(): HasOne
    {
        return $this->hasOne(EmployeeAddress::class);
    }

    public function employee_contacts(): HasOne
    {
        return $this->hasOne(EmployeeContact::class);
    }

    public function employee_documents(): HasOne
    {
        return $this->hasOne(EmployeeDocument::class);
    }
}
