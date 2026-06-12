<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\DashboardStatsWidget;
use App\Filament\Widgets\UpcomingTasksWidget;
use App\Filament\Widgets\UpcomingHearingsWidget;
use App\Filament\Widgets\UpcomingDeadlinesWidget;
use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    public function getWidgets(): array
    {
        return [
            DashboardStatsWidget::class,
            UpcomingDeadlinesWidget::class,
            UpcomingTasksWidget::class,
            UpcomingHearingsWidget::class,

        ];
    }

    public function getColumns(): int|string|array
    {
        return 3;
    }
}
