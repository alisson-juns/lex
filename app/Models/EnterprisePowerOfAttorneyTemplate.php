<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EnterprisePowerOfAttorneyTemplate extends Model
{
    protected $table = 'enterprise_power_of_attorney_templates';

    protected $fillable = ['name', 'body_text', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public function powersOfAttorney(): HasMany
    {
        return $this->hasMany(EnterprisePowerOfAttorney::class);
    }
}
