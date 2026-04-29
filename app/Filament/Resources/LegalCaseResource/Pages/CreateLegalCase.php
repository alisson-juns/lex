<?php

namespace App\Filament\Resources\LegalCaseResource\Pages;

use App\Filament\Resources\LegalCaseResource;
use Filament\Resources\Pages\CreateRecord;

class CreateLegalCase extends CreateRecord
{
    protected static string $resource = LegalCaseResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['registered_by'] = auth()->id();

        return $data;
    }
}