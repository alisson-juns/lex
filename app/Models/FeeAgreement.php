<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use App\Traits\LogsActivityInPortuguese;

class FeeAgreement extends Model
{
    use LogsActivityInPortuguese;

    protected $table = 'fee_agreements';

    protected $fillable = [
        'client_id',
        'fee_agreement_template_id',
        'user_id',
        'specific_text',
        'fee_percentage',
        'rendered_body',
        'pdf_path',
        'is_draft',
    ];

    protected $casts = [
        'fee_percentage' => 'decimal:2',
        'is_draft' => 'boolean',
    ];

    protected array $activitylogFields = [
        'specific_text',
        'fee_percentage',
        'pdf_path',
    ];

    protected function activitylogEventDescriptions(): array
    {
        return [
            'created'  => 'Contrato de honorários criado',
            'updated'  => 'Contrato de honorários atualizado',
            'deleted'  => 'Contrato de honorários excluído',
            'restored' => 'Contrato de honorários restaurado',
        ];
    }

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
