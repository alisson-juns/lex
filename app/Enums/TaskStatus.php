<?php

namespace App\Enums;

enum TaskStatus: string
{
    case Scheduled   = 'scheduled';
    case InProgress  = 'in_progress';
    case Completed   = 'completed';
    case Cancelled   = 'cancelled';
    case Rescheduled = 'rescheduled';

    public function label(): string
    {
        return match($this) {
            self::Scheduled   => 'Agendada',
            self::InProgress  => 'Em andamento',
            self::Completed   => 'Concluída',
            self::Cancelled   => 'Cancelada',
            self::Rescheduled => 'Reagendada',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::Scheduled   => 'warning',
            self::InProgress  => 'info',
            self::Completed   => 'success',
            self::Cancelled   => 'danger',
            self::Rescheduled => 'gray',
        };
    }
}