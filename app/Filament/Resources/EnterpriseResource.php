<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EnterpriseResource\Pages;
use App\Models\Enterprise;
use App\Models\EnterpriseDocument;
use Filament\Forms\Components\Wizard;
use Filament\Forms\Components\Wizard\Step;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Leandrocfe\FilamentPtbrFormFields\Cep;
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

    public static function getRelations(): array
    {
        return [
            \App\Filament\Resources\EnterpriseResource\RelationManagers\LegalCasesRelationManager::class,
        ];
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Wizard::make([
                    Step::make('Dados da Empresa')
                        ->icon('heroicon-m-building-office')
                        ->schema([
                            Forms\Components\TextInput::make('corporate_reason')
                                ->label('Razão Social')
                                ->required()
                                ->maxLength(255),
                            Forms\Components\TextInput::make('trade_name')
                                ->label('Nome Fantasia')
                                ->maxLength(255),
                            Forms\Components\Textarea::make('note')
                                ->label('Observações')
                                ->rows(3)
                                ->columnSpanFull(),
                        ])
                        ->columns(2),

                    Step::make('Documentos')
                        ->icon('heroicon-m-document-text')
                        ->schema([
                            Forms\Components\Group::make()
                                ->relationship('enterprise_documents')
                                ->schema([
                                    Forms\Components\TextInput::make('cnpj')
                                        ->label('CNPJ')
                                        ->required()
                                        ->mask('99.999.999/9999-99')
                                        ->rule('cnpj')
                                        ->maxLength(18)
                                        ->unique(EnterpriseDocument::class, 'cnpj', ignoreRecord: true)
                                        ->validationMessages([
                                            'required' => 'O campo CNPJ é obrigatório.',
                                            'cnpj'     => 'Número de CNPJ inválido.',
                                            'unique'   => 'Este CNPJ já foi registrado.',
                                        ]),
                                    Forms\Components\TextInput::make('ie')
                                        ->label('Inscrição Estadual')
                                        ->maxLength(18),
                                    Forms\Components\TextInput::make('im')
                                        ->label('Inscrição Municipal')
                                        ->maxLength(18),
                                    Forms\Components\Textarea::make('other_documents')
                                        ->label('Outros Documentos')
                                        ->rows(3)
                                        ->columnSpanFull(),
                                ])
                                ->columns(2),
                        ]),

                    Step::make('Endereço')
                        ->icon('heroicon-m-map-pin')
                        ->schema([
                            Forms\Components\Group::make()
                                ->relationship('enterprise_addresses')
                                ->schema([
                                    Cep::make('zipcode')
                                        ->label('CEP')
                                        ->viaCep(
                                            mode: 'suffix',
                                            errorMessage: 'CEP inválido.',
                                            setFields: [
                                                'street'   => 'logradouro',
                                                'district' => 'bairro',
                                                'city'     => 'localidade',
                                                'state'    => 'uf',
                                            ]
                                        ),
                                    Forms\Components\TextInput::make('street')
                                        ->label('Logradouro')
                                        ->maxLength(255),
                                    Forms\Components\TextInput::make('number')
                                        ->label('Número')
                                        ->maxLength(50),
                                    Forms\Components\TextInput::make('complement')
                                        ->label('Complemento')
                                        ->maxLength(50),
                                    Forms\Components\TextInput::make('district')
                                        ->label('Bairro')
                                        ->maxLength(50),
                                    Forms\Components\TextInput::make('city')
                                        ->label('Cidade')
                                        ->maxLength(50),
                                    Forms\Components\TextInput::make('state')
                                        ->label('Estado')
                                        ->maxLength(50),
                                ])
                                ->columns(2),
                        ]),

                    Step::make('Contatos')
                        ->icon('heroicon-m-phone')
                        ->schema([
                            Forms\Components\Group::make()
                                ->relationship('enterprise_contacts')
                                ->schema([
                                    Forms\Components\TextInput::make('email')
                                        ->label('E-mail')
                                        ->email()
                                        ->maxLength(50),
                                    Forms\Components\TextInput::make('optional_email')
                                        ->label('E-mail Alternativo')
                                        ->email()
                                        ->maxLength(50),
                                    Forms\Components\TextInput::make('cellphone')
                                        ->label('Celular')
                                        ->mask('(99) 99999-9999')
                                        ->maxLength(20),
                                    Forms\Components\TextInput::make('phone')
                                        ->label('Telefone Fixo')
                                        ->mask('(99) 9999-9999')
                                        ->maxLength(20),
                                    Forms\Components\TextInput::make('message_cell_phone')
                                        ->label('WhatsApp / Celular Recado')
                                        ->mask('(99) 99999-9999')
                                        ->maxLength(20),
                                    Forms\Components\TextInput::make('message_phone')
                                        ->label('Telefone Recado')
                                        ->mask('(99) 9999-9999')
                                        ->maxLength(20),
                                    Forms\Components\Textarea::make('note')
                                        ->label('Observações')
                                        ->rows(3)
                                        ->columnSpanFull(),
                                ])
                                ->columns(2),
                        ]),

                    Step::make('Dados Bancários')
                        ->icon('heroicon-m-banknotes')
                        ->schema([
                            Forms\Components\Group::make()
                                ->relationship('enterprise_bank_accounts')
                                ->schema([
                                    Forms\Components\TextInput::make('bank_number')
                                        ->label('Número do Banco')
                                        ->maxLength(10),
                                    Forms\Components\TextInput::make('bank_name')
                                        ->label('Nome do Banco')
                                        ->maxLength(50),
                                    Forms\Components\TextInput::make('agency')
                                        ->label('Agência')
                                        ->maxLength(20),
                                    Forms\Components\TextInput::make('account')
                                        ->label('Conta')
                                        ->maxLength(20),
                                ])
                                ->columns(2),
                        ]),

                    Step::make('Representantes')
                        ->icon('heroicon-m-users')
                        ->schema([
                            Forms\Components\Repeater::make('enterprise_representatives')
                                ->relationship()
                                ->schema([
                                    Forms\Components\TextInput::make('name')
                                        ->label('Nome')
                                        ->required()
                                        ->maxLength(255),
                                    Forms\Components\Select::make('gender')
                                        ->label('Gênero')
                                        ->options([
                                            'male'   => 'Masculino',
                                            'female' => 'Feminino',
                                            'other'  => 'Outro',
                                        ]),
                                    Forms\Components\TextInput::make('position')
                                        ->label('Cargo / Função')
                                        ->maxLength(255),
                                    Forms\Components\Textarea::make('note')
                                        ->label('Observações')
                                        ->rows(2)
                                        ->columnSpanFull(),
                                ])
                                ->columns(2)
                                ->addActionLabel('Adicionar Representante')
                                ->columnSpanFull(),
                        ]),
                ])
                ->columnSpanFull()
                ->skippable(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('corporate_reason')
                    ->label('Razão Social')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('trade_name')
                    ->label('Nome Fantasia')
                    ->searchable(),
                Tables\Columns\TextColumn::make('enterprise_documents.cnpj')
                    ->label('CNPJ')
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Cadastrado em')
                    ->date('d/m/Y')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                ]),
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([SoftDeletingScope::class]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListEnterprises::route('/'),
            'create' => Pages\CreateEnterprise::route('/create'),
            'view'   => Pages\ViewEnterprise::route('/{record}'),
            'edit'   => Pages\EditEnterprise::route('/{record}/edit'),
        ];
    }
}