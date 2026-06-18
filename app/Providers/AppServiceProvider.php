<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Validation\ValidationException;
use App\Models\Hearing;
use App\Models\Task;
use App\Models\Deadline;
use App\Observers\HearingObserver;
use App\Observers\TaskObserver;
use App\Observers\DeadlineObserver;
use App\Policies\ActivityPolicy;
use Illuminate\Support\Facades\Gate;
use Spatie\Activitylog\Models\Activity;

class AppServiceProvider extends ServiceProvider
{
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
        Deadline::observe(DeadlineObserver::class);
        Gate::policy(Activity::class, ActivityPolicy::class);


        Page::$reportValidationErrorUsing = function (ValidationException $exception) {
            Notification::make()
                ->title($exception->getMessage())
                ->danger()
                ->send();
        };


    }

}
