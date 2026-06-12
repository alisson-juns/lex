<?php

namespace App\Filament\Widgets;

use App\Enums\DeadlineStatus;
use App\Enums\TaskStatus;
use App\Filament\Resources\ClientResource;
use App\Filament\Resources\DeadlineResource;
use App\Filament\Resources\HearingResource;
use App\Filament\Resources\LegalCaseResource;
use App\Filament\Resources\TaskResource;
use App\Models\Client;
use App\Models\Deadline;
use App\Models\Enterprise;
use App\Models\Hearing;
use App\Models\LegalCase;
use App\Models\Task;
use Filament\Widgets\Widget;
use Illuminate\Support\Carbon;

class DashboardStatsWidget extends Widget
{
    protected static string $view = 'filament.widgets.dashboard-stats-widget';

    // Ocupa a largura toda do dashboard
    protected int|string|array $columnSpan = 'full';

    // Ordem no dashboard (menor = mais acima). Ajuste para ficar acima dos outros widgets.
    protected static ?int $sort = -3;

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getStats(): array
    {
        $today = Carbon::today();
        $in10Days = Carbon::today()->addDays(10);

        return [
            [
                'label'  => 'Clientes',
                'value'  => Client::count() + Enterprise::count(),
                'sub'    => 'Pessoas físicas e jurídicas',
                'icon'   => 'heroicon-o-users',
                'color'  => '#0891b2', // cyan-600
                'url'    => ClientResource::getUrl('index'),
            ],
            [
                'label'  => 'Processos',
                'value'  => LegalCase::count(),
                'sub'    => 'Total cadastrado',
                'icon'   => 'heroicon-o-folder',
                'color'  => '#16a34a', // green-600
                'url'    => LegalCaseResource::getUrl('index'),
            ],
            [
                'label'  => 'Audiências (10 dias)',
                'value'  => Hearing::whereBetween('date', [$today, $in10Days])->count(),
                'sub'    => 'Marcadas ou adiadas',
                'icon'   => 'heroicon-o-scale',
                'color'  => '#d97706', // amber-600
                'url'    => HearingResource::getUrl('index'),
            ],
            [
                'label'  => 'Agendamentos',
                'value'  => Task::where('status', TaskStatus::Scheduled)->count(),
                'sub'    => 'Agendados',
                'icon'   => 'heroicon-o-clock',
                'color'  => '#7c3aed', // violet-600
                'url'    => TaskResource::getUrl('index'),
            ],
            [
                'label'  => 'Prazos (10 dias)',
                'value'  => Deadline::where('status', DeadlineStatus::Pending)
                    ->whereBetween('fatal_date', [$today, $in10Days])
                    ->count(),
                'sub'    => 'Prazos pendentes',
                'icon'   => 'heroicon-o-flag',
                'color'  => '#dc2626', // red-600
                'url'    => DeadlineResource::getUrl('index'),
            ],
        ];
    }
}
