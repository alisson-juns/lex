<?php

namespace App\Filament\Resources\ClientResource\Pages;

use App\Models\PowerOfAttorney;
use App\Models\PowerOfAttorneyTemplate;
use App\Services\PowerOfAttorneyService;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Resources\Pages\ViewRecord;


class ViewClient extends ViewRecord
{
    protected static string $resource = \App\Filament\Resources\ClientResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),

            Action::make('gerarProcuracao')
                ->label('Gerar Procuração')
                ->icon('heroicon-o-document-arrow-down')
                ->color('success')
                ->form([
                    Forms\Components\Select::make('power_of_attorney_template_id')
                        ->label('Tipo de Procuração')
                        ->options(
                            PowerOfAttorneyTemplate::where('is_active', true)->pluck('name', 'id')
                        )
                        ->required(),

                    Forms\Components\Textarea::make('specific_text')
                        ->label('Fim específico do mandato')
                        ->placeholder('Ex: Ação de Indenização por Danos Morais e Materiais...')
                        ->rows(3)
                        ->required(),
                ])
                ->action(function (array $data, PowerOfAttorneyService $service): void {
                    $poa = PowerOfAttorney::create([
                        'client_id'                     => $this->record->id,
                        'power_of_attorney_template_id' => $data['power_of_attorney_template_id'],
                        'user_id'                       => auth()->id(),
                        'specific_text'                 => $data['specific_text'],
                    ]);

                    $poa->update(['rendered_body' => $service->render($poa)]);

                    $this->redirect(
                        \App\Filament\Resources\PowerOfAttorneyResource::getUrl('edit', ['record' => $poa->id])
                    );
                })
                ->modalHeading('Gerar Procuração')
                ->modalSubmitActionLabel('Continuar →'),
        ];
    }
}