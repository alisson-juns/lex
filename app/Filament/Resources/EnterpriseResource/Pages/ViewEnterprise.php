<?php

namespace App\Filament\Resources\EnterpriseResource\Pages;

use App\Filament\Resources\EnterpriseResource;
use App\Filament\Resources\EnterprisePowerOfAttorneyResource;
use App\Models\EnterprisePowerOfAttorney;
use App\Models\EnterprisePowerOfAttorneyTemplate;
use App\Models\Lawyer;
use App\Services\EnterprisePowerOfAttorneyService;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Resources\Pages\ViewRecord;

class ViewEnterprise extends ViewRecord
{
    protected static string $resource = EnterpriseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),

            Action::make('gerarProcuracao')
                ->label('Gerar Procuração')
                ->icon('heroicon-o-document-arrow-down')
                ->color('success')
                ->form([
                    Forms\Components\Select::make('enterprise_power_of_attorney_template_id')
                        ->label('Tipo de Procuração')
                        ->options(
                            EnterprisePowerOfAttorneyTemplate::where('is_active', true)->pluck('name', 'id')
                        )
                        ->required(),

                    Forms\Components\Select::make('enterprise_representative_id')
                        ->label('Representante Legal')
                        ->options(
                            $this->record->enterprise_representatives()
                                ->get()
                                ->mapWithKeys(fn ($r) => [
                                    $r->id => $r->name . ($r->position ? ' — ' . $r->position : ''),
                                ])
                        )
                        ->required(),

                    Forms\Components\Select::make('lawyer_ids')
                        ->label('Advogado(s)')
                        ->options(
                            Lawyer::where('active', true)
                                ->orderBy('name')
                                ->get()
                                ->mapWithKeys(fn ($l) => [
                                    $l->id => $l->name . ($l->oab ? ' — OAB ' . $l->oab . '/' . $l->oab_state : ''),
                                ])
                        )
                        ->multiple()
                        ->required(),

                    Forms\Components\Textarea::make('specific_text')
                        ->label('Fim específico do mandato')
                        ->rows(3)
                        ->required(),
                ])
                ->action(function (array $data, EnterprisePowerOfAttorneyService $service): void {
                    $poa = EnterprisePowerOfAttorney::create([
                        'enterprise_id'                          => $this->record->id,
                        'enterprise_power_of_attorney_template_id' => $data['enterprise_power_of_attorney_template_id'],
                        'enterprise_representative_id'           => $data['enterprise_representative_id'],
                        'user_id'                                => auth()->id(),
                        'specific_text'                          => $data['specific_text'],
                    ]);

                    $poa->lawyers()->sync($data['lawyer_ids']);
                    $poa->update(['rendered_body' => $service->render($poa)]);

                    $this->redirect(
                        EnterprisePowerOfAttorneyResource::getUrl('edit', ['record' => $poa->id])
                    );
                })
                ->modalHeading('Gerar Procuração')
                ->modalSubmitActionLabel('Continuar →'),
        ];
    }
}
