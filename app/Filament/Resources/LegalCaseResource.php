<?php

namespace App\Filament\Resources;

use App\Enums\CaseStatus;
use App\Filament\Resources\LegalCaseResource\Pages;
use App\Models\CourtName;
use App\Models\CourtNumber;
use App\Models\Forum;
use App\Models\Lawyer;
use App\Models\LegalCase;
use App\Enums\HearingStatus;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class LegalCaseResource extends Resource
{
    protected static ?string $model = LegalCase::class;
    protected static ?string $navigationIcon = 'heroicon-o-scale';
    protected static ?string $navigationLabel = 'Processos';
    protected static ?string $modelLabel = 'Processo';
    protected static ?string $navigationGroup = 'Controle de Processos';
    protected static ?int $navigationSort = 1;
    protected static ?string $pluralModelLabel = 'Processos';

    public static function form(Form $form): Form
    {
        return $form->schema([

            Forms\Components\Section::make('Identificação')
                ->columns(2)
                ->schema([
                    Forms\Components\TextInput::make('folder_number')
                        ->label('Nº da Pasta')
                        ->maxLength(255),

                    Forms\Components\TextInput::make('case_number')
                        ->label('Nº do Processo')
                        ->maxLength(255),
                ]),

            Forms\Components\Section::make('Localização')
                ->columns(3)
                ->schema([
                    Forms\Components\Select::make('court_number_id')
                        ->label('Número da Vara')
                        ->options(CourtNumber::orderBy('id')->pluck('number', 'id'))
                        ->searchable()
                        ->preload(),

                    Forms\Components\Select::make('court_name_id')
                        ->label('Nome da Vara')
                        ->options(CourtName::orderBy('id')->pluck('name', 'id'))
                        ->searchable()
                        ->preload(),

                    Forms\Components\Select::make('forum_id')
                        ->label('Fórum')
                        ->options(Forum::orderBy('name')->pluck('name', 'id'))
                        ->searchable()
                        ->preload(),
                ]),

            Forms\Components\Section::make('Partes')
                ->columns(2)
                ->schema([
                    Forms\Components\Radio::make('party_type')
                        ->label('Tipo de parte')
                        ->options([
                            'client'     => 'Pessoa Física',
                            'enterprise' => 'Pessoa Jurídica',
                        ])
                        ->default('client')
                        ->live()
                        ->dehydrated(false)
                        ->afterStateHydrated(function ($component, $record) {
                            if ($record?->enterprise_id) {
                                $component->state('enterprise');
                            } else {
                                $component->state('client');
                            }
                        })
                        ->afterStateUpdated(function (Forms\Set $set) {
                            $set('client_id', null);
                            $set('enterprise_id', null);
                        })
                        ->columnSpanFull(),

                    Forms\Components\Select::make('client_id')
                        ->label('Cliente')
                        ->relationship('client', 'name')
                        ->searchable()
                        ->preload()
                        ->visible(fn (Forms\Get $get) => $get('party_type') === 'client')
                        ->required(fn (Forms\Get $get) => $get('party_type') === 'client')
                        ->columnSpanFull(),

                    Forms\Components\Select::make('enterprise_id')
                        ->label('Empresa')
                        ->relationship('enterprise', 'corporate_reason')
                        ->searchable()
                        ->preload()
                        ->visible(fn (Forms\Get $get) => $get('party_type') === 'enterprise')
                        ->required(fn (Forms\Get $get) => $get('party_type') === 'enterprise')
                        ->columnSpanFull(),

                    Forms\Components\TextInput::make('opponent_name')
                        ->label('Adverso')
                        ->maxLength(255)
                        ->columnSpanFull(),

                    // Múltiplos advogados via pivot
                    Forms\Components\Select::make('lawyers')
                        ->label('Advogado(s)')
                        ->relationship('lawyers', 'name')
                        ->multiple()
                        ->searchable()
                        ->preload()
                        ->columnSpanFull(),
                ]),

            Forms\Components\Section::make('Situação')
                ->columns(2)
                ->schema([
                    Forms\Components\Select::make('status')
                        ->label('Status')
                        ->options(
                            collect(CaseStatus::cases())
                                ->mapWithKeys(fn ($case) => [$case->value => $case->label()])
                        )
                        ->default(CaseStatus::Open->value)
                        ->required(),

                    Forms\Components\Textarea::make('note')
                        ->label('Observações')
                        ->columnSpanFull()
                        ->rows(3),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('case_number')
                    ->label('Nº Processo')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('folder_number')
                    ->label('Nº Pasta')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('party')
                    ->label('Parte')
                    ->getStateUsing(fn (LegalCase $record): string =>
                        $record->client?->name ?? $record->enterprise?->corporate_reason ?? '—'
                    )
                    ->searchable(query: function (Builder $query, string $search) {
                        $query->whereHas('client', fn ($q) => $q->where('name', 'like', "%{$search}%"))
                              ->orWhereHas('enterprise', fn ($q) => $q->where('corporate_reason', 'like', "%{$search}%"));
                    }),

                Tables\Columns\TextColumn::make('lawyers.name')
                    ->label('Advogado(s)')
                    ->listWithLineBreaks()
                    ->limitList(2),

                Tables\Columns\TextColumn::make('location')
                    ->label('Localização')
                    ->getStateUsing(fn (LegalCase $record): string => collect([
                        $record->courtNumber?->number,
                        $record->courtName?->name,
                        $record->forum?->name,
                    ])->filter()->join(' ')),

                Tables\Columns\BadgeColumn::make('status')
                    ->label('Status')
                    ->formatStateUsing(fn (CaseStatus $state) => $state->label())
                    ->colors([
                        'success'   => CaseStatus::Open->value,
                        'primary'   => CaseStatus::InProgress->value,
                        'warning'   => CaseStatus::Suspended->value,
                        'danger'    => fn ($state) => in_array($state, [
                            CaseStatus::Closed->value,
                            CaseStatus::Cancelled->value,
                        ]),
                        'secondary' => CaseStatus::Archived->value,
                    ]),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Criado em')
                    ->dateTime('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options(
                        collect(CaseStatus::cases())
                            ->mapWithKeys(fn ($case) => [$case->value => $case->label()])
                    ),

                Tables\Filters\SelectFilter::make('forum_id')
                    ->label('Fórum')
                    ->options(Forum::orderBy('name')->pluck('name', 'id')),

                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([

                Tables\Actions\Action::make('create_hearing')
                    ->label('Inserir Audiência')
                    ->icon('heroicon-o-calendar-days')
                    ->color('gray')
                    ->modalHeading(fn (LegalCase $record) => "Nova audiência — {$record->case_number}")
                    ->modalWidth('2xl')
                    ->form([
                Forms\Components\Grid::make(2)
                ->schema([
                Forms\Components\TextInput::make('description')
                    ->label('Descrição')
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull(),

                Forms\Components\DatePicker::make('date')
                    ->label('Data')
                    ->required()
                    ->displayFormat('d/m/Y'),

                Forms\Components\TimePicker::make('time')
                    ->label('Hora')
                    ->required()
                    ->seconds(false),

                Forms\Components\TextInput::make('location')
                    ->label('Local')
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull(),

                Forms\Components\Select::make('lawyer_id')
                    ->label('Advogado responsável')
                    ->options(
                        fn (LegalCase $record) =>
                            $record->lawyers()->orderBy('name')->pluck('name', 'lawyers.id')
                    )
                    ->searchable(),

                Forms\Components\Select::make('status')
                    ->label('Status')
                    ->options(
                        collect(HearingStatus::cases())
                            ->mapWithKeys(fn ($case) => [$case->value => $case->label()])
                    )
                    ->default(HearingStatus::Scheduled->value)
                    ->required(),

                Forms\Components\Textarea::make('note')
                    ->label('Observações')
                    ->rows(3)
                    ->columnSpanFull(),
            ]),
                    ])
                    ->action(function (LegalCase $record, array $data) {
                        $record->hearings()->create($data);
                    })
                    ->successNotificationTitle('Audiência inserida com sucesso'),

                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListLegalCases::route('/'),
            'create' => Pages\CreateLegalCase::route('/create'),
            'view'   => Pages\ViewLegalCase::route('/{record}'),
            'edit'   => Pages\EditLegalCase::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([SoftDeletingScope::class]);
    }
}