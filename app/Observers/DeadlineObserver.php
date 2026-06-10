<?php

namespace App\Observers;

use App\Models\Deadline;
use App\Services\GoogleCalendarService;

class DeadlineObserver
{
    public function __construct(
        protected GoogleCalendarService $googleService
    ) {
    }

    public function created(Deadline $deadline): void
    {
        $this->googleService->createDeadlineEvents($deadline);
    }

    public function updated(Deadline $deadline): void
    {
        $relevantFields = ['deadline_type', 'fatal_date', 'internal_date', 'status', 'note'];

        if (! $deadline->wasChanged($relevantFields)) {
            return;
        }

        $this->googleService->updateDeadlineEvents($deadline);
    }

    public function deleted(Deadline $deadline): void
    {
        $this->googleService->deleteDeadlineEvents($deadline);
    }

    public function restored(Deadline $deadline): void
    {
        $this->googleService->createDeadlineEvents($deadline);
    }
}
