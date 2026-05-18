<?php

namespace App\Filament\Resources;

use App\Filament\Resources\GratuityDeclarationResource\Pages;
use App\Models\GratuityDeclaration;
use AmidEsfahani\FilamentTinyEditor\TinyEditor;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class GratuityDeclarationResource extends Resource
{
    protected static ?string $model            = GratuityDeclaration::class;
    protected static ?string $modelLabel       = 'Declaração de Gratuidade';
    protected static ?string $pluralModelLabel = 'Declarações de Gratuidade';
    protected static ?string $navigationGroup  = 'Documentos';
    protected static ?string $navigationIcon   = 'heroicon-o-document-text';

    protected static bool $shouldRegisterNavigation = false;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make()
                ->schema([
                    TinyEditor::make('rendered_body')
                        ->label('Conteúdo da Declaração')
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
            Tables\Columns\TextColumn::make('template.name')->label('Modelo'),
            Tables\Columns\TextColumn::make('created_at')->label('Data')->dateTime('d/m/Y H:i'),
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListGratuityDeclarations::route('/'),
            'edit'  => Pages\EditGratuityDeclaration::route('/{record}/edit'),
        ];
    }
}
