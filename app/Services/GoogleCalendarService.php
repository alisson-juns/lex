<?php

namespace App\Services;

use App\Models\GoogleToken;
use App\Models\Hearing;
use App\Models\HearingGoogleEvent;
use App\Models\Task;
use App\Models\TaskGoogleEvent;
use App\Models\User;
use Carbon\Carbon;
use Google\Client as GoogleClient;
use Google\Service\Calendar as GoogleCalendar;
use Google\Service\Calendar\Event as GoogleEvent;
use Google\Service\Calendar\EventDateTime;
use Illuminate\Support\Facades\Log;

class GoogleCalendarService
{
    // ─────────────────────────────────────────────────────────────────
    // Auth
    // ─────────────────────────────────────────────────────────────────

    public function getAuthUrl(string $state = ''): string
    {
        $client = $this->makeBaseClient();
        if ($state) {
            $client->setState($state);
        }
        return $client->createAuthUrl();
    }

    public function handleCallback(User $user, string $code): void
    {
        $client = $this->makeBaseClient();
        $token  = $client->fetchAccessTokenWithAuthCode($code);

        if (isset($token['error'])) {
            throw new \RuntimeException('Erro ao obter token Google: ' . ($token['error_description'] ?? $token['error']));
        }

        // Preserva refresh_token se não vier na resposta
        if (! isset($token['refresh_token'])) {
            $existing = $user->googleToken;
            if ($existing) {
                $old = json_decode($existing->token_json, true);
                if (! empty($old['refresh_token'])) {
                    $token['refresh_token'] = $old['refresh_token'];
                }
            }
        }

        \App\Models\GoogleToken::updateOrCreate(
            ['user_id' => $user->id],
            ['token_json' => json_encode($token)]
        );
    }

    public function getClient(User $user): ?GoogleClient
    {
        $tokenModel = $user->googleToken;
        if (! $tokenModel) {
            return null;
        }

        $client     = $this->makeBaseClient();
        $tokenArray = json_decode($tokenModel->token_json, true);
        $client->setAccessToken($tokenArray);

        if ($client->isAccessTokenExpired()) {
            $refreshToken = $client->getRefreshToken() ?? ($tokenArray['refresh_token'] ?? null);

            if (! $refreshToken) {
                $tokenModel->delete();
                return null;
            }

            $newToken = $client->fetchAccessTokenWithRefreshToken($refreshToken);

            if (isset($newToken['error'])) {
                Log::warning("Google token refresh falhou user #{$user->id}: " . ($newToken['error_description'] ?? $newToken['error']));
                $tokenModel->delete();
                return null;
            }

            $tokenModel->update(['token_json' => json_encode($client->getAccessToken())]);
        }

        return $client;
    }

    // ─────────────────────────────────────────────────────────────────
    // Audiências — sincroniza para TODOS os usuários com token
    // ─────────────────────────────────────────────────────────────────

    public function createHearingEvent(Hearing $hearing): void
    {
        $tokens = GoogleToken::with('user')->get();

        foreach ($tokens as $token) {
            $this->createHearingEventForUser($hearing, $token->user);
        }
    }

    public function updateHearingEvent(Hearing $hearing): void
    {
        $tokens = GoogleToken::with('user')->get();

        foreach ($tokens as $token) {
            $user   = $token->user;
            $client = $this->getClient($user);
            if (! $client) {
                continue;
            }

            $pivot = HearingGoogleEvent::where('hearing_id', $hearing->id)
                ->where('user_id', $user->id)
                ->first();

            if (! $pivot) {
                // Usuário conectou depois da criação — cria agora
                $this->createHearingEventForUser($hearing, $user);
                continue;
            }

            try {
                $service    = new GoogleCalendar($client);
                $calendarId = $token->google_calendar_id ?? 'primary';

                $event = new GoogleEvent([
                    'summary'     => '⚖️ ' . $hearing->description,
                    'location'    => $hearing->location ?? '',
                    'description' => $this->buildHearingDescription($hearing),
                    'start'       => $this->buildDateTime($hearing->date, $hearing->time),
                    'end'         => $this->buildDateTime($hearing->date, $hearing->time, 60),
                ]);

                $service->events->update($calendarId, $pivot->google_event_id, $event);

            } catch (\Exception $e) {
                Log::error("Google update hearing #{$hearing->id} user #{$user->id}: " . $e->getMessage());
            }
        }
    }

    public function deleteHearingEvent(Hearing $hearing): void
    {
        $pivots = HearingGoogleEvent::where('hearing_id', $hearing->id)->with('user')->get();

        foreach ($pivots as $pivot) {
            $client = $this->getClient($pivot->user);
            if (! $client) {
                continue;
            }

            try {
                $service    = new GoogleCalendar($client);
                $calendarId = $pivot->user->googleToken->google_calendar_id ?? 'primary';
                $service->events->delete($calendarId, $pivot->google_event_id);
            } catch (\Exception $e) {
                Log::error("Google delete hearing #{$hearing->id} user #{$pivot->user_id}: " . $e->getMessage());
            }
        }

        HearingGoogleEvent::where('hearing_id', $hearing->id)->delete();
    }

