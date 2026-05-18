<?php

namespace App\Filament\Resources\EnterpriseFeeAgreementResource\Pages;

use App\Filament\Resources\EnterpriseFeeAgreementResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListEnterpriseFeeAgreements extends ListRecords
{
    protected static string $resource = EnterpriseFeeAgreementResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
