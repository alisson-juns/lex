<?php

namespace App\Traits;

use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

trait LogsActivityInPortuguese
{
    use LogsActivity;

    protected function activitylogEventDescriptions(): array
    {
        return [
            'created'  => 'Registro criado',
            'updated'  => 'Registro atualizado',
            'deleted'  => 'Registro excluído',
            'restored' => 'Registro restaurado',
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly($this->activitylogFields ?? ['*'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(
                fn (string $eventName) => $this->activitylogEventDescriptions()[$eventName] ?? $eventName
            );
    }
}
