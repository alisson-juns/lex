<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PowerOfAttorneyTemplateResource\Pages;
use App\Models\PowerOfAttorneyTemplate;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PowerOfAttorneyTemplateResource extends Resource
{
    protected static ?string $model = PowerOfAttorneyTemplate::class;
    protected static ?string $modelLabel = 'Modelo de Procuração';
    protected static ?string $pluralModelLabel = 'Modelos de Procuração';
    protected static ?string $navigationGroup = 'Configurações';
    protected static ?string $navigationIcon  = 'heroicon-o-document-text';
    protected static ?int $navigationSort     = 98;

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

            Forms\Components\RichEditor::make('body_text')
                ->label('Texto da Procuração')
                ->required()
                ->columnSpanFull()
                ->toolbarButtons([
                    'bold', 'italic', 'underline',
                    'bulletList', 'orderedList',
                    'undo', 'redo',
                ])
                ->helperText('Placeholders disponíveis: {{client_name}}, {{client_nationality}}, {{client_marital_status}}, {{client_profession}}, {{client_rg}}, {{client_cpf}}, {{client_mother}}, {{client_father}}, {{client_date_of_birth}}, {{client_address}}, {{client_email}}, {{firm_lawyers}}, {{specific_text}}'),
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
            'index'  => Pages\ListPowerOfAttorneyTemplates::route('/'),
            'create' => Pages\CreatePowerOfAttorneyTemplate::route('/create'),
            'edit'   => Pages\EditPowerOfAttorneyTemplate::route('/{record}/edit'),
        ];
    }
}