<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EnterpriseBankAccount extends Model
{
    protected $fillable = [
        'enterprise_id',
        'bank_number',
        'bank_name',
        'agency',
        'account',
    ];

    public function enterprise(): BelongsTo
    {
        return $this->belongsTo(Enterprise::class);
    }
}