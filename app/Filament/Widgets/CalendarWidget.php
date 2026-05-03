<?php

namespace App\Filament\Widgets;

use App\Enums\HearingStatus;
use App\Enums\TaskStatus;
use App\Filament\Resources\HearingResource;
use App\Filament\Resources\TaskResource;
use App\Models\Hearing;
use App\Models\Task;
use Filament\Notifications\Notification;
use Saade\FilamentFullCalendar\Widgets\FullCalendarWidget;
use Illuminate\Database\Eloquent\Model;

class CalendarWidget extends FullCalendarWidget
{
    public Model|string|int|null $record = null;

    public function fetchEvents(array $fetchInfo): array
    {
        $hearings = Hearing::query()
            ->whereBetween('date', [$fetchInfo['start'], $fetchInfo['end']])
            ->whereNotIn('status', [HearingStatus::Cancelled->value])
            ->with('legalCase', 'lawyer')
            ->get()
            ->map(fn (Hearing $hearing) => [
                'id'                    => 'hearing-' . $hearing->id,
                'title'                 => '⚖️ ' . $hearing->description,
                'start'                 => $hearing->date->toDateString()
                                           . ($hearing->time ? 'T' . $hearing->time : ''),
                'backgroundColor'       => '#3b82f6',
                'borderColor'           => '#2563eb',
                'textColor'             => '#ffffff',
                'url'                   => HearingResource::getUrl('view', ['record' => $hearing->id]),
                'shouldOpenUrlInNewTab' => false,
                'editable'              => false, // audiências não são arrastáveis
                'extendedProps'         => [
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
                'id'                    => 'task-' . $task->id,
                'title'                 => '📋 ' . $task->title,
                'start'                 => $task->due_date->toDateString()
                                           . ($task->due_time ? 'T' . $task->due_time : ''),
                'backgroundColor'       => '#f59e0b',
                'borderColor'           => '#d97706',
                'textColor'             => '#ffffff',
                'url'                   => TaskResource::getUrl('view', ['record' => $task->id]),
                'shouldOpenUrlInNewTab' => false,
                'editable'              => true, // tarefas são arrastáveis
                'extendedProps'         => [
                    'type'    => 'task',
                    'status'  => $task->status->label(),
                    'process' => $task->legalCase?->case_number,
                    'lawyers' => $task->lawyers->pluck('name')->join(', '),
                ],
            ]);

        return array_merge($hearings->toArray(), $tasks->toArray());
    }

    // Chamado pelo FullCalendar após drag & drop
            public function onEventDrop(
                array $event,
                array $oldEvent,
                array $relatedEvents,
                array $delta,
                ?array $oldResource,
                ?array $newResource
            ): bool {   
     
        // Ignora se não for uma tarefa
        if (($event['extendedProps']['type'] ?? null) !== 'task') {
            return false;
        }

        // Extrai o ID numérico do formato "task-{id}"
        $taskId = (int) str_replace('task-', '', $event['id']);
        $task   = Task::find($taskId);

        if (! $task) {
            return false;
        }

        // O FullCalendar envia a data no formato ISO 8601
        // Ex: "2026-05-10" ou "2026-05-10T14:00:00"
        $newStart = $event['start'];
        $newDate  = substr($newStart, 0, 10); // pega só YYYY-MM-DD
        $newTime  = strlen($newStart) > 10
            ? substr($newStart, 11, 5)  // pega HH:MM
            : null;

        $task->update([
            'due_date' => $newDate,
            'due_time' => $newTime ?? $task->due_time, // mantém hora original se arrastado em dayGridMonth
            'status'   => TaskStatus::Rescheduled->value,
        ]);

        Notification::make()
            ->title('Tarefa reagendada')
            ->body("\"{$task->title}\" movida para " . \Carbon\Carbon::parse($newDate)->format('d/m/Y') . '.')
            ->success()
            ->send();

        $this->refreshRecords();
        return true;
    }

    public function config(): array
    {
        return [
            'firstDay'        => 0,
            'locale'          => 'pt-br',
            'timeZone'        => config('app.timezone'),
            'editable'        => true,  // habilita drag globalmente; por evento controlamos com 'editable' => false
            'eventStartEditable' => true,
            'headerToolbar'   => [
                'left'   => 'prev,next today',
                'center' => 'title',
                'right'  => 'dayGridMonth,timeGridWeek,listMonth',
            ],
            'buttonText'      => [
                'today' => 'Hoje',
                'month' => 'Mês',
                'week'  => 'Semana',
                'day'   => 'Dia',
                'list'  => 'Lista',
            ],
            'noEventsText'    => 'Nenhum evento neste período',
            'allDayText'      => 'Dia inteiro',
            'eventTimeFormat' => [
                'hour'   => '2-digit',
                'minute' => '2-digit',
                'hour12' => false,
            ],
        ];
    }

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