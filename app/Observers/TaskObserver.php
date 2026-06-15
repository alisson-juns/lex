<?php

namespace App\Observers;

use App\Jobs\SyncTaskToGoogle;
use App\Models\Task;

class TaskObserver
{
    public function created(Task $task): void
    {
        SyncTaskToGoogle::dispatch($task->id, 'create');
    }

    public function updated(Task $task): void
    {
        $relevantFields = ['title', 'description', 'due_date', 'due_time', 'note'];

        if (! $task->wasChanged($relevantFields)) {
            return;
        }

        SyncTaskToGoogle::dispatch($task->id, 'update');
    }

    public function deleted(Task $task): void
    {
        SyncTaskToGoogle::dispatch($task->id, 'delete');
    }

    public function restored(Task $task): void
    {
        SyncTaskToGoogle::dispatch($task->id, 'create');
    }
}
