<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LawyerContact extends Model
{
    protected $table = 'lawyer_contacts';

    protected $fillable = [
        'lawyer_id',
        'email',
        'cellphone',
        'phone',
        'optional_email',
        'message_cell_phone',
        'message_phone',
        'note',
    ];

    public function lawyer(): BelongsTo
    {
        return $this->belongsTo(Lawyer::class);
    }
}