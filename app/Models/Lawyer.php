<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Employee;

class Lawyer extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'name',
        'oab',
        'oab_state',
        'oab_subsection',
        'oab_date',
        'status'
    ];

    protected $casts = [
        'oab_date' => 'date'
    ];

    // Relacionamentos
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

   
}