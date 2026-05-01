<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PowerOfAttorneyTemplate extends Model
{
    protected $fillable = ['name', 'body_text', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public function powersOfAttorney(): HasMany
    {
        return $this->hasMany(PowerOfAttorney::class);
    }
}