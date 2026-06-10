<?php

namespace App\Filament\Widgets;

use App\Enums\DeadlineStatus;
use App\Enums\DeadlineType;
use App\Filament\Resources\DeadlineResource;
use App\Models\Deadline;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;

class UpcomingDeadlinesWidget extends BaseWidget
{
    protected static ?string $heading = 'Prazos fatais próximos';

    protected int|string|array $columnSpan = 'half';

    protected static ?int $sort = 3;

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Deadline::query()
                    ->where('status', DeadlineStatus::Pending->value)
                    ->whereDate('fatal_date', '>=', now()->toDateString())
                    ->whereDate('fatal_date', '<=', now()->addDays(7)->toDateString())
                    ->with('legalCase', 'lawyers')
                    ->orderBy('fatal_date', 'asc')
            )
            ->columns([
                Tables\Columns\TextColumn::make('fatal_date')
                    ->label('Prazo fatal')
                    ->date('d/m/Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('days_remaining')
                    ->label('Faltam')
                    ->badge()
                    ->getStateUsing(function (Deadline $record): string {
                        $days = (int) now()->startOfDay()
                            ->diffInDays($record->fatal_date->startOfDay(), false);

                        return match (true) {
                            $days === 0 => 'Hoje',
                            $days === 1 => '1 dia',
                            default     => "{$days} dias",
                        };
                    })
                    ->color(function (Deadline $record): string {
                        $days = (int) now()->startOfDay()
                            ->diffInDays($record->fatal_date->startOfDay(), false);

                        return $days <= 3 ? 'danger' : 'warning';
                    }),

                Tables\Columns\TextColumn::make('deadline_type')
                    ->label('Tipo')
                    ->formatStateUsing(fn (DeadlineType $state) => $state->label()),

                Tables\Columns\TextColumn::make('legalCase.case_number')
                    ->label('Processo'),

                Tables\Columns\TextColumn::make('lawyers.name')
                    ->label('Advogado(s)')
                    ->badge()
                    ->separator(','),

                Tables\Columns\TextColumn::make('internal_date')
                    ->label('Prazo interno')
                    ->date('d/m/Y')
                    ->placeholder('—')
                    ->toggleable(),
            ])
            ->defaultSort('fatal_date', 'asc')
            ->actions([
                Tables\Actions\Action::make('view')
                    ->label('Ver')
                    ->icon('heroicon-o-eye')
                    ->url(fn (Deadline $record) => DeadlineResource::getUrl('view', ['record' => $record->id])),
            ])
            ->paginated([5, 10, 25])
            ->emptyStateHeading('Nenhum prazo fatal nos próximos 7 dias')
            ->emptyStateIcon('heroicon-o-check-circle');
    }
}
