<?php

namespace App\Console\Commands;

use App\Enums\CaseStatus;
use App\Enums\CaseType;
use App\Models\Client;
use App\Models\Lawyer;
use App\Models\LegalCase;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class EtlLegacy extends Command
{
    protected $signature = 'etl:legacy
                            {--dry-run : Mostra o que faria sem gravar nada}
                            {--only=Aberto : Status dos processos a migrar (legado)}';

    protected $description = 'Migra o banco legado (admin_lex) para o schema LexFirma.';

    /**
     * Termos no campo "adverso" (nome_do_adverso) que marcam processo
     * NAO judicial - administrativos/inventario, lancados manualmente depois.
     */
    private array $termosNaoJudiciais = ['INSS', 'ADMINISTRATIVO', 'INVENTÁRIO', 'INVENTARIO'];

    /**
     * De-para de advogados: texto do legado (UPPER, trim) -> chave interna.
     * Valor array = processo com mais de um advogado (vinculo multiplo no pivot).
     */
    private array $mapAdvogados = [
    'MARINA'       => ['marina'],
    'MARINA TAURO' => ['marina'],
    'TULA'         => ['tula'],
    'TULA JUNS'    => ['tula'],
    'MARINA/TULA'  => ['marina', 'tula'],
    ''             => [],
    ];

    /**
     * Cadastro-base dos advogados a criar. Marina entra inativa (saiu da
     * sociedade, mas consta em processos abertos). Preencha OAB antes de producao.
     */
    private array $advogados = [
    'marina' => ['name' => 'Marina Tauro', 'oab' => '431280', 'oab_state' => 'SP', 'active' => false],
    'tula'   => ['name' => 'Tula',          'oab' => '431326', 'oab_state' => 'SP', 'active' => true],
    ];

    /** old_id (legado) -> new_id (LexFirma). */
    private array $idMap = ['lawyer' => []];

    /** CPF normalizado -> client_id (novo). Populado na fase de clientes. */
    private array $cpfToClientId = [];

    /** Anomalias acumuladas para relatorio final. */
    private array $anomalias = [];

    public function handle(): int
    {
        $dry = (bool) $this->option('dry-run');
        $status = $this->option('only');

        if ($dry) {
            $this->warn('DRY-RUN: nada será gravado.');
        }

        try {
            DB::transaction(function () use ($dry, $status) {
                $this->migrarAdvogados();
                $this->migrarClientes($dry, $status);
                $this->migrarProcessos($status);

                if ($dry) {
                    throw new DryRunRollback();
                }
            });
        } catch (DryRunRollback) {
            // Esperado em dry-run: transacao revertida.
        }

        $this->imprimirRelatorio();
        $this->info($dry ? 'DRY-RUN concluído (nada gravado).' : 'ETL concluído.');
        return self::SUCCESS;
    }

    private function migrarAdvogados(): void
    {
        $this->info('— Advogados');

        foreach ($this->advogados as $chave => $dados) {
            $lawyer = Lawyer::updateOrCreate(
                ['name' => $dados['name']],
                [
                    'oab'       => $dados['oab'],
                    'oab_state' => $dados['oab_state'],
                    'active'    => $dados['active'],
                ],
            );

            $this->idMap['lawyer'][$chave] = $lawyer->id;
            $this->line("  ok: {$dados['name']} → id {$lawyer->id}");
        }
    }

    private function migrarClientes(bool $dry, string $status): void
    {
        $this->info("— Clientes (vinculados a processos JUDICIAIS '{$status}')");

        // CPFs de processos JUDICIAIS do status alvo (exclui administrativos).
        $cpfsAlvo = DB::connection('legacy')->table('processos')
            ->where('status_processo', $status)
            ->get(['cpf_cliente', 'nome_do_adverso'])
            ->reject(fn ($p) => $this->ehNaoJudicial($p->nome_do_adverso))
            ->map(fn ($p) => $this->soNumeros($p->cpf_cliente))
            ->filter()
            ->unique();


        $legacyClientes = DB::connection('legacy')->table('clientes as c')
            ->leftJoin('documentos as d', 'd.cliente_id', '=', 'c.id')
            ->select(
                'c.*',
                'd.cpf_cliente as doc_cpf',
                'd.rg_cliente as doc_rg',
                'd.pis_cliente as doc_pis',
                'd.ctps_cliente as doc_ctps',
                'd.outros_documentos_cliente as doc_outros'
            )
            ->get();


        $this->line("  {$legacyClientes->count()} clientes a migrar.");

        foreach ($legacyClientes as $lc) {
            $cpf = $this->soNumeros($lc->doc_cpf);

            if (strlen($cpf) !== 11) {
                $this->anomalias[] = "Cliente '{$lc->nome_cliente}': CPF '{$lc->doc_cpf}' ("
                    . strlen($cpf) . ' dígitos) — revisar.';
            }

            $client = Client::updateOrCreate(
                ['name' => $lc->nome_cliente, 'date_of_birth' => $this->data($lc->nascimento_cliente)],
                [
                    'gender'         => $lc->sexo_cliente,
                    'father'         => $lc->pai_cliente,
                    'mother'         => $lc->mae_cliente,
                    'place_of_birth' => $lc->naturalidade_cliente,
                    'nationality'    => $lc->nacionalidade_cliente,
                    'marital_status' => $lc->estado_civil_cliente,
                    'profession'     => $lc->profissao_cliente,
                    'note'           => $lc->observacao_cliente,
                ],
            );

            if ($cpf !== '') {
                $this->cpfToClientId[$cpf] = $client->id;
            }

            $client->client_documents()->updateOrCreate([], [
                'cpf'             => $cpf,
                'rg'              => $lc->doc_rg,
                'pis'             => $lc->doc_pis,
                'ctps'            => $lc->doc_ctps,
                'other_documents' => $lc->doc_outros,
            ]);

            $this->migrarEndereco($lc->id, $client);
            $this->migrarContato($lc->id, $client);
            $this->migrarConjuge($lc->id, $client);
            $this->migrarTutelados($lc->id, $client);

            $tag = $dry ? '[dry]' : 'ok:';
            $this->line("  {$tag} {$lc->nome_cliente} → id {$client->id}");
        }
    }

    private function migrarEndereco(int $legacyClienteId, Client $client): void
    {
        $e = DB::connection('legacy')->table('endereco')
            ->where('cliente_id', $legacyClienteId)->first();
        if (! $e) {
            return;
        }

        $client->client_addresses()->updateOrCreate([], [
            'street'     => $e->logradouro_cliente,
            'number'     => $e->numeral_cliente,
            'complement' => $e->complemento_cliente,
            'zipcode'    => $e->cep_cliente,
            'district'   => $e->bairro_cliente,
            'city'       => $e->cidade_cliente,
            'state'      => $e->uf_cliente,
        ]);
    }

    private function migrarContato(int $legacyClienteId, Client $client): void
    {
        $c = DB::connection('legacy')->table('contato')
            ->where('cliente_id', $legacyClienteId)->first();
        if (! $c) {
            return;
        }

        $client->client_contacts()->updateOrCreate([], [
            'email'              => $c->email_cliente,
            'cellphone'          => $c->celular_cliente,
            'phone'              => $c->telefone_cliente,
            'optional_email'     => $c->email_recado_cliente,
            'message_cell_phone' => $c->celular_recado_cliente,
            'message_phone'      => $c->telefone_recado_cliente,
        ]);
    }

    private function migrarConjuge(int $legacyClienteId, Client $client): void
    {
        $s = DB::connection('legacy')->table('conjuge')
            ->where('cliente_id', $legacyClienteId)->first();
        if (! $s) {
            return;
        }

        $client->spouse()->updateOrCreate([], [
            'name'           => $s->nome_conjuge,
            'cpf'            => $this->soNumeros($s->cpf_conjuge),
            'rg'             => $s->rg_conjuge,
            'marital_status' => $s->estado_civil_conjuge,
            'father'         => $s->pai_conjuge,
            'mother'         => $s->mae_conjuge,
            'pis'            => $s->pis_conjuge,
            'ctps'           => $s->ctps_conjuge,
            'profession'     => $s->profissao_conjuge,
            'date_of_birth'  => $this->data($s->nascimento_conjuge),
            'place_of_birth' => $s->naturalidade_conjuge,
            'nationality'    => $s->nacionalidade_conjuge,
            'phone'          => $s->telefone_conjuge,
            'mobile'         => $s->celular_conjuge,
            'email'          => $s->email_conjuge,
            'note'           => $s->observacao_conjuge,
        ]);
    }

    private function migrarTutelados(int $legacyClienteId, Client $client): void
    {
        $tutelados = DB::connection('legacy')->table('tutelado')
            ->where('cliente_id', $legacyClienteId)->get();

        foreach ($tutelados as $t) {
            $client->wards()->updateOrCreate(
                ['cpf' => $this->soNumeros($t->cpf_tutelado)],
                [
                    'name'          => $t->nome_tutelado,
                    'rg'            => $t->rg_tutelado,
                    'date_of_birth' => $this->data($t->nascimento_tutelado),
                ],
            );
        }
    }

    private function migrarProcessos(string $status): void
    {
        $this->info("— Processos JUDICIAIS ('{$status}')");

        $processos = DB::connection('legacy')->table('processos')
            ->where('status_processo', $status)->get();

        $migrados = 0;
        $pulados = 0;

        foreach ($processos as $p) {
            if ($this->ehNaoJudicial($p->nome_do_adverso)) {
                $this->anomalias[] = "NÃO MIGRADO (administrativo): id {$p->id} "
                    . "'{$p->numero_processo}' — adverso: {$p->nome_do_adverso}.";
                $pulados++;
                continue;
            }

            $cpf = $this->soNumeros($p->cpf_cliente);
            $clientId = $this->cpfToClientId[$cpf] ?? null;

            if (! $clientId) {
                $this->anomalias[] = "SEM CLIENTE: processo id {$p->id} "
                    . "'{$p->numero_processo}' (cpf '{$p->cpf_cliente}') — revisar.";
                $pulados++;
                continue;
            }

            $case = LegalCase::updateOrCreate(
                ['case_number' => $p->numero_processo],
                [
                    'type'          => CaseType::Judicial,
                    'folder_number' => $p->numero_pasta_processo,
                    'client_id'     => $clientId,
                    'opponent_name' => $p->nome_do_adverso,
                    'status'        => $this->mapStatusProcesso($p->status_processo),
                    'note'          => $p->observacao_processo,
                ],
            );

            $lawyerIds = collect($this->resolverAdvogados($p->nome_advogado))
                ->map(fn ($chave) => $this->idMap['lawyer'][$chave] ?? null)
                ->filter()->values()->all();

            if ($lawyerIds) {
                $case->lawyers()->syncWithoutDetaching($lawyerIds);
            } else {
                $this->anomalias[] = "SEM ADVOGADO: processo '{$p->numero_processo}' "
                    . "(advogado legado: '{$p->nome_advogado}') — revisar.";
            }

            $migrados++;
            $this->line("  ok: {$p->numero_processo} → case {$case->id}");
        }

        $this->line("  → {$migrados} migrados, {$pulados} pulados.");
    }

    private function ehNaoJudicial(?string $adverso): bool
    {
        $txt = strtoupper((string) $adverso);
        foreach ($this->termosNaoJudiciais as $termo) {
            if (str_contains($txt, $termo)) {
                return true;
            }
        }
        return false;
    }

    private function resolverAdvogados(?string $texto): array
    {
        $chave = strtoupper(trim((string) $texto));
        return $this->mapAdvogados[$chave] ?? [];
    }

    private function mapStatusProcesso(?string $legado): CaseStatus
    {
        return match (strtolower(trim((string) $legado))) {
            'aberto'      => CaseStatus::Open,
            'arquivado'   => CaseStatus::Archived,
            'cancelado'   => CaseStatus::Cancelled,
            'concluído', 'concluido' => CaseStatus::Closed,
            default       => CaseStatus::Open,
        };
    }

    private function soNumeros(?string $valor): string
    {
        return preg_replace('/\D+/', '', (string) $valor) ?? '';
    }

    /** Converte datas-lixo do legado (0000-00-00, vazio, -0001) em null. */
    private function data(?string $valor): ?string
    {
        $v = trim((string) $valor);

        // Vazio, zerado ou claramente inválido → null
        if ($v === '' || str_starts_with($v, '0000') || str_starts_with($v, '-')) {
            return null;
        }

        // Valida se é data real; se não for, null
        try {
            return \Carbon\Carbon::parse($v)->format('Y-m-d');
        } catch (\Throwable) {
            return null;
        }
    }

    private function imprimirRelatorio(): void
    {
        if (empty($this->anomalias)) {
            $this->info('Sem anomalias.');
            return;
        }

        $this->newLine();
        $this->warn('=== RELATÓRIO DE ANOMALIAS (' . count($this->anomalias) . ') ===');
        foreach ($this->anomalias as $a) {
            $this->line('  • ' . $a);
        }
    }
}

/** Excecao interna para reverter a transacao em dry-run sem poluir o log. */
class DryRunRollback extends \RuntimeException
{
}
