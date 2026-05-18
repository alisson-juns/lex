<?php

namespace App\Filament\Resources;

use App\Filament\Resources\GratuityDeclarationTemplateResource\Pages;
use App\Models\GratuityDeclarationTemplate;
use AmidEsfahani\FilamentTinyEditor\TinyEditor;
use Filament\Forms;
use Filament\Forms\Components\View;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class GratuityDeclarationTemplateResource extends Resource
{
    protected static ?string $model            = GratuityDeclarationTemplate::class;
    protected static ?string $modelLabel       = 'Modelo de Declaração';
    protected static ?string $pluralModelLabel = 'Modelos de Declaração';
    protected static ?string $navigationGroup  = 'Configurações';
    protected static ?string $navigationIcon   = 'heroicon-o-document-text';
    protected static ?int    $navigationSort   = 98;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('name')
                ->label('Nome do Modelo')
                ->required()
                ->maxLength(100),

            Forms\Components\Toggle::make('is_active')
                ->label('Ativo')
                ->default(true)
                ->inline(false),

            View::make('filament.components.placeholder-buttons-gratuity-declaration')
                ->columnSpanFull(),

            TinyEditor::make('body_text')
                ->label('Corpo da Declaração')
                ->required()
                ->columnSpanFull()
                ->profile('full'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->label('Modelo')->searchable()->sortable(),
                Tables\Columns\IconColumn::make('is_active')->label('Ativo')->boolean(),
                Tables\Columns\TextColumn::make('updated_at')->label('Última edição')->dateTime('d/m/Y H:i')->sortable(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListGratuityDeclarationTemplates::route('/'),
            'create' => Pages\CreateGratuityDeclarationTemplate::route('/create'),
            'edit'   => Pages\EditGratuityDeclarationTemplate::route('/{record}/edit'),
        ];
    }
}
