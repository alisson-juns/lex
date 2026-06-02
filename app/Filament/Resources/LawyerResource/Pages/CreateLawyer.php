<?php

namespace App\Filament\Resources\LawyerResource\Pages;

use App\Filament\Resources\LawyerResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateLawyer extends CreateRecord
{
    protected static string $resource = LawyerResource::class;

    protected function getCreateFormAction(): \Filament\Actions\Action
    {
        return parent::getCreateFormAction()
            ->extraAttributes(['formnovalidate' => true]);
    }

    protected function getCreateAnotherFormAction(): \Filament\Actions\Action
    {
        return parent::getCreateAnotherFormAction()
            ->extraAttributes(['formnovalidate' => true]);
    }
}
