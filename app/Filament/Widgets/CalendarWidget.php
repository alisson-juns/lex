<?php

namespace App\Filament\Widgets;

use App\Enums\HearingStatus;
use App\Enums\TaskStatus;
use App\Filament\Resources\HearingResource;
use App\Filament\Resources\TaskResource;
use App\Models\FirmSetting;
use App\Models\Hearing;
use App\Models\Lawyer;
use App\Models\LegalCase;
use App\Models\Task;
use App\Services\HolidayService;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Forms\Get;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Model;
use Saade\FilamentFullCalendar\Widgets\FullCalendarWidget;

class CalendarWidget extends FullCalendarWidget
{
    public Model|string|int|null $record = null;

    public ?string $selectedDate = null;

    // ─────────────────────────────────────────────
    // Eventos do calendário
    // ─────────────────────────────────────────────

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
                'editable'              => false,
                // sem 'url' — navegação via botões do popover
                'extendedProps'         => [
                    'type'    => 'hearing',
                    'status'  => $hearing->status->label(),
                    'color'   => '#3b82f6',
                    'process' => $hearing->legalCase?->case_number,
                    'lawyer'  => $hearing->lawyer?->name,
                    'viewUrl' => HearingResource::getUrl('view', ['record' => $hearing->id]),
                    'editUrl' => HearingResource::getUrl('edit', ['record' => $hearing->id]),
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
                'editable'              => true,
                'extendedProps'         => [
                    'type'    => 'task',
                    'status'  => $task->status->label(),
                    'color'   => '#f59e0b',
                    'process' => $task->legalCase?->case_number,
                    'lawyers' => $task->lawyers->pluck('name')->join(', '),
                    'viewUrl' => TaskResource::getUrl('view', ['record' => $task->id]),
                    'editUrl' => TaskResource::getUrl('edit', ['record' => $task->id]),
                ],
            ]);

        // ── Feriados ──────────────────────────────────────
        $settings = FirmSetting::instance();

        $holidays = (new HolidayService())->getEvents(
            $fetchInfo['start'],
            $fetchInfo['end'],
            $settings->holiday_states ?? [],
            $settings->holiday_cities ?? []
        );

        return array_merge($hearings->toArray(), $tasks->toArray(), $holidays);
    }

    // ─────────────────────────────────────────────
    // Drag & drop — reagendar tarefas
    // ─────────────────────────────────────────────

    public function onEventDrop(
        array $event,
        array $oldEvent,
        array $relatedEvents,
        array $delta,
        ?array $oldResource,
        ?array $newResource
    ): bool {
        if (($event['extendedProps']['type'] ?? null) !== 'task') {
            return false;
        }

        $taskId = (int) str_replace('task-', '', $event['id']);
        $task   = Task::find($taskId);

        if (! $task) {
            return false;
        }

        $newStart = $event['start'];
        $newDate  = substr($newStart, 0, 10);
        $newTime  = strlen($newStart) > 10 ? substr($newStart, 11, 5) : null;

        $task->update([
            'due_date' => $newDate,
            'due_time' => $newTime ?? $task->due_time,
            'status'   => TaskStatus::Rescheduled->value,
        ]);

        Notification::make()
            ->title('Tarefa reagendada')
            ->body("\"{$task->title}\" movida para " . Carbon::parse($newDate)->format('d/m/Y') . '.')
            ->success()
            ->send();

        $this->refreshRecords();

        return true;
    }

    // ─────────────────────────────────────────────
    // Clique em dia vazio — abre modal de criação
    // ─────────────────────────────────────────────

    public function onDateSelect(
        string $start,
        ?string $end,
        bool $allDay,
        ?array $view = null,
        ?array $resource = null
    ): void {
        $this->selectedDate = substr($start, 0, 10);
        $this->mountAction('createEvent');
    }

    protected function headerActions(): array
    {
        return [
            Action::make('createEvent')
                ->label('Novo evento')
                ->modalHeading('Novo evento')
                ->modalWidth('lg')
                ->mountUsing(function (Forms\Form $form) {
                    $form->fill([
                        'date'     => $this->selectedDate,
                        'due_date' => $this->selectedDate,
                    ]);
                })
                ->form([
                    Forms\Components\Select::make('type')
                        ->label('Tipo de evento')
                        ->options([
                            'task'    => '📋 Tarefa',
                            'hearing' => '⚖️ Audiência',
                        ])
                        ->required()
                        ->live()
                        ->columnSpanFull(),

                    Forms\Components\TextInput::make('title')
                        ->label('Título')
                        ->required()
                        ->maxLength(255)
                        ->visible(fn (Get $get) => $get('type') === 'task')
                        ->columnSpanFull(),

                    Forms\Components\DatePicker::make('due_date')
                        ->label('Data')
                        ->required()
                        ->displayFormat('d/m/Y')
                        ->visible(fn (Get $get) => $get('type') === 'task'),

                    Forms\Components\TimePicker::make('due_time')
                        ->label('Hora')
                        ->seconds(false)
                        ->visible(fn (Get $get) => $get('type') === 'task'),

                    Forms\Components\Select::make('task_lawyers')
                        ->label('Advogado(s)')
                        ->options(Lawyer::query()->pluck('name', 'id'))
                        ->multiple()
                        ->searchable()
                        ->visible(fn (Get $get) => $get('type') === 'task')
                        ->columnSpanFull(),

                    Forms\Components\Textarea::make('description')
                        ->label('Descrição')
                        ->rows(2)
                        ->visible(fn (Get $get) => $get('type') === 'task')
                        ->columnSpanFull(),

                    Forms\Components\TextInput::make('hearing_description')
                        ->label('Descrição')
                        ->required()
                        ->maxLength(255)
                        ->visible(fn (Get $get) => $get('type') === 'hearing')
                        ->columnSpanFull(),

                    Forms\Components\DatePicker::make('date')
                        ->label('Data')
                        ->required()
                        ->displayFormat('d/m/Y')
                        ->visible(fn (Get $get) => $get('type') === 'hearing'),

                    Forms\Components\TimePicker::make('time')
                        ->label('Hora')
                        ->seconds(false)
                        ->visible(fn (Get $get) => $get('type') === 'hearing'),

                    Forms\Components\Select::make('lawyer_id')
                        ->label('Advogado')
                        ->options(Lawyer::query()->pluck('name', 'id'))
                        ->searchable()
                        ->required(fn (Get $get) => $get('type') === 'hearing')
                        ->visible(fn (Get $get) => $get('type') === 'hearing'),

                    Forms\Components\TextInput::make('location')
                        ->label('Local')
                        ->maxLength(255)
                        ->visible(fn (Get $get) => $get('type') === 'hearing'),

                    Forms\Components\Select::make('legal_case_id')
                        ->label('Processo')
                        ->options(LegalCase::query()->pluck('case_number', 'id'))
                        ->searchable()
                        ->nullable()
                        ->required(fn (Get $get) => $get('type') === 'hearing')
                        ->visible(fn (Get $get) => in_array($get('type'), ['task', 'hearing']))
                        ->columnSpanFull(),
                ])
                ->action(function (array $data) {
                    if ($data['type'] === 'task') {
                        $task = Task::create([
                            'title'         => $data['title'],
                            'due_date'      => $data['due_date'],
                            'due_time'      => $data['due_time'] ?? null,
                            'description'   => $data['description'] ?? null,
                            'legal_case_id' => $data['legal_case_id'] ?? null,
                            'status'        => TaskStatus::Scheduled->value,
                            'created_by'    => auth()->id(),
                        ]);

                        if (! empty($data['task_lawyers'])) {
                            $task->lawyers()->sync($data['task_lawyers']);
                        }

                        Notification::make()
                            ->title('Tarefa criada')
                            ->body("\"{$task->title}\" agendada para " . Carbon::parse($task->due_date)->format('d/m/Y') . '.')
                            ->success()
                            ->actions([
                                \Filament\Notifications\Actions\Action::make('edit')
                                    ->label('Editar')
                                    ->url(TaskResource::getUrl('edit', ['record' => $task->id])),
                            ])
                            ->send();
                    } else {
                        $hearing = Hearing::create([
                            'description'   => $data['hearing_description'],
                            'date'          => $data['date'],
                            'time'          => $data['time'] ?? null,
                            'lawyer_id'     => $data['lawyer_id'] ?? null,
                            'location'      => $data['location'] ?? null,
                            'legal_case_id' => $data['legal_case_id'] ?? null,
                            'status'        => HearingStatus::Scheduled->value,
                        ]);

                        Notification::make()
                            ->title('Audiência criada')
                            ->body("\"{$hearing->description}\" agendada para " . Carbon::parse($hearing->date)->format('d/m/Y') . '.')
                            ->success()
                            ->actions([
                                \Filament\Notifications\Actions\Action::make('edit')
                                    ->label('Editar')
                                    ->url(HearingResource::getUrl('edit', ['record' => $hearing->id])),
                            ])
                            ->send();
                    }

                    $this->refreshRecords();
                }),
        ];
    }

    // ─────────────────────────────────────────────
    // Configuração do calendário
    // ─────────────────────────────────────────────

    public function config(): array
    {
        return [
            'firstDay'           => 0,
            'locale'             => 'pt-br',
            'timeZone'           => config('app.timezone'),
            'editable'           => true,
            'selectable'         => true,
            'eventStartEditable' => true,
            'headerToolbar'      => [
                'left'   => 'prev,next today',
                'center' => 'title',
                'right'  => 'dayGridMonth,timeGridWeek,listMonth',
            ],
            'buttonText'         => [
                'today' => 'Hoje',
                'month' => 'Mês',
                'week'  => 'Semana',
                'day'   => 'Dia',
                'list'  => 'Lista',
            ],
            'noEventsText'       => 'Nenhum evento neste período',
            'allDayText'         => 'Dia inteiro',
            'eventTimeFormat'    => [
                'hour'   => '2-digit',
                'minute' => '2-digit',
                'hour12' => false,
            ],
        ];
    }

    // ─────────────────────────────────────────────
    // Popover ao clicar no evento
    // ─────────────────────────────────────────────

    public function eventDidMount(): string
    {
        return <<<'JS'
        function({ event, el }) {

            // ── Feriados: colore o número do dia e não abre popover
            if (event.extendedProps?.type === 'holiday') {
                const cell = el.closest('.fc-daygrid-day');
                if (cell) cell.classList.add('has-holiday');
                return;
            }

            // ── Injeta CSS do popover uma única vez ──────────────
            if (!document.getElementById('fc-popover-style')) {
                const style = document.createElement('style');
                style.id = 'fc-popover-style';
                style.textContent = `
                    #fc-event-popover {
                        position: fixed;
                        z-index: 99999;
                        background: #fff;
                        border: 1px solid #e5e7eb;
                        border-radius: 0.75rem;
                        box-shadow: 0 10px 30px rgba(0,0,0,0.15);
                        min-width: 270px;
                        max-width: 330px;
                        font-family: inherit;
                        font-size: 0.875rem;
                        overflow: hidden;
                    }
                    .dark #fc-event-popover {
                        background: #1f2937;
                        border-color: #374151;
                        color: #f9fafb;
                    }
                    #fc-event-popover .fcp-header {
                        display: flex;
                        align-items: flex-start;
                        justify-content: space-between;
                        gap: 0.5rem;
                        padding: 0.875rem 1rem 0.625rem;
                        border-bottom: 1px solid #f3f4f6;
                    }
                    .dark #fc-event-popover .fcp-header {
                        border-color: #374151;
                    }
                    #fc-event-popover .fcp-title {
                        font-weight: 600;
                        font-size: 0.9rem;
                        line-height: 1.35;
                        color: #111827;
                        flex: 1;
                    }
                    .dark #fc-event-popover .fcp-title { color: #f9fafb; }
                    #fc-event-popover .fcp-close {
                        cursor: pointer;
                        color: #9ca3af;
                        font-size: 1.1rem;
                        line-height: 1;
                        padding: 0 0.125rem;
                        flex-shrink: 0;
                        margin-top: -1px;
                        background: none;
                        border: none;
                    }
                    #fc-event-popover .fcp-close:hover { color: #374151; }
                    #fc-event-popover .fcp-body {
                        padding: 0.625rem 1rem;
                        display: flex;
                        flex-direction: column;
                        gap: 0.35rem;
                    }
                    #fc-event-popover .fcp-row {
                        display: flex;
                        align-items: flex-start;
                        gap: 0.5rem;
                        color: #4b5563;
                    }
                    .dark #fc-event-popover .fcp-row { color: #d1d5db; }
                    #fc-event-popover .fcp-row span:first-child {
                        font-size: 0.8rem;
                        min-width: 72px;
                        font-weight: 500;
                        color: #9ca3af;
                        padding-top: 1px;
                    }
                    #fc-event-popover .fcp-badge {
                        display: inline-block;
                        padding: 0.125rem 0.5rem;
                        border-radius: 999px;
                        font-size: 0.75rem;
                        font-weight: 500;
                        color: #fff;
                    }
                    #fc-event-popover .fcp-footer {
                        display: flex;
                        gap: 0.5rem;
                        padding: 0.625rem 1rem 0.875rem;
                    }
                    #fc-event-popover .fcp-btn {
                        flex: 1;
                        display: inline-block;
                        text-align: center;
                        padding: 0.4rem 0;
                        border-radius: 0.5rem;
                        font-size: 0.8rem;
                        font-weight: 500;
                        text-decoration: none;
                        cursor: pointer;
                        border: none;
                        transition: opacity .15s;
                    }
                    #fc-event-popover .fcp-btn:hover { opacity: 0.85; }
                    #fc-event-popover .fcp-btn-view {
                        background: #f3f4f6;
                        color: #374151;
                    }
                    .dark #fc-event-popover .fcp-btn-view {
                        background: #374151;
                        color: #e5e7eb;
                    }
                    #fc-event-popover .fcp-btn-edit {
                        color: #fff;
                    }
                `;
                document.head.appendChild(style);
            }

            // ── Intercepta o clique no evento ────────────────────
            el.style.cursor = 'pointer';
            el.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();

                // Remove popover existente
                document.getElementById('fc-event-popover')?.remove();

                const props  = event.extendedProps;
                const start  = event.start;
                const color  = props.color || '#6b7280';

                // Formata data/hora
                const dateStr = start
                    ? start.toLocaleDateString('pt-BR', { weekday: 'short', day: '2-digit', month: '2-digit', year: 'numeric' })
                    : '';
                const timeStr = start && !event.allDay
                    ? start.toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit' })
                    : '';

                // Monta linhas de detalhe
                let rows = '';
                rows += `<div class="fcp-row">
                            <span>📅 Data</span>
                            <span>${dateStr}${timeStr ? ' · ' + timeStr : ''}</span>
                         </div>`;
                if (props.status) {
                    // Calcula luminância para escolher texto preto ou branco
                    const hex = color.replace('#','');
                    const r = parseInt(hex.slice(0,2),16)/255;
                    const g = parseInt(hex.slice(2,4),16)/255;
                    const b = parseInt(hex.slice(4,6),16)/255;
                    const lum = 0.2126*r + 0.7152*g + 0.0722*b;
                    const badgeText = lum > 0.45 ? '#1f2937' : '#ffffff';
                    rows += `<div class="fcp-row">
                                <span>● Status</span>
                                <span><span class="fcp-badge" style="background:${color};color:${badgeText}">${props.status}</span></span>
                             </div>`;
                }
                if (props.process) {
                    rows += `<div class="fcp-row">
                                <span>📁 Processo</span>
                                <span>${props.process}</span>
                             </div>`;
                }
                if (props.lawyer) {
                    rows += `<div class="fcp-row">
                                <span>👤 Advogado</span>
                                <span>${props.lawyer}</span>
                             </div>`;
                }
                if (props.lawyers) {
                    rows += `<div class="fcp-row">
                                <span>👤 Advogado(s)</span>
                                <span>${props.lawyers}</span>
                             </div>`;
                }

                const popover = document.createElement('div');
                popover.id = 'fc-event-popover';
                popover.innerHTML = `
                    <div class="fcp-header">
                        <div class="fcp-title">${event.title}</div>
                        <button class="fcp-close" id="fcp-close-btn">✕</button>
                    </div>
                    <div class="fcp-body">${rows}</div>
                    <div class="fcp-footer">
                        <a href="${props.viewUrl}" class="fcp-btn fcp-btn-view">Ver detalhes</a>
                        <a href="${props.editUrl}" class="fcp-btn fcp-btn-edit" style="background:${color}">Editar</a>
                    </div>
                `;
                document.body.appendChild(popover);

                // Posiciona próximo ao elemento clicado
                const rect  = el.getBoundingClientRect();
                const pw    = popover.offsetWidth  || 300;
                const ph    = popover.offsetHeight || 200;
                const vw    = window.innerWidth;
                const vh    = window.innerHeight;

                let left = rect.right + 8;
                let top  = rect.top;

                if (left + pw > vw - 16) left = rect.left - pw - 8;
                if (left < 8) left = 8;
                if (top + ph > vh - 16) top = vh - ph - 16;
                if (top < 8) top = 8;

                popover.style.left = left + 'px';
                popover.style.top  = top  + 'px';

                // Fecha ao clicar no X
                document.getElementById('fcp-close-btn').addEventListener('click', function(ev) {
                    ev.stopPropagation();
                    popover.remove();
                });
            });

            // Fecha ao clicar fora
            if (!window._fcPopoverOutside) {
                window._fcPopoverOutside = true;
                document.addEventListener('click', function(e) {
                    const pop = document.getElementById('fc-event-popover');
                    if (pop && !pop.contains(e.target)) {
                        pop.remove();
                    }
                });
            }
        }
        JS;
    }
}