<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EnterpriseFeeAgreementResource\Pages;
use App\Models\EnterpriseFeeAgreement;
use AmidEsfahani\FilamentTinyEditor\TinyEditor;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class EnterpriseFeeAgreementResource extends Resource
{
    protected static ?string $model            = EnterpriseFeeAgreement::class;
    protected static ?string $modelLabel       = 'Contrato de Honorários (PJ)';
    protected static ?string $pluralModelLabel = 'Contratos de Honorários (PJ)';
    protected static ?string $navigationGroup  = 'Documentos';
    protected static ?string $navigationIcon   = 'heroicon-o-document-check';

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
            Tables\Columns\TextColumn::make('enterprise.corporate_reason')->label('Empresa'),
            Tables\Columns\TextColumn::make('template.name')->label('Tipo'),
            Tables\Columns\TextColumn::make('created_at')->label('Data')->dateTime('d/m/Y H:i'),
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListEnterpriseFeeAgreements::route('/'),
            'edit'  => Pages\EditEnterpriseFeeAgreement::route('/{record}/edit'),
        ];
    }
}
