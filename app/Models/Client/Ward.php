<?php

namespace App\Models\Client;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Ward extends Model
{
    protected $table = 'client_wards';

    protected $fillable = [
        'client_id',
        'name',
        'cpf',
        'rg',
        'date_of_birth',
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
