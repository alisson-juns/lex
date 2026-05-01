<?php

namespace App\Filament\Resources\ClientResource\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Actions\Action;

class PowersOfAttorneyRelationManager extends RelationManager
{
    protected static string $relationship = 'powersOfAttorney';
    protected static ?string $title       = 'Procurações Geradas';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('template.name')
                    ->label('Tipo'),
                Tables\Columns\TextColumn::make('specific_text')
                    ->label('Fim específico')
                    ->limit(60),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Gerado por'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Data')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->actions([
                Action::make('pdf')
                    ->label('Abrir PDF')
                    ->icon('heroicon-o-document')
                    ->color('gray')
                    ->url(fn ($record) => route('power-of-attorney.pdf', $record->id))
                    ->openUrlInNewTab(),

                Tables\Actions\DeleteAction::make(),
            ]);
    }
}