    // ─────────────────────────────────────────────────────────────────
    // Tarefas — sincroniza para TODOS os usuários com token
    // ─────────────────────────────────────────────────────────────────

    public function createTaskEvent(Task $task): void
    {
        $tokens = GoogleToken::with('user')->get();

        foreach ($tokens as $token) {
            $this->createTaskEventForUser($task, $token->user);
        }
    }

    public function updateTaskEvent(Task $task): void
    {
        $tokens = GoogleToken::with('user')->get();

        foreach ($tokens as $token) {
            $user   = $token->user;
            $client = $this->getClient($user);
            if (! $client) {
                continue;
            }

            $pivot = TaskGoogleEvent::where('task_id', $task->id)
                ->where('user_id', $user->id)
                ->first();

            if (! $pivot) {
                $this->createTaskEventForUser($task, $user);
                continue;
            }

            try {
                $service    = new GoogleCalendar($client);
                $calendarId = $token->google_calendar_id ?? 'primary';

                $event = new GoogleEvent([
                    'summary'     => '📋 ' . $task->title,
                    'description' => $this->buildTaskDescription($task),
                    'start'       => $this->buildDateTime($task->due_date, $task->due_time),
                    'end'         => $this->buildDateTime($task->due_date, $task->due_time, 60),
                ]);

                $service->events->update($calendarId, $pivot->google_event_id, $event);

            } catch (\Exception $e) {
                Log::error("Google update task #{$task->id} user #{$user->id}: " . $e->getMessage());
            }
        }
    }

    public function deleteTaskEvent(Task $task): void
    {
        $pivots = TaskGoogleEvent::where('task_id', $task->id)->with('user')->get();

        foreach ($pivots as $pivot) {
            $client = $this->getClient($pivot->user);
            if (! $client) {
                continue;
            }

            try {
                $service    = new GoogleCalendar($client);
                $calendarId = $pivot->user->googleToken->google_calendar_id ?? 'primary';
                $service->events->delete($calendarId, $pivot->google_event_id);
            } catch (\Exception $e) {
                Log::error("Google delete task #{$task->id} user #{$pivot->user_id}: " . $e->getMessage());
            }
        }

        TaskGoogleEvent::where('task_id', $task->id)->delete();
    }

    // ─────────────────────────────────────────────────────────────────
    // Leitura — CalendarWidget
    // ─────────────────────────────────────────────────────────────────

    /**
     * Busca eventos externos do Google do usuário (criados diretamente no Google).
     * Eventos criados pelo LexFirma são filtrados pelo google_event_id na pivot.
     */
    public function getEventsForCalendar(User $user, string $start, string $end): array
    {
        $client = $this->getClient($user);
        if (! $client) {
            return [];
        }

        try {
            $service    = new GoogleCalendar($client);
            $calendarId = $user->googleToken->google_calendar_id ?? 'primary';

            $results = $service->events->listEvents($calendarId, [
                'timeMin'      => Carbon::parse($start)->toRfc3339String(),
                'timeMax'      => Carbon::parse($end)->toRfc3339String(),
                'singleEvents' => true,
                'orderBy'      => 'startTime',
                'maxResults'   => 250,
            ]);

            // IDs de eventos criados pelo LexFirma para este usuário
            $lexIds = HearingGoogleEvent::where('user_id', $user->id)
                ->pluck('google_event_id')
                ->merge(
                    TaskGoogleEvent::where('user_id', $user->id)->pluck('google_event_id')
                )
                ->toArray();

            $events = [];

            foreach ($results->getItems() as $item) {
                if (in_array($item->getId(), $lexIds)) {
                    continue; // já aparece como hearing/task — não duplica
                }

                $startDt  = $item->getStart();
                $isAllDay = (bool) $startDt->getDate();
                $startStr = $isAllDay
                    ? $startDt->getDate()
                    : Carbon::parse($startDt->getDateTime())->toIso8601String();

                $events[] = [
                    'id'              => 'google-' . $item->getId(),
                    'title'           => '📅 ' . ($item->getSummary() ?? '(sem título)'),
                    'start'           => $startStr,
                    'allDay'          => $isAllDay,
                    'backgroundColor' => '#10b981',
                    'borderColor'     => '#059669',
                    'textColor'       => '#ffffff',
                    'editable'        => false,
                    'extendedProps'   => [
                        'type'        => 'google',
                        'color'       => '#10b981',
                        'description' => $item->getDescription() ?? '',
                        'htmlLink'    => $item->getHtmlLink(),
                    ],
                ];
            }

            return $events;

        } catch (\Exception $e) {
            Log::error("Google fetch events user #{$user->id}: " . $e->getMessage());
            return [];
        }
    }

