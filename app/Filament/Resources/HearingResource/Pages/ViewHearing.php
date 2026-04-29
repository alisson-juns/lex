<?php

namespace App\Filament\Resources\HearingResource\Pages;

use App\Filament\Resources\HearingResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewHearing extends ViewRecord
{
    protected static string $resource = HearingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
