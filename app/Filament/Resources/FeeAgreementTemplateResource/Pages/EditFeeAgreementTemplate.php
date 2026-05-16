<?php

namespace App\Filament\Resources\FeeAgreementTemplateResource\Pages;

use App\Filament\Resources\FeeAgreementTemplateResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditFeeAgreementTemplate extends EditRecord
{
    protected static string $resource = FeeAgreementTemplateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
