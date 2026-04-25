<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LawyerAddress extends Model
{
    protected $table = 'lawyer_addresses';

    protected $fillable = [
        'lawyer_id',
        'street',
        'number',
        'complement',
        'zipcode',
        'district',
        'city',
        'state',
    ];

    public function lawyer(): BelongsTo
    {
        return $this->belongsTo(Lawyer::class);
    }
}