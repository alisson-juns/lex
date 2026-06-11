<?php

namespace App\Filament\Widgets;

use App\Enums\HearingStatus;
use App\Filament\Resources\HearingResource;
use App\Models\Hearing;
use Filament\Widgets\Widget;
use Illuminate\Support\Collection;

class UpcomingHearingsWidget extends Widget
{
    protected static string $view = 'filament.widgets.upcoming-hearings-widget';

    protected int|string|array $columnSpan = 1;

    protected static ?int $sort = 3;

    public function getHearings(): Collection
    {
        return Hearing::query()
            ->where('status', '!=', HearingStatus::Cancelled->value)
            ->whereDate('date', '>=', now()->toDateString())
            ->whereDate('date', '<=', now()->addDays(7)->toDateString())
            ->with('legalCase', 'lawyer')
            ->orderBy('date', 'asc')
            ->orderBy('time', 'asc')
            ->limit(8)
            ->get()
            ->map(function (Hearing $hearing) {
                $days = (int) now()->startOfDay()
                    ->diffInDays($hearing->date->startOfDay(), false);

                return [
                    'id'          => $hearing->id,
                    'description' => $hearing->description,
                    'days_label'  => match (true) {
                        $days === 0 => 'Hoje',
                        $days === 1 => 'Falta 1 dia',
                        default     => "Em {$days} dias",
                    },
                    'date'        => $hearing->date->format('d/m/Y'),
                    'time'        => $hearing->time ? substr($hearing->time, 0, 5) : null,
                    'location'    => $hearing->location,
                    'process'     => $hearing->legalCase?->case_number,
                    'lawyer'      => $hearing->lawyer?->name,
                    'url'         => HearingResource::getUrl('view', ['record' => $hearing->id]),
                ];
            });
    }
}
