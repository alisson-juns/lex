<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PowerOfAttorneyResource\Pages;
use App\Models\PowerOfAttorney;
use AmidEsfahani\FilamentTinyEditor\TinyEditor;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PowerOfAttorneyResource extends Resource
{
    protected static ?string $model         = PowerOfAttorney::class;
    protected static ?string $modelLabel    = 'Procuração';
    protected static ?string $pluralModelLabel = 'Procurações';
    protected static ?string $navigationGroup = 'Documentos';
    protected static ?string $navigationIcon  = 'heroicon-o-document-text';

    // Ocultar do menu — acesso só via redirecionamento
    protected static bool $shouldRegisterNavigation = false;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make()
                ->schema([
                    TinyEditor::make('rendered_body')
                        ->label('Conteúdo da Procuração')
                        ->required()
                        ->columnSpanFull()
                        ->profile('full'),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('client.name')->label('Cliente'),
            Tables\Columns\TextColumn::make('template.name')->label('Tipo'),
            Tables\Columns\TextColumn::make('created_at')->label('Data')->dateTime('d/m/Y H:i'),
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPowerOfAttorneys::route('/'),
            'edit'  => Pages\EditPowerOfAttorney::route('/{record}/edit'),
        ];
    }
}
