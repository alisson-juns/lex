<?php

namespace App\Jobs;

use App\Models\Hearing;
use App\Services\GoogleCalendarService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SyncHearingToGoogle implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * @param  int     $hearingId  ID da audiência (não o model — pode estar soft-deleted no delete)
     * @param  string  $action     'create' | 'update' | 'delete'
     */
    public function __construct(
        public int $hearingId,
        public string $action,
    ) {
    }

    public function handle(GoogleCalendarService $google): void
    {
        // delete: o registro já foi soft-deleted; precisa de withTrashed().
        // create/update: registro vivo, mas withTrashed() não atrapalha.
        $hearing = Hearing::withTrashed()->find($this->hearingId);

        if (! $hearing) {
            return; // hard-deleted definitivamente; nada a sincronizar
        }

        match ($this->action) {
            'create' => $google->createHearingEvent($hearing),
            'update' => $google->updateHearingEvent($hearing),
            'delete' => $google->deleteHearingEvent($hearing),
            default  => null,
        };
    }
}
