<?php

namespace App\Filament\Resources;

use App\Enums\TaskStatus;
use App\Filament\Resources\TaskResource\Pages;
use App\Models\Lawyer;
use App\Models\Task;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;

class TaskResource extends Resource
{
    protected static ?string $model = Task::class;
    protected static ?string $navigationIcon  = 'heroicon-o-clipboard-document-list';
    protected static ?string $navigationLabel = 'Tarefas';
    protected static ?string $modelLabel      = 'Tarefa';
    protected static ?string $pluralModelLabel = 'Tarefas';
    protected static ?string $navigationGroup = 'Agenda';
    protected static ?int    $navigationSort  = 1;

    public static function form(Form $form): Form
    {
        return $form->schema([

            Forms\Components\Section::make('Tarefa')
                ->columns(2)
                ->schema([
                    Forms\Components\TextInput::make('title')
                        ->label('Título')
                        ->required()
                        ->maxLength(255)
                        ->columnSpanFull(),

                    Forms\Components\Textarea::make('description')
                        ->label('Descrição')
                        ->rows(3)
                        ->columnSpanFull(),

                    Forms\Components\DatePicker::make('due_date')
                        ->label('Data')
                        ->required()
                        ->displayFormat('d/m/Y'),

                    Forms\Components\TimePicker::make('due_time')
                        ->label('Hora')
                        ->seconds(false),

                    Forms\Components\Select::make('status')
                        ->label('Status')
                        ->options(
                            collect(TaskStatus::cases())
                                ->mapWithKeys(fn ($case) => [$case->value => $case->label()])
                        )
                        ->default(TaskStatus::Scheduled->value)
                        ->required(),

                    Forms\Components\Select::make('lawyers')
                        ->label('Advogado(s) responsável(is)')
                        ->relationship('lawyers', 'name')
                        ->multiple()
                        ->searchable()
                        ->preload(),
                ]),

            Forms\Components\Section::make('Vinculações')
                ->columns(2)
                ->schema([
                    Forms\Components\Select::make('legal_case_id')
                        ->label('Processo vinculado')
                        ->relationship('legalCase', 'case_number')
                        ->searchable()
                        ->preload()
                        ->nullable(),

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
                Tables\Columns\TextColumn::make('due_date')
                    ->label('Data')
                    ->date('d/m/Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('due_time')
                    ->label('Hora'),

                Tables\Columns\TextColumn::make('title')
                    ->label('Título')
                    ->searchable()
                    ->limit(40),

                Tables\Columns\TextColumn::make('lawyers.name')
                    ->label('Advogado(s)')
                    ->badge()
                    ->separator(','),

                Tables\Columns\TextColumn::make('legalCase.case_number')
                    ->label('Processo')
                    ->searchable()
                    ->toggleable(),

                Tables\Columns\BadgeColumn::make('status')
                    ->label('Status')
                    ->formatStateUsing(fn (TaskStatus $state) => $state->label())
                    ->colors([
                        'warning' => TaskStatus::Scheduled->value,
                        'info'    => TaskStatus::InProgress->value,
                        'success' => TaskStatus::Completed->value,
                        'danger'  => TaskStatus::Cancelled->value,
                        'gray'    => TaskStatus::Rescheduled->value,
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
            ->defaultSort('due_date', 'asc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options(
                        collect(TaskStatus::cases())
                            ->mapWithKeys(fn ($case) => [$case->value => $case->label()])
                    ),

                Tables\Filters\SelectFilter::make('lawyers')
                    ->label('Advogado')
                    ->relationship('lawyers', 'name'),

                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                // Ação rápida: concluir sem abrir o formulário
                Tables\Actions\Action::make('complete')
                    ->label('Concluir')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->hidden(fn (Task $record) => $record->status === TaskStatus::Completed)
                    ->action(fn (Task $record) => $record->update(['status' => TaskStatus::Completed->value])),

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
            'index'  => Pages\ListTasks::route('/'),
            'create' => Pages\CreateTask::route('/create'),
            'view'   => Pages\ViewTask::route('/{record}'),
            'edit'   => Pages\EditTask::route('/{record}/edit'),
        ];
    }
}
