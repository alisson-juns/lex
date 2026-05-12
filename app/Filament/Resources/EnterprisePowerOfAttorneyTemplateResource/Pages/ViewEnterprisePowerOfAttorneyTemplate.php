<?php

namespace App\Filament\Resources\EnterprisePowerOfAttorneyTemplateResource\Pages;

use App\Filament\Resources\EnterprisePowerOfAttorneyTemplateResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewEnterprisePowerOfAttorneyTemplate extends ViewRecord
{
    protected static string $resource = EnterprisePowerOfAttorneyTemplateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
