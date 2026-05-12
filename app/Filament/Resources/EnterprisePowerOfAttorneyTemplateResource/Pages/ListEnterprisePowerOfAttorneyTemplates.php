<?php

namespace App\Filament\Resources\EnterprisePowerOfAttorneyTemplateResource\Pages;

use App\Filament\Resources\EnterprisePowerOfAttorneyTemplateResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListEnterprisePowerOfAttorneyTemplates extends ListRecords
{
    protected static string $resource = EnterprisePowerOfAttorneyTemplateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
