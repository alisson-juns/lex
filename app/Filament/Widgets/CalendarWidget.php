<?php

namespace App\Filament\Widgets;

use App\Enums\HearingStatus;
use App\Enums\TaskStatus;
use App\Filament\Resources\HearingResource;
use App\Filament\Resources\TaskResource;
use App\Models\Hearing;
use App\Models\Task;
use Saade\FilamentFullCalendar\Widgets\FullCalendarWidget;

class CalendarWidget extends FullCalendarWidget
{
    
    public function fetchEvents(array $fetchInfo): array
    {
        $hearings = Hearing::query()
            ->whereBetween('date', [$fetchInfo['start'], $fetchInfo['end']])
            ->whereNotIn('status', [HearingStatus::Cancelled->value])
            ->with('legalCase', 'lawyer')
            ->get()
            ->map(fn (Hearing $hearing) => [
                'id'                  => 'hearing-' . $hearing->id,
                'title'               => '⚖️ ' . $hearing->description,
                'start'               => $hearing->date->toDateString()
                                         . ($hearing->time ? 'T' . $hearing->time : ''),
                'backgroundColor'     => '#3b82f6', // blue-500
                'borderColor'         => '#2563eb', // blue-600
                'textColor'           => '#ffffff',
                'url'                 => HearingResource::getUrl('view', ['record' => $hearing->id]),
                'shouldOpenUrlInNewTab' => false,
                'extendedProps'       => [
                    'type'    => 'hearing',
                    'status'  => $hearing->status->label(),
                    'process' => $hearing->legalCase?->case_number,
                    'lawyer'  => $hearing->lawyer?->name,
                ],
            ]);

        $tasks = Task::query()
            ->whereBetween('due_date', [$fetchInfo['start'], $fetchInfo['end']])
            ->whereNotIn('status', [TaskStatus::Completed->value, TaskStatus::Cancelled->value])
            ->with('lawyers', 'legalCase')
            ->get()
            ->map(fn (Task $task) => [
                'id'                  => 'task-' . $task->id,
                'title'               => '📋 ' . $task->title,
                'start'               => $task->due_date->toDateString()
                                         . ($task->due_time ? 'T' . $task->due_time : ''),
                'backgroundColor'     => '#f59e0b', // amber-400
                'borderColor'         => '#d97706', // amber-500
                'textColor'           => '#ffffff',
                'url'                 => TaskResource::getUrl('view', ['record' => $task->id]),
                'shouldOpenUrlInNewTab' => false,
                'extendedProps'       => [
                    'type'    => 'task',
                    'status'  => $task->status->label(),
                    'process' => $task->legalCase?->case_number,
                    'lawyers' => $task->lawyers->pluck('name')->join(', '),
                ],
            ]);

        return array_merge($hearings->toArray(), $tasks->toArray());
    }

    public function config(): array
    {
        return [
            'firstDay'      => 0, // Domingo
            'locale'        => 'pt-br',
            'timeZone'      => config('app.timezone'),
            'headerToolbar' => [
                'left'   => 'prev,next today',
                'center' => 'title',
                'right'  => 'dayGridMonth,timeGridWeek,listMonth',
            ],
            'buttonText' => [
                'today'  => 'Hoje',
                'month'  => 'Mês',
                'week'   => 'Semana',
                'day'    => 'Dia',
                'list'   => 'Lista',
            ],
            'noEventsText'   => 'Nenhum evento neste período',
            'allDayText'     => 'Dia inteiro',
            'eventTimeFormat' => [
                'hour'   => '2-digit',
                'minute' => '2-digit',
                'hour12' => false,
            ],
        ];
    }

    // Tooltip com detalhes ao passar o mouse
    public function eventDidMount(): string
    {
        return <<<JS
            function({ event, el }) {
                const props = event.extendedProps;
                let lines = [];
                if (props.status)  lines.push('Status: ' + props.status);
                if (props.process) lines.push('Processo: ' + props.process);
                if (props.lawyer)  lines.push('Advogado: ' + props.lawyer);
                if (props.lawyers) lines.push('Advogado(s): ' + props.lawyers);
                if (lines.length) {
                    el.setAttribute('title', lines.join('\\n'));
                }
            }
        JS;
    }
}