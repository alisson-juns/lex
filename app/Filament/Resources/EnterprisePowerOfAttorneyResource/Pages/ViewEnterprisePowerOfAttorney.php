<?php

namespace App\Filament\Resources\EnterprisePowerOfAttorneyResource\Pages;

use App\Filament\Resources\EnterprisePowerOfAttorneyResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewEnterprisePowerOfAttorney extends ViewRecord
{
    protected static string $resource = EnterprisePowerOfAttorneyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
