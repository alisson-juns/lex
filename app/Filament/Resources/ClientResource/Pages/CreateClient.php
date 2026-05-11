<?php
namespace App\Filament\Resources\ClientResource\Pages;

use App\Filament\Resources\ClientResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateClient extends CreateRecord
{
    protected static string $resource = ClientResource::class;

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