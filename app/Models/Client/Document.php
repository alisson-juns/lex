<?php

namespace App\Models\Client;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Document extends Model
{
    protected $table = 'client_documents';

    protected $fillable = [
        'client_id',
        'cpf',
        'rg',
        'cnh',
        'pis',
        'ctps',
        'rnm',
        'other_documents',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }
}
