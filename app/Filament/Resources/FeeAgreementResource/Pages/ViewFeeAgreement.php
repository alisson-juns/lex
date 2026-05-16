<?php

namespace App\Filament\Resources\FeeAgreementResource\Pages;

use App\Filament\Resources\FeeAgreementResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewFeeAgreement extends ViewRecord
{
    protected static string $resource = FeeAgreementResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
