<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class RecycleBinPage extends Page
{
    protected static string $view = 'revive::pages.recycle-bin';

    public static function getNavigationGroup(): ?string
    {
        return 'Configurações';
    }

    public static function getNavigationSort(): ?int
    {
        return 100;
    }

    public static function getNavigationIcon(): string
    {
        return 'heroicon-s-trash';
    }

    public static function getActiveNavigationIcon(): string
    {
        return 'heroicon-s-trash';
    }

    public static function getNavigationLabel(): string
    {
        return 'Lixeira';
    }

    public static function getSlug(): string
    {
        return 'recycle-bin';
    }

    public function getTitle(): string
    {
        return 'Lixeira';
    }

    public static function canAccess(): bool
    {
        return auth()->check() && auth()->user()->hasRole(['super_admin', 'admin']);
    }
}
