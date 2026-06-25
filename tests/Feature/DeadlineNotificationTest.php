<?php

use App\Jobs\SendUpcomingNotification;
use App\Models\Deadline;
use App\Models\Lawyer;
use App\Models\LegalCase;
use App\Models\NotificationLog;
use App\Models\User;
use App\Enums\DeadlineStatus;
use App\Enums\DeadlineType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Bus;

uses(RefreshDatabase::class);

/**
 * Monta um prazo com um advogado vinculado a um usuário do sistema.
 * Retorna [Deadline, User] para as asserções.
 */
function makeDeadlineWithLawyer(array $deadlineAttrs = []): array
{
    $user   = User::factory()->create();
    $lawyer = Lawyer::factory()->create(['user_id' => $user->id]);
    $case   = LegalCase::factory()->create();

    $deadline = Deadline::factory()->create(array_merge([
        'legal_case_id' => $case->id,
        'deadline_type' => DeadlineType::Contestacao,
        'status'        => DeadlineStatus::Pending,
        'fatal_date'    => Carbon::today()->addDay(),   // 1 dia → janela de 24h
        'internal_date' => null,
    ], $deadlineAttrs));

    $deadline->lawyers()->attach($lawyer->id);

    return [$deadline, $user];
}

beforeEach(function () {
    // Congela o tempo às 08:00 (horário do scan --allday) para a contagem de dias ser determinística.
    Carbon::setTestNow(Carbon::today()->setTime(8, 0));
});

it('enfileira notificacao quando o prazo fatal vence em 1 dia', function () {
    Bus::fake();
    [$deadline, $user] = makeDeadlineWithLawyer();

    $this->artisan('notifications:scan --allday')->assertSuccessful();

    Bus::assertDispatched(SendUpcomingNotification::class, function ($job) use ($user) {
        return $job->userId === $user->id && $job->windowHours === 24;
    });
});

it('nao enfileira quando o prazo esta fora das janelas de 24h e 48h', function () {
    Bus::fake();
    // Vence em 5 dias → nem 24h nem 48h.
    makeDeadlineWithLawyer(['fatal_date' => Carbon::today()->addDays(5)]);

    $this->artisan('notifications:scan --allday')->assertSuccessful();

    Bus::assertNotDispatched(SendUpcomingNotification::class);
});

it('nao reenfileira o mesmo prazo na segunda execucao (idempotencia)', function () {
    Bus::fake();
    makeDeadlineWithLawyer();

    $this->artisan('notifications:scan --allday')->assertSuccessful();
    $this->artisan('notifications:scan --allday')->assertSuccessful();

    // Mesmo rodando duas vezes, o NotificationLog garante um único dispatch.
    Bus::assertDispatchedTimes(SendUpcomingNotification::class, 1);
    expect(NotificationLog::count())->toBe(1);
});

it('ignora prazos com status Cumprido ou Cancelado', function () {
    Bus::fake();
    makeDeadlineWithLawyer(['status' => DeadlineStatus::Completed]);
    makeDeadlineWithLawyer(['status' => DeadlineStatus::Cancelled]);

    $this->artisan('notifications:scan --allday')->assertSuccessful();

    Bus::assertNotDispatched(SendUpcomingNotification::class);
});

it('nao entrega para advogado sem usuario do sistema vinculado', function () {
    Bus::fake();

    $lawyer = Lawyer::factory()->create(['user_id' => null]);
    $case   = LegalCase::factory()->create();
    $deadline = Deadline::factory()->create([
        'legal_case_id' => $case->id,
        'deadline_type' => DeadlineType::Contestacao,
        'status'        => DeadlineStatus::Pending,
        'fatal_date'    => Carbon::today()->addDay(),
    ]);
    $deadline->lawyers()->attach($lawyer->id);

    $this->artisan('notifications:scan --allday')->assertSuccessful();

    Bus::assertNotDispatched(SendUpcomingNotification::class);
});

it('dispara dois eventos distintos para prazo com data fatal e interna na mesma janela', function () {
    Bus::fake();
    // Fatal e interna ambas em 1 dia → dois dispatches (date_type fatal + internal).
    makeDeadlineWithLawyer([
        'fatal_date'    => Carbon::today()->addDay(),
        'internal_date' => Carbon::today()->addDay(),
    ]);

    $this->artisan('notifications:scan --allday')->assertSuccessful();

    Bus::assertDispatchedTimes(SendUpcomingNotification::class, 2);
    expect(NotificationLog::count())->toBe(2);
});

it('respeita a preferencia de email do usuario no payload do job', function () {
    Bus::fake();
    [$deadline, $user] = makeDeadlineWithLawyer();
    $user->update(['notify_email_deadlines' => false]);

    $this->artisan('notifications:scan --allday')->assertSuccessful();

    Bus::assertDispatched(SendUpcomingNotification::class, fn ($job) => $job->sendEmail === false);
});
