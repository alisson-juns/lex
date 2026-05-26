<?php

namespace App\Livewire;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Promethys\Revive\Tables\RecycleBin as BaseRecycleBin;
use Filament\Tables\Actions\ForceDeleteAction;
use Filament\Tables\Actions\ForceDeleteBulkAction;
use Filament\Tables\Actions\RestoreAction;
use Filament\Tables\Actions\RestoreBulkAction;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Collection;

class CustomRecycleBin extends BaseRecycleBin
{
    protected static ?string $modelLabel = 'Lixeira';

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
            ->recordAction(null)
            ->columns([

                TextColumn::make('model_type')
                    ->label('Tipo')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $modelLabels[$state] ?? class_basename($state))
                    ->sortable()
                    ->searchable(),

                TextColumn::make('state')
                    ->label('Registro')
                    ->getStateUsing(fn ($record) => $this->getModelIdentifier(
                        $record->model_type,
                        $record->state ?? []
                    )),

                TextColumn::make('deleted_at')
                    ->label('Excluído em')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])

            ->actions([
                RestoreAction::make('restore')
                    ->button()
                    ->visible(true)
                    ->requiresConfirmation()
                    ->modalHeading('Restaurar registro')
                    ->modalDescription('Tem certeza que deseja restaurar este registro?')
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
                    ->modalHeading('Excluir permanentemente')
                    ->modalDescription('Esta ação não pode ser desfeita. Tem certeza?')
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
                    ->modalHeading('Restaurar os itens selecionados')
                    ->modalDescription('Tem certeza que deseja restaurar estes registros?')
                    ->action(function (Collection $models) {
                        foreach ($models as $model) {
                            try {
                                $model->model?->restore();
                            } catch (\Throwable) {
                            }
                        }
                    })->deselectRecordsAfterCompletion(),

                ForceDeleteBulkAction::make('force_delete_selected')->button()
                    ->modalHeading('Excluir permanentemente os itens selecionados')
                    ->modalDescription('Esta ação não pode ser desfeita. Tem certeza?')
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

    protected function getModelIdentifier(string $modelType, array $state): string
    {
        return match ($modelType) {
            'App\\Models\\Client'     => $state['name'] ?? '-',
            'App\\Models\\Enterprise' => $state['corporate_reason'] ?? '-',
            'App\\Models\\LegalCase'  => $state['case_number'] ?? $state['folder_number'] ?? '-',
            'App\\Models\\Hearing'    => $state['description'] ?? '-',
            'App\\Models\\Task'       => $state['title'] ?? '-',
            'App\\Models\\Lawyer'     => ($state['name'] ?? '-') . ' — OAB ' . ($state['oab'] ?? ''),
            'App\\Models\\Employee'   => $state['name'] ?? '-',
            default                   => "#{$state['id']}" ?? '-',
        };
    }


}
