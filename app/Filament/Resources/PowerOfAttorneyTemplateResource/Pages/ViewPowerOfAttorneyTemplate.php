<?php

namespace App\Filament\Resources\PowerOfAttorneyTemplateResource\Pages;

use App\Filament\Resources\PowerOfAttorneyTemplateResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewPowerOfAttorneyTemplate extends ViewRecord
{
    protected static string $resource = PowerOfAttorneyTemplateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
