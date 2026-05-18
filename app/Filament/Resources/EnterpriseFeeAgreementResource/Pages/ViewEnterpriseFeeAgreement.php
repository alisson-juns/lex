<?php

namespace App\Filament\Resources\EnterpriseFeeAgreementResource\Pages;

use App\Filament\Resources\EnterpriseFeeAgreementResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewEnterpriseFeeAgreement extends ViewRecord
{
    protected static string $resource = EnterpriseFeeAgreementResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
