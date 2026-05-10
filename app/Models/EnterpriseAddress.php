<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EnterpriseAddress extends Model
{
    protected $fillable = [
        'enterprise_id',
        'street',
        'number',
        'complement',
        'zipcode',
        'district',
        'city',
        'state',
    ];

    public function enterprise(): BelongsTo
    {
        return $this->belongsTo(Enterprise::class);
    }
}