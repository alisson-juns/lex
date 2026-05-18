<?php

namespace App\Filament\Resources\EnterpriseFeeAgreementTemplateResource\Pages;

use App\Filament\Resources\EnterpriseFeeAgreementTemplateResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewEnterpriseFeeAgreementTemplate extends ViewRecord
{
    protected static string $resource = EnterpriseFeeAgreementTemplateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
