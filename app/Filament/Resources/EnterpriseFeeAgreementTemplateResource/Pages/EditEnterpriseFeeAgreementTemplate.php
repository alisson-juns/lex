<?php

namespace App\Filament\Resources\EnterpriseFeeAgreementTemplateResource\Pages;

use App\Filament\Resources\EnterpriseFeeAgreementTemplateResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditEnterpriseFeeAgreementTemplate extends EditRecord
{
    protected static string $resource = EnterpriseFeeAgreementTemplateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
