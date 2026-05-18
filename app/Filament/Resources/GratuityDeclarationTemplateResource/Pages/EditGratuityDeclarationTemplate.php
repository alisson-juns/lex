<?php

namespace App\Filament\Resources\GratuityDeclarationTemplateResource\Pages;

use App\Filament\Resources\GratuityDeclarationTemplateResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditGratuityDeclarationTemplate extends EditRecord
{
    protected static string $resource = GratuityDeclarationTemplateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
