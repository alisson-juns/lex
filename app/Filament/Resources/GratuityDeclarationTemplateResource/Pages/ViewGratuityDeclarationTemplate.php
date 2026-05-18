<?php

namespace App\Filament\Resources\GratuityDeclarationTemplateResource\Pages;

use App\Filament\Resources\GratuityDeclarationTemplateResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewGratuityDeclarationTemplate extends ViewRecord
{
    protected static string $resource = GratuityDeclarationTemplateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
