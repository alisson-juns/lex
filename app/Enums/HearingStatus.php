<?php
namespace App\Enums;

enum HearingStatus: string
{
    case Scheduled = 'scheduled';
    case Completed = 'completed';
    case Cancelled = 'cancelled';
    case Postponed = 'postponed';
    case Suspended = 'suspended';

    public function label(): string
    {
        return match($this) {
            self::Scheduled => 'Marcada',
            self::Completed => 'Realizada',
            self::Cancelled => 'Cancelada',
            self::Postponed => 'Adiada',
            self::Suspended => 'Suspensa',
        };
    }
}