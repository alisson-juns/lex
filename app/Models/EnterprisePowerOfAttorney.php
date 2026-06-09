<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use App\Traits\LogsActivityInPortuguese;

class EnterprisePowerOfAttorney extends Model
{
    use LogsActivityInPortuguese;

    protected $table = 'enterprise_powers_of_attorney';

    protected $fillable = [
        'enterprise_id',
        'enterprise_power_of_attorney_template_id',
        'enterprise_representative_id',
        'user_id',
        'specific_text',
        'rendered_body',
        'pdf_path',
        'is_draft',
    ];

    protected $casts = [
        'is_draft' => 'boolean',
    ];

    protected array $activitylogFields = [
        'specific_text',
        'pdf_path',
    ];

    protected function activitylogEventDescriptions(): array
    {
        return [
            'created'  => 'Procuração PJ criada',
            'updated'  => 'Procuração PJ atualizada',
            'deleted'  => 'Procuração PJ excluída',
            'restored' => 'Procuração PJ restaurada',
        ];
    }

    public function enterprise(): BelongsTo
    {
        return $this->belongsTo(Enterprise::class);
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(EnterprisePowerOfAttorneyTemplate::class, 'enterprise_power_of_attorney_template_id');
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
        return $this->belongsToMany(Lawyer::class, 'enterprise_power_of_attorney_lawyer');
    }
}
