<?php

namespace App\Filament\Widgets;

use App\Enums\TaskStatus;
use App\Filament\Resources\TaskResource;
use App\Models\Task;
use Filament\Widgets\Widget;
use Illuminate\Support\Collection;

class UpcomingTasksWidget extends Widget
{
    protected static string $view = 'filament.widgets.upcoming-tasks-widget';

    protected int|string|array $columnSpan = 1;

    protected static ?int $sort = 2;

    public function getTasks(): Collection
    {
        return Task::query()
            ->whereNotIn('status', [TaskStatus::completed->value, TaskStatus::cancelled->value])
            ->whereDate('due_date', '>=', now()->toDateString())
            ->whereDate('due_date', '<=', now()->addDays(7)->toDateString())
            ->with('legalCase', 'lawyers')
            ->orderBy('due_date', 'asc')
            ->orderBy('due_time', 'asc')
            ->limit(8)
            ->get()
            ->map(function (Task $task) {
                $days = (int) now()->startOfDay()
                    ->diffInDays($task->due_date->startOfDay(), false);

                return [
                    'id'         => $task->id,
                    'title'      => $task->title,
                    'days_label' => match (true) {
                        $days === 0 => 'Hoje',
                        $days === 1 => 'Falta 1 dia',
                        default     => "Em {$days} dias",
                    },
                    'date'       => $task->due_date->format('d/m/Y'),
                    'time'       => $task->due_time ? substr($task->due_time, 0, 5) : null,
                    'description' => $task->description,
                    'process'    => $task->legalCase?->case_number,
                    'lawyers'    => $task->lawyers->pluck('name')->join(', '),
                    'url'        => TaskResource::getUrl('view', ['record' => $task->id]),
                ];
            });
    }
}
