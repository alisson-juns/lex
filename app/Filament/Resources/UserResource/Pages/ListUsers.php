<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListUsers extends ListRecords
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    protected function getTableQuery(): Builder
    {
        $query = parent::getTableQuery();
        $currentUser = auth()->user();

        // super_admin vê todos
        if ($currentUser->hasRole('super_admin')) {
            return $query;
        }

        // admin vê a si mesmo + lawyer + user (não vê outros admins nem super_admin)
        if ($currentUser->hasRole('admin')) {
            return $query->where(function ($q) use ($currentUser) {
                $q->where('id', $currentUser->id)
                  ->orWhereDoesntHave(
                      'roles',
                      fn ($r) =>
                      $r->whereIn('name', ['super_admin', 'admin'])
                  );
            });
        }

        // outros papéis: só veem a si mesmos
        return $query->where('id', $currentUser->id);
    }
}
