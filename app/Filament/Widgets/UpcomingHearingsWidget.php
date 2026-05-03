<?php

namespace App\Filament\Widgets;

use App\Enums\HearingStatus;
use App\Models\Hearing;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class UpcomingHearingsWidget extends BaseWidget
{
    protected static ?int $sort = 3;
    protected int | string | array $columnSpan = 'full';
    protected static ?string $heading = 'Audiências nos próximos 10 dias';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Hearing::query()
                    ->whereBetween('date', [now(), now()->addDays(10)])
                    ->whereNotIn('status', [
                        HearingStatus::Cancelled->value,
                        HearingStatus::Completed->value,
                    ])
                    ->orderBy('date')
                    ->orderBy('time')
            )
            ->columns([
                Tables\Columns\TextColumn::make('date')
                    ->label('Data')
                    ->date('d/m/Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('time')
                    ->label('Hora')
                    ->time('H:i'),

                Tables\Columns\TextColumn::make('description')
                    ->label('Descrição')
                    ->limit(50)
                    ->searchable(),

                Tables\Columns\TextColumn::make('lawyer.name')
                    ->label('Advogado'),

                Tables\Columns\TextColumn::make('location')
                    ->label('Local')
                    ->limit(40),

                Tables\Columns\BadgeColumn::make('status')
                    ->label('Status')
                    ->formatStateUsing(fn (HearingStatus $state) => $state->label())
                    ->colors([
                        'success' => HearingStatus::Scheduled->value,
                        'warning' => HearingStatus::Postponed->value,
                        'gray'    => HearingStatus::Suspended->value,
                    ]),
            ])
            ->actions([
                Tables\Actions\Action::make('ver')
                    ->url(fn (Hearing $record) => \App\Filament\Resources\HearingResource::getUrl('view', ['record' => $record]))
                    ->icon('heroicon-o-eye')
                    ->color('gray'),
            ])
            ->paginated([5, 10])
            ->emptyStateHeading('Nenhuma audiência nos próximos 10 dias')
            ->emptyStateIcon('heroicon-o-scale');
    }
}