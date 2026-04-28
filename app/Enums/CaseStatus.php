<?php

namespace App\Enums;

enum CaseStatus: string
{
    case Open      = 'open';
    case InProgress = 'in_progress';
    case Suspended = 'suspended';
    case Closed    = 'closed';
    case Archived  = 'archived';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match($this) {
            self::Open       => 'Aberto',
            self::InProgress => 'Em andamento',
            self::Suspended  => 'Suspenso',
            self::Closed     => 'Encerrado',
            self::Archived   => 'Arquivado',
            self::Cancelled  => 'Cancelado',
        };
    }
}