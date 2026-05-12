<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EnterprisePowerOfAttorneyResource\Pages;
use App\Models\EnterprisePowerOfAttorney;
use AmidEsfahani\FilamentTinyEditor\TinyEditor;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class EnterprisePowerOfAttorneyResource extends Resource
{
    protected static ?string $model            = EnterprisePowerOfAttorney::class;
    protected static ?string $modelLabel       = 'Procuração PJ';
    protected static ?string $pluralModelLabel = 'Procurações PJ';
    protected static bool    $shouldRegisterNavigation = false;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make()->schema([
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
            Tables\Columns\TextColumn::make('enterprise.corporate_reason')->label('Empresa'),
            Tables\Columns\TextColumn::make('template.name')->label('Tipo'),
            Tables\Columns\TextColumn::make('created_at')->label('Data')->dateTime('d/m/Y H:i'),
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListEnterprisePowersOfAttorney::route('/'),
            'edit'  => Pages\EditEnterprisePowerOfAttorney::route('/{record}/edit'),
        ];
    }
}
