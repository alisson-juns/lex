<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EnterpriseFeeAgreementTemplateResource\Pages;
use App\Models\EnterpriseFeeAgreementTemplate;
use AmidEsfahani\FilamentTinyEditor\TinyEditor;
use Filament\Forms;
use Filament\Forms\Components\View;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class EnterpriseFeeAgreementTemplateResource extends Resource
{
    protected static ?string $model            = EnterpriseFeeAgreementTemplate::class;
    protected static ?string $modelLabel       = 'Modelo de Contrato PJ';
    protected static ?string $pluralModelLabel = 'Modelos de Contrato PJ';
    protected static ?string $navigationGroup  = 'Modelos de Documentos';
    protected static ?string $navigationIcon   = 'heroicon-o-document-check';
    protected static ?int    $navigationSort   = 97;

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

            View::make('filament.components.placeholder-buttons-enterprise-fee-agreement')
                ->columnSpanFull(),

            TinyEditor::make('body_text')
                ->label('Corpo do Contrato')
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
                    ->label('Modelo')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Ativo')
                    ->boolean(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Última edição')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListEnterpriseFeeAgreementTemplates::route('/'),
            'create' => Pages\CreateEnterpriseFeeAgreementTemplate::route('/create'),
            'edit'   => Pages\EditEnterpriseFeeAgreementTemplate::route('/{record}/edit'),
        ];
    }
}
