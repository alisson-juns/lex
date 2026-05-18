<?php

namespace App\Filament\Resources\GratuityDeclarationResource\Pages;

use App\Filament\Resources\GratuityDeclarationResource;
use App\Services\GratuityDeclarationService;
use Filament\Actions\Action;
use Filament\Resources\Pages\EditRecord;

class EditGratuityDeclaration extends EditRecord
{
    protected static string $resource = GratuityDeclarationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('generatePdf')
                ->label('Gerar PDF')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('success')
                ->action(function (GratuityDeclarationService $service): void {
                    $this->save();
                    $url = route('gratuity-declaration.pdf', $this->record->id);
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
        return 'Declaração atualizada';
    }
}
