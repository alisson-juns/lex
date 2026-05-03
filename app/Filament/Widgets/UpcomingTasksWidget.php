<?php

namespace App\Filament\Widgets;

use App\Enums\TaskStatus;
use App\Models\Task;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class UpcomingTasksWidget extends BaseWidget
{
    protected static ?int $sort = 2;
    protected int | string | array $columnSpan = 'full';
    protected static ?string $heading = 'Agendamentos nos próximos 10 dias';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Task::query()
                    ->whereBetween('due_date', [now(), now()->addDays(10)])
                    ->whereIn('status', [
                        TaskStatus::Scheduled->value,
                        TaskStatus::Rescheduled->value,
                    ])
                    ->orderBy('due_date')
                    ->orderBy('due_time')
            )
            ->columns([
                Tables\Columns\TextColumn::make('due_date')
                    ->label('Data')
                    ->date('d/m/Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('due_time')
                    ->label('Hora')
                    ->time('H:i'),

                Tables\Columns\TextColumn::make('title')
                    ->label('Título')
                    ->searchable()
                    ->limit(50),

                Tables\Columns\TextColumn::make('lawyers.name')
                    ->label('Advogado(s)')
                    ->badge()
                    ->separator(','),

                Tables\Columns\BadgeColumn::make('status')
                    ->label('Status')
                    ->formatStateUsing(fn (TaskStatus $state) => $state->label())
                    ->colors([
                        'warning' => TaskStatus::Scheduled->value,
                        'gray'    => TaskStatus::Rescheduled->value,
                    ]),
            ])
            ->actions([
                Tables\Actions\Action::make('ver')
                    ->url(fn (Task $record) => \App\Filament\Resources\TaskResource::getUrl('view', ['record' => $record]))
                    ->icon('heroicon-o-eye')
                    ->color('gray'),
            ])
            ->paginated([5, 10])
            ->emptyStateHeading('Nenhum agendamento nos próximos 10 dias')
            ->emptyStateIcon('heroicon-o-clock');
    }
}