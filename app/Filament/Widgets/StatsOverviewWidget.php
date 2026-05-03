<?php

namespace App\Filament\Widgets;

use App\Enums\HearingStatus;
use App\Enums\TaskStatus;
use App\Models\Client;
use App\Models\Enterprise;
use App\Models\Hearing;
use App\Models\LegalCase;
use App\Models\Task;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverviewWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $hoje = now()->toDateString();
        $em10dias = now()->addDays(10)->toDateString();

        $totalClientes = Client::count() + Enterprise::count();

        $totalProcessos = LegalCase::count();

        $audienciasProximas = Hearing::query()
            ->whereBetween('date', [$hoje, $em10dias])
            ->whereNotIn('status', [
                HearingStatus::Cancelled->value,
                HearingStatus::Completed->value,
            ])
            ->count();

        $agendamentosProximos = Task::query()
            ->whereBetween('due_date', [$hoje, $em10dias])
            ->whereIn('status', [
                TaskStatus::Scheduled->value,
                TaskStatus::Rescheduled->value,
            ])
            ->count();

        return [
            Stat::make('Clientes', $totalClientes)
                ->description('Pessoas físicas e jurídicas')
                ->icon('heroicon-o-users')
                ->color('info'),

            Stat::make('Processos', $totalProcessos)
                ->description('Total cadastrado')
                ->icon('heroicon-o-folder-open')
                ->color('success'),

            Stat::make('Audiências (10 dias)', $audienciasProximas)
                ->description('Marcadas ou adiadas')
                ->icon('heroicon-o-scale')
                ->color('warning'),

            Stat::make('Agendamentos', $agendamentosProximos)
                ->description('Agendados')
                ->icon('heroicon-o-clock')
                ->color('danger'),
        ];
    }
}