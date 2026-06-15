<?php

namespace App\Observers;

use App\Jobs\SyncHearingToGoogle;
use App\Models\Hearing;

class HearingObserver
{
    public function created(Hearing $hearing): void
    {
        SyncHearingToGoogle::dispatch($hearing->id, 'create');
    }

    public function updated(Hearing $hearing): void
    {
        $relevantFields = ['description', 'date', 'time', 'location', 'lawyer_id', 'note'];

        if (! $hearing->wasChanged($relevantFields)) {
            return;
        }

        SyncHearingToGoogle::dispatch($hearing->id, 'update');
    }

    public function deleted(Hearing $hearing): void
    {
        SyncHearingToGoogle::dispatch($hearing->id, 'delete');
    }

    public function restored(Hearing $hearing): void
    {
        SyncHearingToGoogle::dispatch($hearing->id, 'create');
    }
}
