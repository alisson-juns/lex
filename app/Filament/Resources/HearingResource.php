<?php

namespace App\Filament\Resources;

use App\Enums\HearingStatus;
use App\Filament\Resources\HearingResource\Pages;
use App\Models\Hearing;
use App\Models\Lawyer;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class HearingResource extends Resource
{
    protected static ?string $model = Hearing::class;
    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';
    protected static ?string $navigationLabel = 'Audiências';
    protected static ?string $modelLabel = 'Audiência';
    protected static ?string $navigationGroup = 'Controle de Processos';
    protected static ?int $navigationSort = 2;
    protected static ?string $pluralModelLabel = 'Audiências';

    public static function form(Form $form): Form
    {
        return $form->schema([

            Forms\Components\Section::make('Processo')
                ->columns(2)
                ->schema([
                    Forms\Components\Select::make('legal_case_id')
                        ->label('Processo')
                        ->relationship('legalCase', 'case_number')
                        ->searchable()
                        ->preload()
                        ->required(),

                    Forms\Components\Select::make('lawyer_id')
                        ->label('Advogado')
                        ->options(Lawyer::orderBy('name')->pluck('name', 'id'))
                        ->searchable()
                        ->preload(),
                ]),

            Forms\Components\Section::make('Audiência')
                ->columns(2)
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
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('date')
                    ->label('Data')
                    ->date('d/m/Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('time')
                    ->label('Hora'),

                Tables\Columns\TextColumn::make('legalCase.case_number')
                    ->label('Processo')
                    ->searchable(),

                Tables\Columns\TextColumn::make('description')
                    ->label('Descrição')
                    ->searchable(),

                Tables\Columns\TextColumn::make('location')
                    ->label('Local')
                    ->searchable(),

                Tables\Columns\TextColumn::make('lawyer.name')
                    ->label('Advogado')
                    ->searchable(),

                Tables\Columns\BadgeColumn::make('status')
                    ->label('Status')
                    ->formatStateUsing(fn (HearingStatus $state) => $state->label())
                    ->colors([
                        'success' => HearingStatus::Scheduled->value,
                        'primary' => HearingStatus::Completed->value,
                        'danger'  => HearingStatus::Cancelled->value,
                        'warning' => HearingStatus::Postponed->value,
                        'secondary' => HearingStatus::Suspended->value,
                    ]),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Criado em')
                    ->dateTime('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('date', 'asc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options(
                        collect(HearingStatus::cases())
                            ->mapWithKeys(fn ($case) => [$case->value => $case->label()])
                    ),

                Tables\Filters\SelectFilter::make('lawyer_id')
                    ->label('Advogado')
                    ->options(Lawyer::orderBy('name')->pluck('name', 'id')),

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
            'index'  => Pages\ListHearings::route('/'),
            'create' => Pages\CreateHearing::route('/create'),
            'view'   => Pages\ViewHearing::route('/{record}'),
            'edit'   => Pages\EditHearing::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([SoftDeletingScope::class]);
    }
}