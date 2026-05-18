<?php

namespace App\Filament\Resources\GratuityDeclarationResource\Pages;

use App\Filament\Resources\GratuityDeclarationResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewGratuityDeclaration extends ViewRecord
{
    protected static string $resource = GratuityDeclarationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
