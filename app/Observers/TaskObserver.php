<?php

namespace App\Observers;

use App\Models\Task;
use App\Services\GoogleCalendarService;

class TaskObserver
{
    public function __construct(
        protected GoogleCalendarService $googleService
    ) {
    }

    public function created(Task $task): void
    {
        $this->googleService->createTaskEvent($task);
    }

    public function updated(Task $task): void
    {
        $relevantFields = ['title', 'description', 'due_date', 'due_time', 'note'];

        if (! $task->wasChanged($relevantFields)) {
            return;
        }

        $this->googleService->updateTaskEvent($task);
    }

    public function deleted(Task $task): void
    {
        $this->googleService->deleteTaskEvent($task);
    }
}
