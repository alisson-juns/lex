<?php

namespace App\Jobs;

use App\Models\Task;
use App\Services\GoogleCalendarService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SyncTaskToGoogle implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * @param  int     $taskId  ID da tarefa (não o model — pode estar soft-deleted no delete)
     * @param  string  $action  'create' | 'update' | 'delete'
     */
    public function __construct(
        public int $taskId,
        public string $action,
    ) {
    }

    public function handle(GoogleCalendarService $google): void
    {
        $task = Task::withTrashed()->find($this->taskId);

        if (! $task) {
            return;
        }

        match ($this->action) {
            'create' => $google->createTaskEvent($task),
            'update' => $google->updateTaskEvent($task),
            'delete' => $google->deleteTaskEvent($task),
            default  => null,
        };
    }
}
