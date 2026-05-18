<?php

namespace App\Filament\Resources\GratuityDeclarationResource\Pages;

use App\Filament\Resources\GratuityDeclarationResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListGratuityDeclarations extends ListRecords
{
    protected static string $resource = GratuityDeclarationResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()];
    }
}
