<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EnterpriseDocument extends Model
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