<?php

namespace App\Filament\Resources\EnterpriseResource\Pages;

use App\Filament\Resources\EnterpriseResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateEnterprise extends CreateRecord
{
    protected static string $resource = EnterpriseResource::class;

    protected function getCreateFormAction(): \Filament\Actions\Action
    {
        return parent::getCreateFormAction()
            ->extraAttributes(['formnovalidate' => true]);
    }

    protected function getCreateAnotherFormAction(): \Filament\Actions\Action
    {
        return parent::getCreateAnotherFormAction()
            ->extraAttributes(['formnovalidate' => true]);
    }
}
