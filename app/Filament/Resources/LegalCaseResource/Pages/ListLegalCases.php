<?php

namespace App\Filament\Resources\LegalCaseResource\Pages;

use App\Filament\Resources\LegalCaseResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListLegalCases extends ListRecords
{
    protected static string $resource = LegalCaseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
