<?php
namespace App\Filament\Resources\ClientResource\Pages;

use App\Filament\Resources\ClientResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditClient extends EditRecord
{
    protected static string $resource = ClientResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Cônjuge
        if (!empty($data['_delete_spouse'])) {
            $this->record->spouse()->delete();
        }
        unset($data['_delete_spouse']);

        // Dependentes — sync manual
        if (array_key_exists('wards', $data)) {
            // Filtra itens sem nome (itens deletados que ficam como fantasmas)
            $wards = array_filter($data['wards'] ?? [], fn ($w) => !empty($w['name']));

            $keepIds = collect($wards)->pluck('id')->filter()->toArray();

            $this->record->wards()->whereNotIn('id', $keepIds)->delete();

            foreach ($wards as $wardData) {
                $id = $wardData['id'] ?? null;
                unset($wardData['id']);

                if ($id) {
                    $this->record->wards()->where('id', $id)->update($wardData);
                } else {
                    $this->record->wards()->create($wardData);
                }
            }

            unset($data['wards']);
        }

        return $data;
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}