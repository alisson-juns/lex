<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EnterpriseFeeAgreementTemplate extends Model
{
    protected $table = 'enterprise_fee_agreement_templates';

    protected $fillable = ['name', 'body_text', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public function feeAgreements(): HasMany
    {
        return $this->hasMany(EnterpriseFeeAgreement::class);
    }
}
