<?php

namespace App\Filament\Resources\PowerOfAttorneyTemplateResource\Pages;

use App\Filament\Resources\PowerOfAttorneyTemplateResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPowerOfAttorneyTemplate extends EditRecord
{
    protected static string $resource = PowerOfAttorneyTemplateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
