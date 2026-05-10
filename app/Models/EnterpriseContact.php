<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EnterpriseContact extends Model
{
    protected $fillable = [
        'enterprise_id',
        'email',
        'cellphone',
        'phone',
        'optional_email',
        'message_cell_phone',
        'message_phone',
        'note',
    ];

    public function enterprise(): BelongsTo
    {
        return $this->belongsTo(Enterprise::class);
    }
}