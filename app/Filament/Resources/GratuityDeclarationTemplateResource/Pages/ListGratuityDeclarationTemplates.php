<?php

namespace App\Filament\Resources\GratuityDeclarationTemplateResource\Pages;

use App\Filament\Resources\GratuityDeclarationTemplateResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListGratuityDeclarationTemplates extends ListRecords
{
    protected static string $resource = GratuityDeclarationTemplateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
