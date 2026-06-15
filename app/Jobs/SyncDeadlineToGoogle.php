<?php

namespace App\Jobs;

use App\Models\Deadline;
use App\Services\GoogleCalendarService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SyncDeadlineToGoogle implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * @param  int     $deadlineId  ID do prazo (não o model — pode estar soft-deleted no delete)
     * @param  string  $action      'create' | 'update' | 'delete'
     */
    public function __construct(
        public int $deadlineId,
        public string $action,
    ) {
    }

    public function handle(GoogleCalendarService $google): void
    {
        $deadline = Deadline::withTrashed()->find($this->deadlineId);

        if (! $deadline) {
            return;
        }

        match ($this->action) {
            'create' => $google->createDeadlineEvents($deadline),
            'update' => $google->updateDeadlineEvents($deadline),
            'delete' => $google->deleteDeadlineEvents($deadline),
            default  => null,
        };
    }
}
