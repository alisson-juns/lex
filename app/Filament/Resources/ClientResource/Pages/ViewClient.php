<?php

namespace App\Filament\Resources\ClientResource\Pages;

use App\Models\CourtName;
use App\Models\CourtNumber;
use App\Models\Forum;
use App\Models\LegalCase;
use App\Enums\CaseStatus;
use App\Models\FeeAgreement;
use App\Models\FeeAgreementTemplate;
use App\Models\GratuityDeclaration;
use App\Models\GratuityDeclarationTemplate;
use App\Models\Lawyer;
use App\Models\PowerOfAttorney;
use App\Models\PowerOfAttorneyTemplate;
use App\Services\FeeAgreementService;
use App\Services\GratuityDeclarationService;
use App\Services\PowerOfAttorneyService;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Forms;
use Filament\Resources\Pages\ViewRecord;

class ViewClient extends ViewRecord
{
    protected static string $resource = \App\Filament\Resources\ClientResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),

            ActionGroup::make([
                Action::make('gerarProcuracao')
                    ->label('Gerar Procuração')
                    ->icon('heroicon-o-document-arrow-down')
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
                            'is_draft'                      => true,
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
                            'is_draft'                  => true,
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
                            'is_draft'                         => true,
                        ]);

                        $declaration->update(['rendered_body' => $service->render($declaration)]);

                        $this->redirect(
                            \App\Filament\Resources\GratuityDeclarationResource::getUrl('edit', ['record' => $declaration->id])
                        );
                    })
                    ->modalHeading('Gerar Declaração de Gratuidade')
                    ->modalSubmitActionLabel('Continuar →'),
            ])
            ->label('Gerar Documentos')
            ->icon('heroicon-o-document-text')
            ->color('success')
            ->button(),

            Action::make('create_case')
                ->label('Inserir Processo')
                ->icon('heroicon-o-scale')
                ->color('gray')
                ->modalHeading(fn () => "Novo processo — {$this->record->name}")
                ->modalWidth('3xl')
                ->form([
                    Forms\Components\Grid::make(2)
                        ->schema([
                            Forms\Components\TextInput::make('folder_number')
                                ->label('Nº da Pasta')
                                ->maxLength(255),
                            Forms\Components\TextInput::make('case_number')
                                ->label('Nº do Processo')
                                ->placeholder('Ex: 0000000-00.0000.0.00.0000')
                                ->mask("0000000-00.0000.0.00.0000")
                                ->maxLength(255),
                        ]),
                    Forms\Components\Grid::make(3)
                        ->schema([
                            Forms\Components\Select::make('court_number_id')
                                ->label('Nº da Vara')
                                ->options(CourtNumber::orderBy('id')->pluck('number', 'id'))
                                ->searchable(),
                            Forms\Components\Select::make('court_name_id')
                                ->label('Nome da Vara')
                                ->options(CourtName::orderBy('id')->pluck('name', 'id'))
                                ->searchable(),
                            Forms\Components\Select::make('forum_id')
                                ->label('Fórum')
                                ->options(Forum::orderBy('id')->pluck('name', 'id'))
                                ->searchable(),
                        ]),
                    Forms\Components\Grid::make(2)
                        ->schema([
                            Forms\Components\Select::make('lawyers')
                                ->label('Advogado(s)')
                                ->options(Lawyer::orderBy('name')->pluck('name', 'id'))
                                ->multiple()
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
            ])
                ->action(function (array $data, Action $action): void {
                    $lawyers = $data['lawyers'] ?? [];
                    unset($data['lawyers']);

                    $legalCase = $this->record->legalCases()->create([
                        ...$data,
                        'registered_by' => auth()->id(),
                    ]);

                    if (!empty($lawyers)) {
                        $legalCase->lawyers()->attach($lawyers);
                    }

                    $action->success();
                })
                ->successNotificationTitle('Processo inserido com sucesso'),

            Action::make('fichaCliente')
                ->label('Imprimir Ficha')
                ->icon('heroicon-o-printer')
                ->color('gray')
                ->action(function () {
                    $url = route('client.ficha', $this->record->id);
                    $this->js("window.open('{$url}', '_blank')");
                }),
        ];
    }

}
