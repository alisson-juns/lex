<?php

namespace App\Filament\Resources\PowerOfAttorneyResource\Pages;

use App\Filament\Resources\PowerOfAttorneyResource;
use App\Services\PowerOfAttorneyService;
use Filament\Actions\Action;
use Filament\Resources\Pages\EditRecord;

class EditPowerOfAttorney extends EditRecord
{
    protected static string $resource = PowerOfAttorneyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('gerarPdf')
                ->label('Gerar PDF')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('success')
                ->action(function (PowerOfAttorneyService $service): void {
                    // Salva edições antes de gerar
                    $this->save();

                    $url = route('power-of-attorney.pdf', $this->record->id);
                    $this->js("window.open('{$url}', '_blank')");
                }),
        ];
    }

    // Marca a procuração como definitiva sempre que for salva
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
        return 'Procuração atualizada';
    }
}
