<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LawyerResource\Pages;
use App\Models\Lawyer;
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

class LawyerResource extends Resource
{
    protected static ?string $model = Lawyer::class;
    protected static ?string $navigationIcon = 'heroicon-o-scale';
    protected static ?string $navigationGroup = 'Controle de Advogados';
    protected static ?int $navigationSort = 1;
    protected static ?string $navigationLabel = 'Advogados';
    protected static ?string $modelLabel = 'Advogado';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Wizard::make([
                    Step::make('Dados Pessoais')
                        ->icon('heroicon-m-user')
                        ->description('Informações principais do advogado')
                        ->schema([
                            Forms\Components\TextInput::make('name')
                                ->label('Nome')
                                ->required()
                                ->maxLength(255),
                            Forms\Components\DatePicker::make('date_of_birth')
                                ->label('Data de nascimento'),
                            Forms\Components\Select::make('gender')
                                ->label('Gênero')
                                ->options([
                                    'male' => 'Masculino',
                                    'female' => 'Feminino',
                                    'other' => 'Outro',
                                ]),
                            Forms\Components\Select::make('marital_status')
                                ->label('Estado civil')
                                ->options([
                                    'single' => 'Solteiro(a)',
                                    'married' => 'Casado(a)',
                                    'separated' => 'Separado(a)',
                                    'divorced' => 'Divorciado(a)',
                                    'widowed' => 'Viúvo(a)',
                                ]),
                            Forms\Components\TextInput::make('father')
                                ->label('Pai')
                                ->maxLength(255),
                            Forms\Components\TextInput::make('mother')
                                ->label('Mãe')
                                ->maxLength(255),
                            Forms\Components\TextInput::make('place_of_birth')
                                ->label('Naturalidade')
                                ->maxLength(255),
                            Forms\Components\TextInput::make('nationality')
                                ->label('Nacionalidade')
                                ->default('Brasileira')
                                ->maxLength(255),
                            Forms\Components\Toggle::make('active')
                                ->label('Ativo')
                                ->default(true),
                            Forms\Components\Textarea::make('note')
                                ->label('Observações')
                                ->rows(3)
                                ->columnSpanFull(),
                        ])
                        ->columns(2),

                    Step::make('OAB')
                        ->icon('heroicon-m-identification')
                        ->description('Dados da inscrição na OAB')
                        ->schema([
                            Forms\Components\TextInput::make('oab')
                                ->label('Número OAB')
                                ->required()
                                ->unique('lawyers', 'oab', ignoreRecord: true)
                                ->maxLength(255)
                                ->validationMessages([
                                    'required' => 'O número da OAB é obrigatório.',
                                    'unique' => 'Este número de OAB já está registrado.',
                                ]),
                            Forms\Components\TextInput::make('oab_state')
                                ->label('Estado (OAB)')
                                ->maxLength(2),
                            Forms\Components\TextInput::make('oab_subsection')
                                ->label('Subseção')
                                ->maxLength(255),
                            Forms\Components\DatePicker::make('oab_date')
                                ->label('Data de inscrição'),
                        ])
                        ->columns(2),

                    Step::make('Documentos')
                        ->icon('heroicon-m-document-text')
                        ->description('Documentação do advogado')
                        ->schema([
                            Forms\Components\Group::make()
                                ->relationship('lawyer_documents')
                                ->schema([
                                    Forms\Components\TextInput::make('cpf')
                                        ->label('CPF')
                                        ->mask('999.999.999-99')
                                        ->maxLength(14)
                                        ->rule('cpf')
                                        ->required()
                                        ->unique('lawyer_documents', 'cpf', ignoreRecord: true)
                                        ->validationMessages([
                                            'required' => 'O campo CPF é obrigatório.',
                                            'cpf' => 'Número de CPF inválido.',
                                            'unique' => 'Este CPF já foi registrado.',
                                        ]),
                                    Forms\Components\TextInput::make('rg')
                                        ->label('RG')
                                        ->mask('99.999.999-9')
                                        ->maxLength(12),
                                    Forms\Components\TextInput::make('cnh')
                                        ->label('CNH')
                                        ->mask('99999999999')
                                        ->rule('cnh')
                                        ->maxLength(11)
                                        ->validationMessages([
                                            'cnh' => 'Número de CNH inválido.',
                                        ]),
                                    Forms\Components\TextInput::make('pis')
                                        ->label('PIS')
                                        ->mask('999.99999.99-9')
                                        ->rule('pis')
                                        ->maxLength(14)
                                        ->validationMessages([
                                            'pis' => 'Número de PIS inválido.',
                                        ]),
                                    Forms\Components\TextInput::make('ctps')
                                        ->label('CTPS')
                                        ->maxLength(20),
                                    Forms\Components\TextInput::make('rnm')
                                        ->label('RNM')
                                        ->maxLength(20),
                                    Forms\Components\Textarea::make('other_documents')
                                        ->label('Outros documentos')
                                        ->rows(3)
                                        ->columnSpanFull(),
                                ])
                                ->columns(2),
                        ]),

                    Step::make('Endereço')
                        ->icon('heroicon-m-map-pin')
                        ->description('Endereço do advogado')
                        ->schema([
                            Forms\Components\Group::make()
                                ->relationship('lawyer_addresses')
                                ->schema([
                                    Cep::make('zipcode')
                                        ->label('CEP')
                                        ->viaCep(
                                            mode: 'suffix',
                                            errorMessage: 'CEP inválido.',
                                            setFields: [
                                                'street' => 'logradouro',
                                                'number' => 'numero',
                                                'complement' => 'complemento',
                                                'district' => 'bairro',
                                                'city' => 'localidade',
                                                'state' => 'uf',
                                            ]
                                        )
                                        ->live(onBlur: true),
                                    Forms\Components\TextInput::make('street')
                                        ->label('Endereço')
                                        ->maxLength(255),
                                    Forms\Components\TextInput::make('number')
                                        ->label('Número')
                                        ->maxLength(10),
                                    Forms\Components\TextInput::make('complement')
                                        ->label('Complemento')
                                        ->maxLength(50),
                                    Forms\Components\TextInput::make('district')
                                        ->label('Bairro')
                                        ->maxLength(100),
                                    Forms\Components\TextInput::make('city')
                                        ->label('Cidade')
                                        ->maxLength(100),
                                    Forms\Components\TextInput::make('state')
                                        ->label('Estado')
                                        ->maxLength(2),
                                ])
                                ->columns(2),
                        ]),

                    Step::make('Contatos')
                        ->icon('heroicon-m-phone')
                        ->description('E-mail e telefone do advogado')
                        ->schema([
                            Forms\Components\Group::make()
                                ->relationship('lawyer_contacts')
                                ->schema([
                                    Forms\Components\TextInput::make('email')
                                        ->label('E-mail')
                                        ->email()
                                        ->maxLength(255),
                                    Forms\Components\TextInput::make('cellphone')
                                        ->label('Celular')
                                        ->mask('(99) 99999-9999')
                                        ->maxLength(15),
                                    Forms\Components\TextInput::make('phone')
                                        ->label('Telefone')
                                        ->mask('(99) 9999-9999')
                                        ->maxLength(14),
                                    Forms\Components\TextInput::make('optional_email')
                                        ->label('E-mail opcional')
                                        ->email()
                                        ->maxLength(255),
                                    Forms\Components\TextInput::make('message_cell_phone')
                                        ->label('Celular para mensagens')
                                        ->mask('(99) 99999-9999')
                                        ->maxLength(15),
                                    Forms\Components\TextInput::make('message_phone')
                                        ->label('Telefone para mensagens')
                                        ->mask('(99) 9999-9999')
                                        ->maxLength(14),
                                    Forms\Components\Textarea::make('note')
                                        ->label('Observações')
                                        ->rows(3)
                                        ->columnSpanFull(),
                                ])
                                ->columns(2),
                        ]),
                ])
                    ->skippable()
                    ->columnSpan('full'),
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
                Tables\Columns\TextColumn::make('oab')
                    ->label('OAB')
                    ->searchable(),
                Tables\Columns\TextColumn::make('oab_state')
                    ->label('Estado')
                    ->sortable(),
                Tables\Columns\IconColumn::make('active')
                    ->label('Ativo')
                    ->boolean(),
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
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
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
            ->withoutGlobalScope(SoftDeletingScope::class);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLawyers::route('/'),
            'create' => Pages\CreateLawyer::route('/create'),
            'view' => Pages\ViewLawyer::route('/{record}'),
            'edit' => Pages\EditLawyer::route('/{record}/edit'),
        ];
    }
}