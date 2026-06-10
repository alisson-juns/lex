<?php

namespace App\Enums;

enum DeadlineStatus: string
{
    case Pending   = 'pending';
    case Completed = 'completed';
    case Missed    = 'missed';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match($this) {
            self::Pending   => 'Pendente',
            self::Completed => 'Cumprido',
            self::Missed    => 'Perdido',
            self::Cancelled => 'Cancelado',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::Pending   => 'warning',
            self::Completed => 'success',
            self::Missed    => 'danger',
            self::Cancelled => 'gray',
        };
    }
}
