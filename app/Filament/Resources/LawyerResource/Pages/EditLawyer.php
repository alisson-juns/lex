<?php

namespace App\Filament\Resources\LawyerResource\Pages;

use App\Filament\Resources\LawyerResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditLawyer extends EditRecord
{
    protected static string $resource = LawyerResource::class;

    protected function getSaveFormAction(): \Filament\Actions\Action
    {
        return parent::getSaveFormAction()
            ->extraAttributes(['formnovalidate' => true]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
