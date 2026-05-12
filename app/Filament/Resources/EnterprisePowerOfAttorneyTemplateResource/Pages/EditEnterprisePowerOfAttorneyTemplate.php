<?php

namespace App\Filament\Resources\EnterprisePowerOfAttorneyTemplateResource\Pages;

use App\Filament\Resources\EnterprisePowerOfAttorneyTemplateResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditEnterprisePowerOfAttorneyTemplate extends EditRecord
{
    protected static string $resource = EnterprisePowerOfAttorneyTemplateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
