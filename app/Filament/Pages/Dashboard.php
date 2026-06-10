<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\StatsOverviewWidget;
use App\Filament\Widgets\UpcomingTasksWidget;
use App\Filament\Widgets\UpcomingHearingsWidget;
use App\Filament\Widgets\UpcomingDeadlinesWidget;
use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    public function getWidgets(): array
    {
        return [
            StatsOverviewWidget::class,
            UpcomingTasksWidget::class,
            UpcomingHearingsWidget::class,
            UpcomingDeadlinesWidget::class,
        ];
    }
}
