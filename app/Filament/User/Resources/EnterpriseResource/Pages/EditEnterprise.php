<?php

namespace App\Filament\User\Resources\EnterpriseResource\Pages;

use App\Filament\User\Resources\EnterpriseResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditEnterprise extends EditRecord
{
    protected static string $resource = EnterpriseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
