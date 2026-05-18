<?php

namespace App\Filament\Resources\ClientResource\Pages;

use App\Models\FeeAgreement;
use App\Models\FeeAgreementTemplate;
use App\Models\PowerOfAttorney;
use App\Models\PowerOfAttorneyTemplate;
use App\Models\Lawyer;
use App\Models\GratuityDeclaration;
use App\Models\GratuityDeclarationTemplate;
use App\Services\GratuityDeclarationService;
use App\Services\FeeAgreementService;
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

                    $poa->lawyers()->sync($data['lawyer_ids']);

                    $poa->update(['rendered_body' => $service->render($poa)]);

                    $this->redirect(
                        \App\Filament\Resources\PowerOfAttorneyResource::getUrl('edit', ['record' => $poa->id])
                    );
                })
                ->modalHeading('Gerar Procuração')
                ->modalSubmitActionLabel('Continuar →'),

            Action::make('gerarContrato')
                ->label('Gerar Contrato')
                ->icon('heroicon-o-document-check')
                ->color('warning')
                ->form([
                    Forms\Components\Select::make('fee_agreement_template_id')
                        ->label('Tipo de Contrato')
                        ->options(
                            FeeAgreementTemplate::where('is_active', true)->pluck('name', 'id')
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
                        ->label('Tipo de Ação')
                        ->placeholder('Ex: Ação Trabalhista, Ação de Indenização por Danos Morais...')
                        ->rows(2)
                        ->required(),

                    Forms\Components\TextInput::make('fee_percentage')
                        ->label('Percentual de Honorários')
                        ->numeric()
                        ->minValue(1)
                        ->maxValue(100)
                        ->step(0.01)
                        ->suffix('%')
                        ->placeholder('Ex: 30')
                        ->required(),
                ])
                ->action(function (array $data, FeeAgreementService $service): void {
                    $agreement = FeeAgreement::create([
                        'client_id'                 => $this->record->id,
                        'fee_agreement_template_id' => $data['fee_agreement_template_id'],
                        'user_id'                   => auth()->id(),
                        'specific_text'             => $data['specific_text'],
                        'fee_percentage'            => $data['fee_percentage'],
                    ]);

                    $agreement->lawyers()->sync($data['lawyer_ids']);

                    $agreement->update(['rendered_body' => $service->render($agreement)]);

                    $this->redirect(
                        \App\Filament\Resources\FeeAgreementResource::getUrl('edit', ['record' => $agreement->id])
                    );
                })
                ->modalHeading('Gerar Contrato de Honorários')
                ->modalSubmitActionLabel('Continuar →'),

            Action::make('generateDeclaration')
                ->label('Declaração de Gratuidade')
                ->icon('heroicon-o-document-text')
                ->color('info')
                ->form([
                    Forms\Components\Select::make('gratuity_declaration_template_id')
                        ->label('Modelo')
                        ->options(
                            GratuityDeclarationTemplate::where('is_active', true)->pluck('name', 'id')
                        )
                        ->required(),
                ])
                ->action(function (array $data, GratuityDeclarationService $service): void {
                    $declaration = GratuityDeclaration::create([
                        'client_id'                        => $this->record->id,
                        'gratuity_declaration_template_id' => $data['gratuity_declaration_template_id'],
                        'user_id'                          => auth()->id(),
                    ]);

                    $declaration->update(['rendered_body' => $service->render($declaration)]);

                    $this->redirect(
                        \App\Filament\Resources\GratuityDeclarationResource::getUrl('edit', ['record' => $declaration->id])
                    );
                })
                ->modalHeading('Gerar Declaração de Gratuidade')
                ->modalSubmitActionLabel('Continuar →'),
                    ];
    }
}
