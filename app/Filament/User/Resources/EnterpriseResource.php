<?php

namespace App\Filament\User\Resources;

use App\Filament\User\Resources\EnterpriseResource\Pages;
use App\Models\Enterprise;
use App\Models\EnterpriseDocument;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\Wizard;
use Filament\Forms\Components\Wizard\Step;
use Leandrocfe\FilamentPtbrFormFields\Cep;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationGroup;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;


class EnterpriseResource extends Resource
{
    protected static ?string $model = Enterprise::class;
    protected static ?string $modelLabel = 'Empresa';
    protected static ?string $navigationGroup = 'Controle de Clientes';
    protected static ?int $navigationSort = 2;
    protected static ?string $navigationLabel = 'Pessoa Jurídica';
    protected static ?string $navigationIcon = 'heroicon-o-building-office-2';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Wizard::make([
                    Step::make('Dados da Empresa')
                        ->icon('heroicon-m-building-office')
                        ->schema([
                            Forms\Components\TextInput::make('corporate_reason')
                                ->required()
                                ->label('Razão Social')
                                ->maxLength(255),
                            Forms\Components\TextInput::make('trade_name')
                                ->label('Nome Fantasia')
                                ->maxLength(255),
                            Forms\Components\Textarea::make('note')
                                ->label('Observações')
                                ->columnSpan(2),

                        ])
                        ->columns(2),

                    Step::make('Documentos')
                    ->icon('heroicon-m-document-text')

                        ->schema([
                            Forms\Components\Group::make()
                                ->relationship('documents')
                                ->schema([
                                    Forms\Components\TextInput::make('cnpj')
                                        ->required()
                                        ->label('CNPJ')
                                        ->mask('99.999.999/9999-99')
                                        ->rule('cnpj')
                                        ->maxLength(18)
                                        ->unique(EnterpriseDocument::class, 'cnpj', ignoreRecord: true)
                                        ->validationMessages([
                                            'required' => 'O campo CNPJ é obrigatório.',
                                            'cnpj' => 'Número de CNPJ inválido.',
                                            'unique' => 'Este CNPJ já foi registrado.',
                                        ]),
                                    Forms\Components\TextInput::make('ie')
                                        ->label('Inscrição Estadual')
                                        ->maxLength(18),
                                    Forms\Components\TextInput::make('im')
                                        ->label('Inscrição Municipal')
                                        ->maxLength(18),
                                    Forms\Components\Textarea::make('other_documents')
                                        ->label('Outros Documentos')
                                        ->maxLength(65535)
                                        ->rows(3),
                                ])
                                ->columns(2),
                        ])

                ])
                    ->skippable()
                    ->columnSpan('full'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('documents.cnpj')
                    ->label('CNPJ')
                    ->searchable(),
                Tables\Columns\TextColumn::make('corporate_reason')
                    ->label('Razão Social')
                    ->searchable(),
                Tables\Columns\TextColumn::make('trade_name')
                    ->label('Nome Fantasia')
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Criado em')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Atualizado em')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
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
            'index' => Pages\ListEnterprises::route('/'),
            'create' => Pages\CreateEnterprise::route('/create'),
            'view' => Pages\ViewEnterprise::route('/{record}'),
            'edit' => Pages\EditEnterprise::route('/{record}/edit'),
        ];
    }
}
