<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClientContact extends Model
{
    protected $table = 'client_contacts';
    
    protected $fillable = [
        'client_id',
        'email',
        'cellphone',
        'phone',
        'optional_email',
        'message_cell_phone',
        'message_phone',
        'note',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }
}
