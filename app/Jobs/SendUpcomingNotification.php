<?php

namespace App\Jobs;

use App\Models\User;
use App\Notifications\UpcomingItemNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendUpcomingNotification implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(
        public int $userId,
        public string $title,
        public string $body,
        public int $windowHours,
        public ?string $url,
        public bool $sendEmail,
    ) {
        $this->onQueue('notifications');
    }

    public function handle(): void
    {
        $user = User::find($this->userId);

        if (! $user) {
            return;
        }

        $user->notify(new UpcomingItemNotification(
            title:       $this->title,
            body:        $this->body,
            windowHours: $this->windowHours,
            url:         $this->url,
            sendEmail:   $this->sendEmail,
        ));
    }
}
