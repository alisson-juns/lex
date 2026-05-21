<?php

namespace App\Livewire;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Promethys\Revive\Tables\RecycleBin as BaseRecycleBin;
use Filament\Infolists\Components\KeyValueEntry;
use Filament\Tables\Actions\ForceDeleteAction;
use Filament\Tables\Actions\ForceDeleteBulkAction;
use Filament\Tables\Actions\RestoreAction;
use Filament\Tables\Actions\RestoreBulkAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Collection;

class CustomRecycleBin extends BaseRecycleBin
{
    public function table(Table $table): Table
    {
        $modelLabels = [
            'App\\Models\\Client'     => 'Cliente',
            'App\\Models\\Enterprise' => 'Empresa',
            'App\\Models\\LegalCase'  => 'Processo',
            'App\\Models\\Hearing'    => 'Audiência',
            'App\\Models\\Task'       => 'Tarefa',
            'App\\Models\\Lawyer'     => 'Advogado',
            'App\\Models\\Employee'   => 'Funcionário',
        ];

        return parent::table($table)
            ->columns([

                TextColumn::make('model_type')
                    ->label('Tipo')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $modelLabels[$state] ?? class_basename($state))
                    ->sortable()
                    ->searchable(),

                TextColumn::make('deleted_at')
                    ->label('Excluído em')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])

            ->actions([
                ViewAction::make('view_details')
            ->button()
            ->modalHeading('Detalhes do registro')
            ->infolist(function ($record) {
                $labels = $this->getFieldLabels($record->model_type);

                return [
                    KeyValueEntry::make('state')
                        ->label('')
                        ->state(function () use ($record, $labels) {
                            $translated = [];
                            foreach ($record->state ?? [] as $key => $value) {
                                $label = $labels[$key] ?? $key;
                                if (empty($label)) {
                                    continue;
                                } // oculta campos sem label (ex: recycle_bin_item)
                                $translated[$label] = $value;
                            }
                            return $translated;
                        }),
                ];
            }),
                RestoreAction::make('restore')
                    ->button()
                    ->visible(true)
                    ->requiresConfirmation()
                    ->action(function ($record) {
                        try {
                            $record->model?->restore();
                            Notification::make()->success()
                                ->title('Registro restaurado com sucesso')->send();
                        } catch (\Throwable) {
                            Notification::make()->danger()
                                ->title('Erro ao restaurar registro')->send();
                        }
                    }),

                ForceDeleteAction::make('force_delete')
                    ->button()
                    ->visible(true)
                    ->using(function ($record) {
                        try {
                            $record->model?->forceDelete();
                            $record->delete();
                            return true;
                        } catch (\Throwable) {
                            return false;
                        }
                    })
                    ->successNotification(Notification::make()->success()->title('Excluído permanentemente'))
                    ->failureNotification(Notification::make()->danger()->title('Erro ao excluir')),
            ])
            ->bulkActions([
                RestoreBulkAction::make('restore_selected')->button()
                    ->action(function (Collection $models) {
                        foreach ($models as $model) {
                            try {
                                $model->model?->restore();
                            } catch (\Throwable) {
                            }
                        }
                    })->deselectRecordsAfterCompletion(),

                ForceDeleteBulkAction::make('force_delete_selected')->button()
                    ->action(function (Collection $models) {
                        foreach ($models as $model) {
                            try {
                                $model->model?->forceDelete();
                                $model->delete();
                            } catch (\Throwable) {
                            }
                        }
                    })->deselectRecordsAfterCompletion(),
            ]);
    }

    protected function getFieldLabels(string $modelType): array
    {
        $common = [
            'id'               => 'ID',
            'created_at'       => 'Criado em',
            'updated_at'       => 'Atualizado em',
            'deleted_at'       => 'Excluído em',
            'note'             => 'Observação',
            'status'           => 'Status',
            'google_event_id'  => 'Google Event ID',
            'recycle_bin_item' => '',
        ];

        $specific = match ($modelType) {
            'App\\Models\\Task' => [
                'legal_case_id' => 'Processo',
                'created_by'    => 'Criado por',
                'title'         => 'Título',
                'description'   => 'Descrição',
                'due_date'      => 'Data',
                'due_time'      => 'Hora',
            ],
            'App\\Models\\Hearing' => [
                'legal_case_id' => 'Processo',
                'lawyer_id'     => 'Advogado',
                'description'   => 'Descrição',
                'date'          => 'Data',
                'time'          => 'Hora',
                'location'      => 'Local',
            ],
            'App\\Models\\Client' => [
                'name'           => 'Nome',
                'date_of_birth'  => 'Nascimento',
                'gender'         => 'Gênero',
                'marital_status' => 'Estado civil',
                'profession'     => 'Profissão',
                'nationality'    => 'Nacionalidade',
                'father'         => 'Pai',
                'mother'         => 'Mãe',
                'place_of_birth' => 'Naturalidade',
            ],
            'App\\Models\\Enterprise' => [
                'corporate_reason' => 'Razão social',
                'trade_name'       => 'Nome fantasia',
            ],
            'App\\Models\\LegalCase' => [
                'folder_number'   => 'Nº pasta',
                'case_number'     => 'Nº processo',
                'client_id'       => 'Cliente',
                'enterprise_id'   => 'Empresa',
                'opponent_name'   => 'Adverso',
                'registered_by'   => 'Cadastrado por',
                'forum_id'        => 'Fórum',
                'court_name_id'   => 'Vara',
                'court_number_id' => 'Nº vara',
            ],
            'App\\Models\\Lawyer' => [
                'user_id'        => 'Usuário',
                'name'           => 'Nome',
                'oab'            => 'OAB',
                'oab_state'      => 'Estado OAB',
                'oab_subsection' => 'Subseção OAB',
                'oab_date'       => 'Data OAB',
                'date_of_birth'  => 'Nascimento',
                'gender'         => 'Gênero',
                'marital_status' => 'Estado civil',
                'nationality'    => 'Nacionalidade',
                'active'         => 'Ativo',
            ],
            'App\\Models\\Employee' => [
                'name'           => 'Nome',
                'date_of_birth'  => 'Nascimento',
                'gender'         => 'Gênero',
                'occupation_id'  => 'Cargo',
                'marital_status' => 'Estado civil',
                'nationality'    => 'Nacionalidade',
                'active'         => 'Ativo',
                'father'         => 'Pai',
                'mother'         => 'Mãe',
                'place_of_birth' => 'Naturalidade',
            ],
            default => [],
        };

        return array_merge($common, $specific);
    }

}
