<?php

namespace App\Filament\User\Resources\EnterpriseResource\Pages;

use App\Filament\User\Resources\EnterpriseResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewEnterprise extends ViewRecord
{
    protected static string $resource = EnterpriseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
