<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EnterprisePowerOfAttorneyTemplateResource\Pages;
use App\Models\EnterprisePowerOfAttorneyTemplate;
use AmidEsfahani\FilamentTinyEditor\TinyEditor;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\View;

class EnterprisePowerOfAttorneyTemplateResource extends Resource
{
    protected static ?string $model           = EnterprisePowerOfAttorneyTemplate::class;
    protected static ?string $modelLabel      = 'Modelo de Procuração PJ';
    protected static ?string $pluralModelLabel = 'Modelos de Procuração PJ';
    protected static ?string $navigationGroup = 'Modelos de Documentos';
    protected static ?string $navigationIcon  = 'heroicon-o-document-chart-bar';
    protected static ?int    $navigationSort  = 98;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('name')
                ->label('Nome do Modelo')
                ->required()
                ->maxLength(100),

            Forms\Components\Toggle::make('is_active')
                ->label('Ativo')
                ->default(true),

            View::make('filament.components.placeholder-buttons-enterprise')
                ->columnSpanFull(),

            TinyEditor::make('body_text')
                ->label('Conteúdo do Modelo')
                ->required()
                ->columnSpanFull()
                ->profile('full'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nome')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Ativo')
                    ->boolean(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Atualizado em')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListEnterprisePowerOfAttorneyTemplates::route('/'),
            'create' => Pages\CreateEnterprisePowerOfAttorneyTemplate::route('/create'),
            'view'   => Pages\ViewEnterprisePowerOfAttorneyTemplate::route('/{record}'),
            'edit'   => Pages\EditEnterprisePowerOfAttorneyTemplate::route('/{record}/edit'),
        ];
    }
}
