<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class PowerOfAttorney extends Model
{
    protected $table = 'powers_of_attorney';

    protected $fillable = [
        'client_id',
        'power_of_attorney_template_id',
        'user_id',
        'specific_text',
        'rendered_body',
        'pdf_path',
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

    public function lawyers(): BelongsToMany
    {
        return $this->belongsToMany(Lawyer::class, 'lawyer_power_of_attorney');
    }
}