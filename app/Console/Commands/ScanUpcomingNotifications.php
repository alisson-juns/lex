<?php

namespace App\Console\Commands;

use App\Enums\{DeadlineStatus, TaskStatus};
use App\Filament\Resources\{DeadlineResource, HearingResource, TaskResource};
use App\Jobs\SendUpcomingNotification;
use App\Models\{Deadline, Hearing, NotificationLog, Task};
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class ScanUpcomingNotifications extends Command
{
    protected $signature = 'notifications:scan {--timed} {--allday} {--test}';
    protected $description = 'Varre prazos, audiências e tarefas próximas e enfileira notificações.';

    public function handle(): int
    {

        if ($this->option('test')) {
            return $this->runTest();
        }

        $timed  = $this->option('timed');
        $allday = $this->option('allday');

        if (! $timed && ! $allday) {
            $this->error('Informe --timed ou --allday.');
            return self::FAILURE;
        }


        foreach ([24, 48] as $window) {
            $this->scanDeadlines($window, $allday);   // prazo é sempre all-day
            $this->scanHearings($window, $timed, $allday);
            $this->scanTasks($window, $timed, $allday);
        }

        $this->info('Varredura concluída.');
        return self::SUCCESS;
    }

    private function scanDeadlines(int $window, bool $allday): void
    {
        if (! $allday) {
            return; // prazo só roda no --allday
        }

        $deadlines = Deadline::query()
            ->whereNotIn('status', [
                DeadlineStatus::Completed->value,
                DeadlineStatus::Cancelled->value,
            ])
            ->with('lawyers.user', 'legalCase')
            ->get();

        foreach ($deadlines as $deadline) {

            $this->dispatchByDay(
                item:      $deadline,
                date:      $deadline->fatal_date,
                window:    $window,
                dateType:  'fatal',
                title:     '⚠️ Prazo fatal — ' . $deadline->deadline_type->label(),
                emailPref: 'notify_email_deadlines',
                url:       DeadlineResource::getUrl('view', ['record' => $deadline->id]),
                process:   $deadline->legalCase?->case_number,
                lawyers:   $deadline->lawyers,
            );


            if ($deadline->internal_date) {

                $this->dispatchByDay(
                    item:      $deadline,
                    date:      $deadline->internal_date,
                    window:    $window,
                    dateType:  'internal',
                    title:     'Prazo interno — ' . $deadline->deadline_type->label(),
                    emailPref: 'notify_email_deadlines',
                    url:       DeadlineResource::getUrl('view', ['record' => $deadline->id]),
                    process:   $deadline->legalCase?->case_number,
                    lawyers:   $deadline->lawyers,
                );

            }
        }
    }

    private function scanHearings(int $window, bool $timed, bool $allday): void
    {
        $hearings = Hearing::query()
            ->whereDate('date', '>=', now()->toDateString())
            ->with('lawyer.user', 'legalCase')
            ->get();

        foreach ($hearings as $hearing) {
            $hasTime = ! empty($hearing->time);

            // Roteia: audiência com hora → só no --timed; sem hora → só no --allday
            if ($hasTime && ! $timed) {
                continue;
            }
            if (! $hasTime && ! $allday) {
                continue;
            }

            $title     = '⚖️ Audiência — ' . ($hearing->legalCase?->case_number ?? 'Processo');
            $emailPref = 'notify_email_hearings';
            $url       = HearingResource::getUrl('view', ['record' => $hearing->id]);
            $process   = $hearing->legalCase?->case_number;
            $lawyers   = collect([$hearing->lawyer])->filter();

            if ($hasTime) {
                $this->dispatchByHour(
                    $hearing,
                    $this->combineDateTime($hearing->date, $hearing->time),
                    $window,
                    null,
                    $title,
                    $emailPref,
                    $url,
                    $process,
                    $lawyers
                );
            } else {
                $this->dispatchByDay(
                    $hearing,
                    $hearing->date,
                    $window,
                    null,
                    $title,
                    $emailPref,
                    $url,
                    $process,
                    $lawyers
                );
            }
        }
    }

    private function scanTasks(int $window, bool $timed, bool $allday): void
    {
        $tasks = Task::query()
            ->whereNotIn('status', [
                TaskStatus::Completed->value,
                TaskStatus::Cancelled->value,
            ])
            ->with('lawyers.user', 'legalCase')
            ->get();

        foreach ($tasks as $task) {
            $hasTime = ! empty($task->due_time);

            if ($hasTime && ! $timed) {
                continue;
            }
            if (! $hasTime && ! $allday) {
                continue;
            }

            $title     = '📋 Tarefa — ' . $task->title;
            $emailPref = 'notify_email_tasks';
            $url       = TaskResource::getUrl('view', ['record' => $task->id]);
            $process   = $task->legalCase?->case_number;
            $lawyers   = $task->lawyers;

            if ($hasTime) {
                $this->dispatchByHour(
                    $task,
                    $this->combineDateTime($task->due_date, $task->due_time),
                    $window,
                    null,
                    $title,
                    $emailPref,
                    $url,
                    $process,
                    $lawyers
                );
            } else {
                $this->dispatchByDay(
                    $task,
                    $task->due_date,
                    $window,
                    null,
                    $title,
                    $emailPref,
                    $url,
                    $process,
                    $lawyers
                );
            }
        }
    }

    /**
     * Janela por HORA — itens com horário real. Roda no --timed (hourly).
     * Dispara quando faltam entre (window-1) e window horas.
     */
    private function dispatchByHour(
        $item,
        ?Carbon $moment,
        int $window,
        ?string $dateType,
        string $title,
        string $emailPref,
        ?string $url,
        ?string $process,
        $lawyers,
    ): void {
        if (! $moment) {
            return;
        }

        $diff = now()->diffInHours($moment, false);
        if ($diff > $window || $diff <= ($window - 1)) {
            return;
        }

        $vencimento = $moment->format('d/m/Y \à\s H:i');
        $this->fanOut($item, $window, $dateType, $title, $emailPref, $url, $process, $lawyers, $vencimento);
    }

    /**
     * Janela por DIA — itens all-day. Roda no --allday (dailyAt 8h).
     * Dispara quando faltam exatamente `window/24` dias.
     */
    private function dispatchByDay(
        $item,
        ?Carbon $date,
        int $window,
        ?string $dateType,
        string $title,
        string $emailPref,
        ?string $url,
        ?string $process,
        $lawyers,
    ): void {
        if (! $date) {
            return;
        }

        $daysWindow = intdiv($window, 24); // 24h→1 dia, 48h→2 dias
        $daysLeft   = now()->startOfDay()->diffInDays($date->copy()->startOfDay(), false);


        if ((int) $daysLeft !== $daysWindow) {
            return;
        }


        $vencimento = $date->format('d/m/Y');
        $this->fanOut($item, $window, $dateType, $title, $emailPref, $url, $process, $lawyers, $vencimento);
    }

    /**
     * Resolve advogados→usuários, aplica idempotência e enfileira o job.
     */
    private function fanOut(
        $item,
        int $window,
        ?string $dateType,
        string $title,
        string $emailPref,
        ?string $url,
        ?string $process,
        $lawyers,
        string $vencimento,
    ): void {
        foreach ($lawyers as $lawyer) {
            $user = $lawyer->user;
            if (! $user) {
                continue; // advogado sem usuário do sistema → sem entrega
            }

            $log = NotificationLog::firstOrCreate(
                [
                    'user_id'         => $user->id,
                    'notifiable_type' => get_class($item),
                    'notifiable_id'   => $item->id,
                    'date_type'       => $dateType,
                    'window_hours'    => $window,
                ],
                ['sent_at' => now()],
            );

            if (! $log->wasRecentlyCreated) {
                continue; // já enfileirado antes
            }

            $body = trim(($process ? "Processo {$process}. " : '')
                . "Vence em {$window}h ({$vencimento}).");

            SendUpcomingNotification::dispatch(
                userId:      $user->id,
                title:       $title,
                body:        $body,
                windowHours: $window,
                url:         $url,
                sendEmail:   (bool) $user->{$emailPref},
            );
        }
    }

    private function runTest(): int
    {
        $this->warn('MODO TESTE — ignora janela e idempotência. Dispara pro 1º registro de cada tipo que existir.');

        $task = Task::with('lawyers.user', 'legalCase')
            ->whereNotIn('status', [TaskStatus::Completed->value, TaskStatus::Cancelled->value])
            ->whereHas('lawyers')
            ->first();

        if ($task) {
            $this->fireTest(
                item:      $task,
                lawyers:   $task->lawyers,
                title:     '📋 [TESTE] Tarefa — ' . $task->title,
                emailPref: 'notify_email_tasks',
                url:       TaskResource::getUrl('view', ['record' => $task->id]),
                process:   $task->legalCase?->case_number,
            );
        } else {
            $this->line('Nenhuma tarefa com advogado vinculado encontrada.');
        }

        $deadline = Deadline::with('lawyers.user', 'legalCase')
            ->whereNotIn('status', [DeadlineStatus::Completed->value, DeadlineStatus::Cancelled->value])
            ->whereHas('lawyers')
            ->first();

        if ($deadline) {
            $this->fireTest(
                item:      $deadline,
                lawyers:   $deadline->lawyers,
                title:     '⚠️ [TESTE] Prazo — ' . $deadline->deadline_type->label(),
                emailPref: 'notify_email_deadlines',
                url:       DeadlineResource::getUrl('view', ['record' => $deadline->id]),
                process:   $deadline->legalCase?->case_number,
            );
        }

        $this->info('Jobs de teste enfileirados. Rode o worker e cheque sino + e-mail.');
        return self::SUCCESS;
    }

    private function fireTest($item, $lawyers, string $title, string $emailPref, ?string $url, ?string $process): void
    {
        foreach ($lawyers as $lawyer) {
            $user = $lawyer->user;

            if (! $user) {
                $this->warn("  ⚠ {$lawyer->name} sem usuário vinculado — não recebe.");
                continue;
            }

            SendUpcomingNotification::dispatch(
                userId:      $user->id,
                title:       $title,
                body:        trim(($process ? "Processo {$process}. " : '') . 'Notificação de TESTE.'),
                windowHours: 24,
                url:         $url,
                sendEmail:   (bool) $user->{$emailPref},
            );

            $email = $user->{$emailPref} ? 'sim' : 'não';
            $this->line("  → {$user->name} <{$user->email}> (e-mail: {$email})");
        }
    }

    private function combineDateTime($date, $time): ?Carbon
    {
        if (! $date) {
            return null;
        }

        $d = Carbon::parse($date)->format('Y-m-d');
        return Carbon::parse($time ? "{$d} {$time}" : "{$d} 00:00");
    }
}
