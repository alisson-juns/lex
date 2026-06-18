<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use App\Models\FeeAgreement;
use App\Models\EnterpriseFeeAgreement;
use App\Models\PowerOfAttorney;
use App\Models\GratuityDeclaration;
use App\Models\EnterprisePowerOfAttorney;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/**
 * Limpeza diária de rascunhos órfãos (is_draft = true) com mais de 1 dia.
 * São registros criados pelo modal de geração de documento que nunca
 * foram finalizados (afterSave() vira is_draft = false). Não aparecem
 * no RelationManager por causa do filtro, mas acumulam no banco.
 *
 * Hard delete proposital: nenhum desses models usa SoftDeletes —
 * rascunho descartado não precisa ir para a lixeira.
 */
Schedule::call(function () {
    $documentModels = [
        FeeAgreement::class,
        EnterpriseFeeAgreement::class,
        PowerOfAttorney::class,
        GratuityDeclaration::class,
        EnterprisePowerOfAttorney::class,
    ];

    foreach ($documentModels as $model) {
        $model::where('is_draft', true)
            ->where('created_at', '<', now()->subDay())
            ->delete();
    }
})->daily();


// Itens com horário definido (audiência com hora, tarefa com hora) — janela por hora
Schedule::command('notifications:scan --timed')
    ->hourly()
    ->withoutOverlapping();

// Itens all-day (prazos sempre; audiência/tarefa sem hora) — 8h de Brasília
Schedule::command('notifications:scan --allday')
    ->dailyAt('08:00')
    ->timezone('America/Sao_Paulo')
    ->withoutOverlapping();
