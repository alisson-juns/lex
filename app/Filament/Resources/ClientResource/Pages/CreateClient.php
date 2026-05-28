<?php

namespace App\Filament\Resources\ClientResource\Pages;

use App\Filament\Resources\ClientResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;
use Illuminate\Validation\ValidationException;

class CreateClient extends CreateRecord
{
    protected static string $resource = ClientResource::class;

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

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (isset($data['wards'])) {
            $data['wards'] = array_values(
                array_filter(
                    $data['wards'],
                    fn ($ward) => !empty($ward['name'])
                )
            );
        }

        return $data;
    }
}
