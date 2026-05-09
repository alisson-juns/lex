<?php

namespace App\Filament\Resources\PowerOfAttorneyResource\Pages;

use App\Filament\Resources\PowerOfAttorneyResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewPowerOfAttorney extends ViewRecord
{
    protected static string $resource = PowerOfAttorneyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
