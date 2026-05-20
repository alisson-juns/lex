<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Validation\ValidationException;
use App\Models\Hearing;
use App\Models\Task;
use App\Observers\HearingObserver;
use App\Observers\TaskObserver;

class AppServiceProvider extends ServiceProvider
{
    // remova o bloco public $singletons inteiro

    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        \Carbon\Carbon::setLocale('pt_BR');
        setlocale(LC_TIME, 'pt_BR.UTF-8', 'pt_BR', 'portuguese');

        Hearing::observe(HearingObserver::class);
        Task::observe(TaskObserver::class);


    }

}

Page::$reportValidationErrorUsing = function (ValidationException $exception) {
    Notification::make()
        ->title($exception->getMessage())
        ->danger()
        ->send();
};
