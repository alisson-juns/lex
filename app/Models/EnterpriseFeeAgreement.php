<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class EnterpriseFeeAgreement extends Model
{
    protected $table = 'enterprise_fee_agreements';

    protected $fillable = [
        'enterprise_id',
        'enterprise_fee_agreement_template_id',
        'enterprise_representative_id',
        'user_id',
        'specific_text',
        'fee_percentage',
        'rendered_body',
        'is_draft',
        'pdf_path',
    ];

    protected $casts = [
        'fee_percentage' => 'decimal:2',
        'is_draft' => 'boolean',
    ];

    public function enterprise(): BelongsTo
    {
        return $this->belongsTo(Enterprise::class);
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(EnterpriseFeeAgreementTemplate::class, 'enterprise_fee_agreement_template_id');
    }

    public function representative(): BelongsTo
    {
        return $this->belongsTo(EnterpriseRepresentative::class, 'enterprise_representative_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function lawyers(): BelongsToMany
    {
        return $this->belongsToMany(Lawyer::class, 'enterprise_fee_agreement_lawyer');
    }
}
