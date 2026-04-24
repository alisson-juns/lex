<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClientBankAccount extends Model
{
    protected $table = 'client_bank_accounts';

    protected $fillable = [

        'client_id',
        'bank_number',
        'bank_name',
        'agency',
        'account',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }
}
