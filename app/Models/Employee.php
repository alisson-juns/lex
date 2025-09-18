<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Occupation;
use App\Models\Lawyer;
use App\Models\EmployeeContact;
use App\Models\EmployeeDocument;
use App\Models\EmployeeAddress;

class Employee extends Model
{
    use HasFactory, SoftDeletes;

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
        'active'
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'active' => 'boolean'
    ];

    // Relacionamentos
    public function occupation(): BelongsTo
    {
        return $this->belongsTo(Occupation::class);
    }

    public function lawyer(): HasOne
    {
        return $this->hasOne(Lawyer::class);
    }

    public function contact(): HasOne
    {
        return $this->hasOne(EmployeeContact::class);
    }

    public function documents(): HasOne
    {
        return $this->hasOne(EmployeeDocument::class);
    }

    public function address(): HasOne
    {
        return $this->hasOne(EmployeeAddress::class);
    }

   

}