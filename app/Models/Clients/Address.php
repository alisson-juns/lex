<?php

namespace App\Models\Clients;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Address extends Model
{
    protected $table = 'client_addresses';
    
    protected $fillable = [
        'client_id',
        'street',
        'number',
        'complement',
        'zipcode',
        'district',
        'city',
        'state',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }
}
