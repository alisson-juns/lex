<?php

namespace App\Filament\Resources\ClientResource\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Actions\Action;

class FeeAgreementsRelationManager extends RelationManager
{
    protected static string $relationship = 'feeAgreements';
    protected static ?string $title       = 'Contratos de Honorários';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('template.name')
                    ->label('Tipo'),
                Tables\Columns\TextColumn::make('specific_text')
                    ->label('Ação')
                    ->limit(50),
                Tables\Columns\TextColumn::make('fee_percentage')
                    ->label('Honorários')
                    ->formatStateUsing(fn ($state) => $state . '%'),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Gerado por'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Data')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->actions([
                Action::make('editar')
                    ->label('Editar')
                    ->icon('heroicon-o-pencil')
                    ->color('warning')
                    ->url(fn ($record) => \App\Filament\Resources\FeeAgreementResource::getUrl('edit', ['record' => $record->id])),

                Action::make('pdf')
                    ->label('Abrir PDF')
                    ->icon('heroicon-o-document')
                    ->color('gray')
                    ->url(
                        fn ($record) => $record->pdf_path
                        ? \Storage::disk('public')->url($record->pdf_path)
                        : route('fee-agreement.pdf', $record->id)
                    )
                    ->openUrlInNewTab(),
            ]);
    }
}
