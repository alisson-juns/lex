<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FeeAgreementTemplate extends Model
{
    protected $fillable = ['name', 'body_text', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public function feeAgreements(): HasMany
    {
        return $this->hasMany(FeeAgreement::class);
    }
}
