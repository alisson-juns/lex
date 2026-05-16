<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FeeAgreementResource\Pages;
use App\Models\FeeAgreement;
use AmidEsfahani\FilamentTinyEditor\TinyEditor;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class FeeAgreementResource extends Resource
{
    protected static ?string $model            = FeeAgreement::class;
    protected static ?string $modelLabel       = 'Contrato de Honorários';
    protected static ?string $pluralModelLabel = 'Contratos de Honorários';
    protected static ?string $navigationGroup  = 'Documentos';
    protected static ?string $navigationIcon   = 'heroicon-o-document-check';

    // Oculto do menu — acesso somente via redirecionamento a partir do cliente
    protected static bool $shouldRegisterNavigation = false;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make()
                ->schema([
                    TinyEditor::make('rendered_body')
                        ->label('Conteúdo do Contrato')
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
            'index' => Pages\ListFeeAgreements::route('/'),
            'edit'  => Pages\EditFeeAgreement::route('/{record}/edit'),
        ];
    }
}
