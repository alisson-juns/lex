<?php

namespace App\Filament\Widgets;

use App\Enums\DeadlineStatus;
use App\Filament\Resources\DeadlineResource;
use App\Models\Deadline;
use Filament\Widgets\Widget;
use Illuminate\Support\Collection;

class UpcomingDeadlinesWidget extends Widget
{
    protected static string $view = 'filament.widgets.upcoming-deadlines-widget';

    protected int|string|array $columnSpan = 1;

    protected static ?int $sort = 1;

    public function getDeadlines(): Collection
    {
        return Deadline::query()
            ->where('status', DeadlineStatus::Pending->value)
            ->whereDate('fatal_date', '>=', now()->toDateString())
            ->whereDate('fatal_date', '<=', now()->addDays(7)->toDateString())
            ->with('legalCase', 'lawyers')
            ->orderBy('fatal_date', 'asc')
            ->limit(8)
            ->get()
            ->map(function (Deadline $deadline) {
                $days = (int) now()->startOfDay()
                    ->diffInDays($deadline->fatal_date->startOfDay(), false);

                return [
                    'id'            => $deadline->id,
                    'type'          => $deadline->deadline_type->label(),
                    'fatal_date'    => $deadline->fatal_date->format('d/m/Y'),
                    'internal_date' => $deadline->internal_date?->format('d/m/Y'),
                    'days_label'    => match (true) {
                        $days === 0 => 'Vence hoje',
                        $days === 1 => 'Falta 1 dia',
                        default     => "Em {$days} dias",
                    },
                    'process'       => $deadline->legalCase?->case_number,
                    'lawyers'       => $deadline->lawyers->pluck('name')->join(', '),
                    'url'           => DeadlineResource::getUrl('view', ['record' => $deadline->id]),
                ];
            });
    }
}
