<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Validation\ValidationException;

class AppServiceProvider extends ServiceProvider
{
    // remova o bloco public $singletons inteiro
    
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        //
    }
}

Page::$reportValidationErrorUsing = function (ValidationException $exception) {
    Notification::make()
        ->title($exception->getMessage())
        ->danger()
        ->send();
};