    // ─────────────────────────────────────────────────────────────────
    // Helpers privados
    // ─────────────────────────────────────────────────────────────────

    private function createHearingEventForUser(Hearing $hearing, User $user): void
    {
        $client = $this->getClient($user);
        if (! $client) {
            return;
        }

        try {
            $service    = new GoogleCalendar($client);
            $calendarId = $user->googleToken->google_calendar_id ?? 'primary';

            $event = new GoogleEvent([
                'summary'     => '⚖️ ' . $hearing->description,
                'location'    => $hearing->location ?? '',
                'description' => $this->buildHearingDescription($hearing),
                'start'       => $this->buildDateTime($hearing->date, $hearing->time),
                'end'         => $this->buildDateTime($hearing->date, $hearing->time, 60),
                'reminders'   => [
                    'useDefault' => false,
                    'overrides'  => [
                        ['method' => 'popup', 'minutes' => 60],
                        ['method' => 'email', 'minutes' => 1440],
                    ],
                ],
            ]);

            $created = $service->events->insert($calendarId, $event);

            HearingGoogleEvent::updateOrCreate(
                ['hearing_id' => $hearing->id, 'user_id' => $user->id],
                ['google_event_id' => $created->getId()]
            );

        } catch (\Exception $e) {
            Log::error("Google create hearing #{$hearing->id} user #{$user->id}: " . $e->getMessage());
        }
    }

    private function createTaskEventForUser(Task $task, User $user): void
    {
        $client = $this->getClient($user);
        if (! $client) {
            return;
        }

        try {
            $service    = new GoogleCalendar($client);
            $calendarId = $user->googleToken->google_calendar_id ?? 'primary';

            $event = new GoogleEvent([
                'summary'     => '📋 ' . $task->title,
                'description' => $this->buildTaskDescription($task),
                'start'       => $this->buildDateTime($task->due_date, $task->due_time),
                'end'         => $this->buildDateTime($task->due_date, $task->due_time, 60),
                'reminders'   => [
                    'useDefault' => false,
                    'overrides'  => [
                        ['method' => 'popup', 'minutes' => 30],
                    ],
                ],
            ]);

            $created = $service->events->insert($calendarId, $event);

            TaskGoogleEvent::updateOrCreate(
                ['task_id' => $task->id, 'user_id' => $user->id],
                ['google_event_id' => $created->getId()]
            );

        } catch (\Exception $e) {
            Log::error("Google create task #{$task->id} user #{$user->id}: " . $e->getMessage());
        }
    }

    private function makeBaseClient(): GoogleClient
    {
        $client = new GoogleClient();
        $client->setClientId(config('services.google.client_id'));
        $client->setClientSecret(config('services.google.client_secret'));
        $client->setRedirectUri(config('services.google.redirect'));
        $client->addScope(GoogleCalendar::CALENDAR_EVENTS);
        $client->setAccessType('offline');
        $client->setPrompt('consent');
        return $client;
    }

    private function buildDateTime(Carbon $date, ?string $time, int $addMinutes = 0): EventDateTime
    {
        $dt = new EventDateTime();

        if ($time) {
            $dateTime = $date->copy()
                ->setTimeFromTimeString($time)
                ->addMinutes($addMinutes)
                ->setTimezone('America/Sao_Paulo');
            $dt->setDateTime($dateTime->toRfc3339String());
            $dt->setTimeZone('America/Sao_Paulo');
        } else {
            $dt->setDate(
                $addMinutes > 0
                ? $date->copy()->addDay()->toDateString()
                : $date->toDateString()
            );
        }

        return $dt;
    }

    private function buildHearingDescription(Hearing $hearing): string
    {
        $lines = ['Criado pelo LexFirma'];
        if ($hearing->legalCase?->case_number) {
            $lines[] = 'Processo: ' . $hearing->legalCase->case_number;
        }
        if ($hearing->location) {
            $lines[] = 'Local: ' . $hearing->location;
        }
        if ($hearing->lawyer?->name) {
            $lines[] = 'Advogado: ' . $hearing->lawyer->name;
        }
        if ($hearing->note) {
            $lines[] = 'Obs: ' . $hearing->note;
        }
        return implode("\n", $lines);
    }

    private function buildTaskDescription(Task $task): string
    {
        $lines = ['Criado pelo LexFirma'];
        if ($task->description) {
            $lines[] = $task->description;
        }
        if ($task->legalCase?->case_number) {
            $lines[] = 'Processo: ' . $task->legalCase->case_number;
        }
        if ($task->note) {
            $lines[] = 'Obs: ' . $task->note;
        }
        return implode("\n", $lines);
    }
}
