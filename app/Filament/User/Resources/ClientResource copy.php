<?php

namespace App\Filament\User\Resources;

use App\Filament\User\Resources\ClientResource\Pages;
use App\Filament\User\Resources\ClientResource\RelationManagers;
use App\Models\Client;
use Filament\Forms\Components\Wizard;
use Filament\Forms\Components\Wizard\Step;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\TextInput;

use Leandrocfe\FilamentPtbrFormFields\Document;

use Illuminate\Http\Request;
use LaravelLegends\PtBrValidator\Rules\FormatoCpf;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ClientResource extends Resource
{
    protected static ?string $model = Client::class;

    protected static ?string $modelLabel = 'Cliente';

    protected static ?string $navigationLabel = 'Controle de clientes';

    protected static ?string $navigationIcon = 'heroicon-o-users';

    


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Wizard::make([
                    Step::make('Dados Pessoais')
                        ->schema([
                            Forms\Components\TextInput::make('name')
                                ->label('Nome')
                                ->required()
                                ->maxLength(255),
                            Forms\Components\DatePicker::make('date_of_birth')
                                ->label('Data de nascimento'),
                            Forms\Components\Select::make('gender')
                                ->options([
                                    'male' => 'Masculino',
                                    'female' => 'Feminino',
                                    'other' => 'Outro',
                                ])
                                ->label('Gênero'),
                            Forms\Components\TextInput::make('father')
                                ->label('Pai')
                                ->maxLength(255),
                            Forms\Components\TextInput::make('mother')
                                ->label('Mãe')
                                ->required()
                                ->maxLength(255),
                            Forms\Components\TextInput::make('place_of_birth')
                                ->label('Naturalidade')
                                ->maxLength(255),
                            Forms\Components\TextInput::make('nationality')
                                ->label('Nacionalidade')
                                ->maxLength(255),
                            Forms\Components\Select::make('marital_status')
                                ->options([
                                    'single' => 'Solterio(a)',
                                    'married' => 'Casado(a)',
                                    'separated' => 'Separado(a)',
                                    'divorced' => 'Diviorciado(a)',
                                    'widowed' => 'Viúvo(a)',
                                ])
                                ->label('Estado civil'),
                            Forms\Components\TextInput::make('profession')
                                ->label('Profissão')
                                ->maxLength(255),
                            Forms\Components\Textarea::make('note')
                                ->label('Observações')
                                ->rows(3),
                        ])
                        ->columns(2),

                    Step::make('Documentos')
                        ->schema([
                            Forms\Components\Group::make()
                                ->relationship('documents')
                                ->schema([
                                    Forms\Components\TextInput::make('cpf')
                                        ->label('CPF')
                                        ->mask('999.999.999-99')
                                        ->rule('cpf')
                                        ->required()
                                        ->dehydrateStateUsing(fn($state) => preg_replace('/\D/', '', $state)),
                                       


                                    Forms\Components\TextInput::make('rg')
                                        ->label('RG')
                                        ->maxlength(20),
                                    Forms\Components\TextInput::make('cnh')
                                        ->label('CNH')
                                        ->maxlength(20),
                                    Forms\Components\TextInput::make('pis')
                                        ->label('PIS')
                                        ->maxlength(20),
                                    Forms\Components\TextInput::make('ctps')
                                        ->label('CTPS')
                                        ->maxlength(20),
                                    Forms\Components\TextInput::make('rnm')
                                        ->label('RNM')
                                        ->maxlength(20),
                                    Forms\Components\Textarea::make('other_documents')
                                        ->label('Outros documentos')
                                        ->rows(3),
                                ])
                                ->columns(2),
                        ]),
                ])
                ->columnSpan('full'),
            ]);
    }

    public static function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->sortable(),
                Tables\Columns\TextColumn::make('name')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('date_of_birth')
                    ->date()
                    ->label('DOB'),
                Tables\Columns\TextColumn::make('gender')
                    ->label('Gender')
                    ->sortable(),
                Tables\Columns\TextColumn::make('documents.cpf')
                    ->label('CPF'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->label('Created'),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

     public static function getPages(): array
    {
        return [
            'index' => Pages\ListClients::route('/'),
            'create' => Pages\CreateClient::route('/create'),
            'view' => Pages\ViewClient::route('/{record}'),
            'edit' => Pages\EditClient::route('/{record}/edit'),
        ];
    }
}