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
                                        ->rows(3)
                                        ->columnSpanFull(),
                                ])
                                ->columns(2),
                        ]),

                    Step::make('Representantes')
                        ->icon('heroicon-m-user-group')
                        ->description('Representantes legais da empresa')
                        ->schema([
                            Forms\Components\Repeater::make('enterprise_representatives')
                                ->relationship('enterprise_representatives')
                                ->schema([
                                    Forms\Components\Grid::make(2)
                                        ->schema([
                                            Forms\Components\TextInput::make('name')
                                                ->label('Nome completo')
                                                ->required()
                                                ->maxLength(255)
                                                ->columnSpan(2),
                                            Forms\Components\Select::make('gender')
                                                ->label('Gênero')
                                                ->options([
                                                    'male' => 'Masculino',
                                                    'female' => 'Feminino',
                                                    'other' => 'Outro',
                                                ]),
                                            Forms\Components\TextInput::make('position')
                                                ->label('Cargo/Função')
                                                ->maxLength(255),
                                            Forms\Components\Textarea::make('note')
                                                ->label('Observações')
                                                ->rows(2)
                                                ->columnSpan(2),
                                        ]),
                                ])
                                ->itemLabel(fn(array $state): ?string =>
                                    $state['name']
                                        ? "{$state['name']}" . ($state['position'] ? " — {$state['position']}" : '')
                                        : 'Novo representante'
                                )
                                ->addActionLabel('Adicionar representante')
                                ->deleteAction(fn($action) => $action->requiresConfirmation())
                                ->reorderable()
                                ->collapsible(),
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
                Tables\Columns\TextColumn::make('enterprise_documents.cnpj')
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
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\Action::make('create_case')
                    ->label('Inserir Processo')
                    ->icon('heroicon-o-scale')
                    ->color('gray')
                    ->modalHeading(fn (Enterprise $record) => "Novo processo — {$record->corporate_reason}")
                    ->modalWidth('3xl')
                    ->form([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('folder_number')
                                    ->label('Nº da Pasta')
                                    ->maxLength(255),

                                Forms\Components\TextInput::make('case_number')
                                    ->label('Nº do Processo')
                                    ->maxLength(255),
                            ]),

                        Forms\Components\Grid::make(3)
                            ->schema([
                                Forms\Components\Select::make('court_number_id')
                                    ->label('Nº da Vara')
                                    ->options(\App\Models\CourtNumber::orderBy('id')->pluck('number', 'id'))
                                    ->searchable(),

                                Forms\Components\Select::make('court_name_id')
                                    ->label('Nome da Vara')
                                    ->options(\App\Models\CourtName::orderBy('id')->pluck('name', 'id'))
                                    ->searchable(),

                                Forms\Components\Select::make('forum_id')
                                    ->label('Fórum')
                                    ->options(\App\Models\Forum::orderBy('id')->pluck('name', 'id'))
                                    ->searchable(),
                            ]),

                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\Select::make('lawyer_id')
                                    ->label('Advogado')
                                    ->options(\App\Models\Lawyer::orderBy('name')->pluck('name', 'id'))
                                    ->searchable(),

                                Forms\Components\TextInput::make('opponent_name')
                                    ->label('Adverso')
                                    ->maxLength(255),

                                Forms\Components\Select::make('status')
                                    ->label('Status')
                                    ->options(
                                        collect(\App\Enums\CaseStatus::cases())
                                            ->mapWithKeys(fn ($case) => [$case->value => $case->label()])
                                    )
                                    ->default('open')
                                    ->required(),

                                Forms\Components\Textarea::make('note')
                                    ->label('Observações')
                                    ->rows(3),
                            ]),
                    ])
                    ->action(function (Enterprise $record, array $data) {
                        $record->legalCases()->create([
                            ...$data,
                            'registered_by' => auth()->id(),
                        ]);
                    }),

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
            'index' => Pages\ListEnterprises::route('/'),
            'create' => Pages\CreateEnterprise::route('/create'),
            'view' => Pages\ViewEnterprise::route('/{record}'),
            'edit' => Pages\EditEnterprise::route('/{record}/edit'),
        ];
    }
}