<?php

namespace App\Observers;

use App\Models\Hearing;
use App\Services\GoogleCalendarService;

class HearingObserver
{
    public function __construct(
        protected GoogleCalendarService $googleService
    ) {
    }

    public function created(Hearing $hearing): void
    {
        $this->googleService->createHearingEvent($hearing);
    }

    public function updated(Hearing $hearing): void
    {
        $relevantFields = ['description', 'date', 'time', 'location', 'lawyer_id', 'note'];

        if (! $hearing->wasChanged($relevantFields)) {
            return;
        }

        $this->googleService->updateHearingEvent($hearing);
    }

    public function deleted(Hearing $hearing): void
    {
        $this->googleService->deleteHearingEvent($hearing);
    }
}
