<?php

namespace App\Filament\Resources\EnterprisePowerOfAttorneyResource\Pages;

use App\Filament\Resources\EnterprisePowerOfAttorneyResource;
use App\Services\EnterprisePowerOfAttorneyService;
use Filament\Actions\Action;
use Filament\Resources\Pages\EditRecord;

class EditEnterprisePowerOfAttorney extends EditRecord
{
    protected static string $resource = EnterprisePowerOfAttorneyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('gerarPdf')
                ->label('Gerar PDF')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('success')
                ->action(function (): void {
                    $this->save();
                    $url = route('enterprise-power-of-attorney.pdf', $this->record->id);
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
        return 'Procuração atualizada';
    }
}
