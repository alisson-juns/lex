<?php

namespace App\Filament\Resources\EnterpriseFeeAgreementResource\Pages;

use App\Filament\Resources\EnterpriseFeeAgreementResource;
use App\Services\EnterpriseFeeAgreementService;
use Filament\Actions\Action;
use Filament\Resources\Pages\EditRecord;

class EditEnterpriseFeeAgreement extends EditRecord
{
    protected static string $resource = EnterpriseFeeAgreementResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('gerarPdf')
                ->label('Gerar PDF')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('success')
                ->action(function (EnterpriseFeeAgreementService $service): void {
                    $this->save();
                    $url = route('enterprise-fee-agreement.pdf', $this->record->id);
                    $this->js("window.open('{$url}', '_blank')");
                }),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('edit', ['record' => $this->record->id]);
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return 'Contrato atualizado';
    }
}
