<?php

namespace App\Filament\Resources\FeeAgreementResource\Pages;

use App\Filament\Resources\FeeAgreementResource;
use App\Services\FeeAgreementService;
use Filament\Actions\Action;
use Filament\Resources\Pages\EditRecord;

class EditFeeAgreement extends EditRecord
{
    protected static string $resource = FeeAgreementResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('gerarPdf')
                ->label('Gerar PDF')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('success')
                ->action(function (FeeAgreementService $service): void {
                    $this->save();

                    $url = route('fee-agreement.pdf', $this->record->id);
                    $this->js("window.open('{$url}', '_blank')");
                }),
        ];
    }

    // Marca o contrato como definitivo sempre que for salvo
    protected function afterSave(): void
    {
        if ($this->record->is_draft) {
            $this->record->update(['is_draft' => false]);
        }
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('edit', ['record' => $this->record->id]);
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return 'Contrato salvo';
    }
}
