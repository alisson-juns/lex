<?php

namespace App\Notifications;

use Filament\Notifications\Notification as FilamentNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class UpcomingItemNotification extends Notification
{
    use Queueable;

    public function __construct(
        public string $title,       // ex: "Prazo fatal — Contestação"
        public string $body,        // ex: "Processo 0001234-..., vence em 24h (16/06/2026)"
        public int $windowHours,
        public ?string $url = null, // link pro registro no Filament
        public bool $sendEmail = true,
    ) {
    }

    public function via(object $notifiable): array
    {
        $channels = ['database'];
        if ($this->sendEmail && $notifiable->notify_email) {
            $channels[] = 'mail';
        }
        return $channels;
    }

    // Sino do Filament (formato que o bell icon entende)
    public function toDatabase(object $notifiable): array
    {
        return FilamentNotification::make()
            ->title($this->title)
            ->body($this->body)
            ->icon('heroicon-o-bell-alert')
            ->color($this->windowHours <= 24 ? 'danger' : 'warning')
            ->getDatabaseMessage();
    }

    public function toMail(object $notifiable): MailMessage
    {
        $mail = (new MailMessage())
            ->subject("[LexFirma] {$this->title} — vence em {$this->windowHours}h")
            ->greeting("Olá, {$notifiable->name}")
            ->line($this->body);

        if ($this->url) {
            $mail->action('Abrir no sistema', $this->url);
        }

        return $mail->line('Esta é uma notificação automática do LexFirma.');
    }
}
