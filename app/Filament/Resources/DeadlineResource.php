<?php

namespace App\Filament\Resources;

use App\Enums\DeadlineStatus;
use App\Enums\DeadlineType;
use App\Filament\Resources\DeadlineResource\Pages;
use App\Models\Deadline;
use App\Models\LegalCase;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;

class DeadlineResource extends Resource
{
    protected static ?string $model = Deadline::class;
    protected static ?string $navigationIcon   = 'heroicon-o-exclamation-triangle';
    protected static ?string $navigationLabel  = 'Prazos';
    protected static ?string $modelLabel       = 'Prazo';
    protected static ?string $pluralModelLabel = 'Prazos';
    protected static ?string $navigationGroup  = 'Agenda';
    protected static ?int    $navigationSort   = 2;

    public static function form(Form $form): Form
    {
        return $form->schema([

            Forms\Components\Section::make('Prazo')
                ->columns(2)
                ->schema([
                    Forms\Components\Select::make('legal_case_id')
                        ->label('Processo')
                        ->relationship('legalCase', 'case_number')
                        ->searchable()
                        ->preload()
                        ->required()
                        ->live()
                        ->afterStateUpdated(function ($state, Forms\Set $set) {
                            if (! $state) {
                                return;
                            }

                            // Sugere os advogados já vinculados ao processo
                            $lawyerIds = LegalCase::find($state)
                                ?->lawyers()
                                ->pluck('lawyers.id')
                                ->toArray();

                            $set('lawyers', $lawyerIds ?? []);
                        }),

                    Forms\Components\Select::make('deadline_type')
                        ->label('Tipo de prazo')
                        ->options(
                            collect(DeadlineType::cases())
                                ->mapWithKeys(fn ($case) => [$case->value => $case->label()])
                        )
                        ->searchable()
                        ->required(),

                    Forms\Components\DatePicker::make('fatal_date')
                        ->label('Prazo fatal')
                        ->required()
                        ->displayFormat('d/m/Y'),

                    Forms\Components\DatePicker::make('internal_date')
                        ->label('Prazo interno')
                        ->displayFormat('d/m/Y')
                        ->beforeOrEqual('fatal_date')
                        ->validationMessages([
                            'before_or_equal' => 'O prazo interno deve ser anterior ou igual ao prazo fatal.',
                        ]),

                    Forms\Components\Select::make('lawyers')
                        ->label('Advogado(s) responsável(is)')
                        ->relationship('lawyers', 'name')
                        ->multiple()
                        ->searchable()
                        ->preload(),

                    Forms\Components\Select::make('status')
                        ->label('Status')
                        ->options(
                            collect(DeadlineStatus::cases())
                                ->mapWithKeys(fn ($case) => [$case->value => $case->label()])
                        )
                        ->default(DeadlineStatus::Pending->value)
                        ->required(),

                    Forms\Components\Hidden::make('created_by')
                        ->default(fn () => Auth::id()),
                ]),

            Forms\Components\Section::make('Observações')
                ->schema([
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
                Tables\Columns\TextColumn::make('fatal_date')
                    ->label('Prazo fatal')
                    ->date('d/m/Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('internal_date')
                    ->label('Prazo interno')
                    ->date('d/m/Y')
                    ->sortable()
                    ->placeholder('—'),

                Tables\Columns\TextColumn::make('days_remaining')
                    ->label('Dias restantes')
                    ->badge()
                    ->getStateUsing(function (Deadline $record): string {
                        if ($record->status !== DeadlineStatus::Pending) {
                            return '—';
                        }

                        $days = (int) now()->startOfDay()
                            ->diffInDays($record->fatal_date->startOfDay(), false);

                        return $days < 0 ? 'Vencido' : (string) $days;
                    })
                    ->color(function (Deadline $record): string {
                        if ($record->status !== DeadlineStatus::Pending) {
                            return 'gray';
                        }

                        $days = (int) now()->startOfDay()
                            ->diffInDays($record->fatal_date->startOfDay(), false);

                        return match (true) {
                            $days < 0   => 'danger',
                            $days <= 3  => 'danger',
                            $days <= 7  => 'warning',
                            default     => 'success',
                        };
                    }),

                Tables\Columns\TextColumn::make('deadline_type')
                    ->label('Tipo')
                    ->formatStateUsing(fn (DeadlineType $state) => $state->label()),

                Tables\Columns\TextColumn::make('legalCase.case_number')
                    ->label('Processo')
                    ->searchable(),

                Tables\Columns\TextColumn::make('lawyers.name')
                    ->label('Advogado(s)')
                    ->badge()
                    ->separator(','),

                Tables\Columns\BadgeColumn::make('status')
                    ->label('Status')
                    ->formatStateUsing(fn (DeadlineStatus $state) => $state->label())
                    ->colors([
                        'warning' => DeadlineStatus::Pending->value,
                        'success' => DeadlineStatus::Completed->value,
                        'danger'  => DeadlineStatus::Missed->value,
                        'gray'    => DeadlineStatus::Cancelled->value,
                    ]),

                Tables\Columns\TextColumn::make('creator.name')
                    ->label('Criado por')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Criado em')
                    ->dateTime('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('fatal_date', 'asc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options(
                        collect(DeadlineStatus::cases())
                            ->mapWithKeys(fn ($case) => [$case->value => $case->label()])
                    ),

                Tables\Filters\SelectFilter::make('deadline_type')
                    ->label('Tipo')
                    ->options(
                        collect(DeadlineType::cases())
                            ->mapWithKeys(fn ($case) => [$case->value => $case->label()])
                    ),

                Tables\Filters\SelectFilter::make('lawyers')
                    ->label('Advogado')
                    ->relationship('lawyers', 'name'),

                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                // Ação rápida: cumprir sem abrir o formulário
                Tables\Actions\Action::make('complete')
                    ->label('Cumprir')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->hidden(fn (Deadline $record) => $record->status !== DeadlineStatus::Pending)
                    ->action(fn (Deadline $record) => $record->update([
                        'status' => DeadlineStatus::Completed->value,
                    ])),

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

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([SoftDeletingScope::class]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListDeadlines::route('/'),
            'create' => Pages\CreateDeadline::route('/create'),
            'view'   => Pages\ViewDeadline::route('/{record}'),
            'edit'   => Pages\EditDeadline::route('/{record}/edit'),
        ];
    }
}
