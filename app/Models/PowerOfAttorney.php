<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PowerOfAttorney extends Model
{
    protected $table = 'powers_of_attorney';

    protected $fillable = [
        'client_id',
        'power_of_attorney_template_id',
        'user_id',
        'specific_text',
        'rendered_body',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(PowerOfAttorneyTemplate::class, 'power_of_attorney_template_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}