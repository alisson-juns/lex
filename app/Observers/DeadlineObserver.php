<?php

namespace App\Observers;

use App\Jobs\SyncDeadlineToGoogle;
use App\Models\Deadline;

class DeadlineObserver
{
    public function created(Deadline $deadline): void
    {
        SyncDeadlineToGoogle::dispatch($deadline->id, 'create');
    }

    public function updated(Deadline $deadline): void
    {
        $relevantFields = ['deadline_type', 'fatal_date', 'internal_date', 'status', 'note'];

        if (! $deadline->wasChanged($relevantFields)) {
            return;
        }

        SyncDeadlineToGoogle::dispatch($deadline->id, 'update');
    }

    public function deleted(Deadline $deadline): void
    {
        SyncDeadlineToGoogle::dispatch($deadline->id, 'delete');
    }

    public function restored(Deadline $deadline): void
    {
        SyncDeadlineToGoogle::dispatch($deadline->id, 'create');
    }
}
