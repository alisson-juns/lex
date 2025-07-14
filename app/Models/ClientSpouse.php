<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClientSpouse extends Model
{
    protected $table = 'client_spouses';

    protected $fillable = [
        'client_id',
        'name',
        'cpf',
        'rg',
        'marital_status',
        'father',
        'mother',
        'pis',
        'ctps',
        'profession',
        'date_of_birth',
        'place_of_birth',
        'nationality',
        'phone',
        'mobile',
        'email',
        'note',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }
}