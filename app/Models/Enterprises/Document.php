<?php

namespace App\Models\Enterprises;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Document extends Model
{
    protected $table = 'enterprise_documents';

    protected $fillable = [
        'enterprise_id',
        'cnpj',
        'ie',
        'im',
        'other_documents',
    ];

    public function enterprise(): BelongsTo
    {
        return $this->belongsTo(Enterprise::class);
    }
}