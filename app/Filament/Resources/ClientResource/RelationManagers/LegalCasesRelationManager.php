<?php

namespace App\Filament\Resources\ClientResource\RelationManagers;

use App\Enums\CaseStatus;
use App\Models\CourtName;
use App\Models\CourtNumber;
use App\Models\Forum;
use App\Models\Lawyer;
use App\Models\LegalCase;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class LegalCasesRelationManager extends RelationManager
{
    protected static string $relationship = 'legalCases';
    protected static ?string $title = 'Processos';

    public function form(Form $form): Form
    {
        return $form->schema([
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
                        ->options(CourtNumber::orderBy('number')->pluck('number', 'id'))
                        ->searchable(),

                    Forms\Components\Select::make('court_name_id')
                        ->label('Nome da Vara')
                        ->options(CourtName::orderBy('name')->pluck('name', 'id'))
                        ->searchable(),

                    Forms\Components\Select::make('forum_id')
                        ->label('Fórum')
                        ->options(Forum::orderBy('name')->pluck('name', 'id'))
                        ->searchable(),
                ]),

            Forms\Components\Grid::make(2)
                ->schema([
                    Forms\Components\Select::make('lawyer_id')
                        ->label('Advogado')
                        ->options(Lawyer::orderBy('name')->pluck('name', 'id'))
                        ->searchable(),

                    Forms\Components\TextInput::make('opponent_name')
                        ->label('Adverso')
                        ->maxLength(255),

                    Forms\Components\Select::make('status')
                        ->label('Status')
                        ->options(
                            collect(CaseStatus::cases())
                                ->mapWithKeys(fn ($case) => [$case->value => $case->label()])
                        )
                        ->default('open')
                        ->required(),

                    Forms\Components\Textarea::make('note')
                        ->label('Observações')
                        ->rows(3),
                ]),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('case_number')
            ->columns([
                Tables\Columns\TextColumn::make('case_number')
                    ->label('Nº Processo')
                    ->searchable(),

                Tables\Columns\TextColumn::make('location')
                    ->label('Localização')
                    ->getStateUsing(fn (LegalCase $record): string => collect([
                        $record->courtNumber?->number,
                        $record->courtName?->name,
                        $record->forum?->name,
                    ])->filter()->join(' - ')),

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
            ])
            ->filters([])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Novo Processo')
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['registered_by'] = auth()->id();
                        return $data;
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}