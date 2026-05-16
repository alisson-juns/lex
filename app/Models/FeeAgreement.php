<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class FeeAgreement extends Model
{
    protected $table = 'fee_agreements';

    protected $fillable = [
        'client_id',
        'fee_agreement_template_id',
        'user_id',
        'specific_text',
        'fee_percentage',
        'rendered_body',
        'pdf_path',
    ];

    protected $casts = [
        'fee_percentage' => 'decimal:2',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(FeeAgreementTemplate::class, 'fee_agreement_template_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function lawyers(): BelongsToMany
    {
        return $this->belongsToMany(Lawyer::class, 'fee_agreement_lawyer');
    }
}
