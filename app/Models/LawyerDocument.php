<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LawyerDocument extends Model
{
    protected $table = 'lawyer_documents';

    protected $fillable = [
        'lawyer_id',
        'cpf',
        'rg',
        'cnh',
        'pis',
        'ctps',
        'rnm',
        'other_documents',
    ];

    public function lawyer(): BelongsTo
    {
        return $this->belongsTo(Lawyer::class);
    }
}