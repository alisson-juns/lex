<?php

namespace App\Enums;

enum CaseType: string
{
    case Judicial = 'judicial';
    case Administrative = 'administrative';

    public function label(): string
    {
        return match ($this) {
            self::Judicial => 'Judicial',
            self::Administrative => 'Administrativo',
        };
    }

    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn ($case) => [$case->value => $case->label()])
            ->toArray();
    }
}
