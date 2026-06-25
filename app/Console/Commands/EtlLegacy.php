<?php

namespace App\Console\Commands;

use App\Enums\CaseStatus;
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
     * De-para de advogados: texto do legado (UPPER, trim) → chave interna.
     * Valor array = processo com mais de um advogado (vínculo múltiplo no pivot).
     * Ajuste conforme a query 4 do profiling revelar novas variações.
     */
    private array $mapAdvogados = [
        'MARINA'       => ['marina'],
        'MARINA TAURO' => ['marina'],
        'TULA'         => ['tula'],
        'MARINA/TULA'  => ['marina', 'tula'],
        ''             => [],
    ];

    /**
     * Cadastro-base dos advogados a criar no sistema novo.
     * Marina entra como inativa (saiu da sociedade, mas consta em processos abertos).
     * Preencha OAB/dados reais antes de rodar em produção.
     */
    private array $advogados = [
        'marina' => ['name' => 'Marina Tauro', 'oab' => null, 'oab_state' => 'SP', 'active' => false],
        'tula'   => ['name' => 'Tula',          'oab' => null, 'oab_state' => 'SP', 'active' => true],
    ];

    /** Mapa old_id (legado) → new_id (LexFirma), por entidade. */
    private array $idMap = [
        'client' => [],
        'lawyer' => [],
    ];

    public function handle(): int
    {
        $dry = (bool) $this->option('dry-run');
        $status = $this->option('only');

        if ($dry) {
            $this->warn('DRY-RUN: nada será gravado.');
        }

        DB::transaction(function () use ($dry, $status) {
            $this->migrarAdvogados($dry);
            $this->migrarClientes($dry, $status);
            $this->migrarProcessos($dry, $status);
            // Próximas fases: audiências, tarefas, procurações.

            if ($dry) {
                // Em dry-run, desfaz tudo no fim da transação.
                throw new \RuntimeException('__DRY_RUN_ROLLBACK__');
            }
        });

        $this->info('ETL concluído.');
        return self::SUCCESS;
    }

    /**
     * Cria os advogados-base (Marina, Tula) de forma idempotente.
     */
    private function migrarAdvogados(bool $dry): void
    {
        $this->info('— Advogados');

        foreach ($this->advogados as $chave => $dados) {
            if ($dry) {
                $this->line("  [dry] criaria advogado: {$dados['name']} (active=" . ($dados['active'] ? '1' : '0') . ')');
                continue;
            }

            $lawyer = Lawyer::updateOrCreate(
                ['name' => $dados['name']],          // chave natural simples (universo pequeno)
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

    /**
     * Migra apenas os clientes vinculados a processos do status alvo.
     * Junta clientes + documentos + endereco + contato + conjuge + tutelado.
     */
    private function migrarClientes(bool $dry, string $status): void
    {
        $this->info("— Clientes (vinculados a processos '{$status}')");

        // CPFs (normalizados) que aparecem em processos do status alvo.
        $cpfsAlvo = DB::connection('legacy')->table('processos')
            ->where('status_processo', $status)
            ->pluck('cpf_cliente')
            ->map(fn ($c) => $this->soNumeros($c))
            ->filter()
            ->unique();

        // Clientes do legado cujo CPF (em 'documentos') está na lista alvo.
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
            ->get()
            ->filter(fn ($row) => $cpfsAlvo->contains($this->soNumeros($row->doc_cpf)));

        $this->line("  {$legacyClientes->count()} clientes a migrar.");

        foreach ($legacyClientes as $lc) {
            if ($dry) {
                $this->line("  [dry] cliente: {$lc->nome_cliente} (cpf {$lc->doc_cpf})");
                continue;
            }

            $cpf = $this->soNumeros($lc->doc_cpf);

            // Idempotência: chave natural = CPF (via client_documents).
            $client = Client::updateOrCreate(
                // Sem CPF na tabela clients; resolvemos pelo documento abaixo.
                // Usamos nome+nascimento como fallback de unicidade na base.
                ['name' => $lc->nome_cliente, 'date_of_birth' => $lc->nascimento_cliente],
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

            $this->idMap['client'][$lc->id] = $client->id;

            // 1:1 — documentos
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

            $this->line("  ok: {$lc->nome_cliente} → id {$client->id}");
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
            'date_of_birth'  => $s->nascimento_conjuge,
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
                    'date_of_birth' => $t->nascimento_tutelado,
                ],
            );
        }
    }

    /**
     * Migra processos do status alvo, ligando cliente (por CPF) e advogados (de-para).
     */
    private function migrarProcessos(bool $dry, string $status): void
    {
        $this->info("— Processos ('{$status}')");

        $processos = DB::connection('legacy')->table('processos')
            ->where('status_processo', $status)->get();

        $this->line("  {$processos->count()} processos a migrar.");

        foreach ($processos as $p) {
            // Resolve o cliente pelo CPF normalizado, via documento.
            $cpf = $this->soNumeros($p->cpf_cliente);
            $clientId = $this->resolverClientePorCpf($cpf);

            if (! $clientId) {
                $this->warn("  ⚠ processo id {$p->id} ('{$p->numero_processo}') sem cliente resolvido (cpf '{$p->cpf_cliente}') — PULADO.");
                continue;
            }

            if ($dry) {
                $adv = $this->resolverAdvogados($p->nome_advogado);
                $this->line("  [dry] processo {$p->numero_processo} → client {$clientId}, advs " . json_encode($adv));
                continue;
            }

            $case = LegalCase::updateOrCreate(
                ['case_number' => $p->numero_processo],   // chave natural
                [
                    'folder_number' => $p->numero_pasta_processo,
                    'client_id'     => $clientId,
                    'opponent_name' => $p->nome_do_adverso,
                    'status'        => $this->mapStatusProcesso($p->status_processo),
                    'note'          => $p->observacao_processo,
                ],
            );

            // Vínculo advogado(s) via pivot — trata MARINA/TULA como dois.
            $lawyerIds = collect($this->resolverAdvogados($p->nome_advogado))
                ->map(fn ($chave) => $this->idMap['lawyer'][$chave] ?? null)
                ->filter()
                ->values()
                ->all();

            if ($lawyerIds) {
                $case->lawyers()->syncWithoutDetaching($lawyerIds);
            }

            $this->line("  ok: {$p->numero_processo} → case {$case->id}");
        }
    }

    /** Resolve client_id a partir do CPF normalizado (busca em client_documents). */
    private function resolverClientePorCpf(string $cpf): ?int
    {
        if ($cpf === '') {
            return null;
        }

        return DB::table('client_documents')
            ->where('cpf', $cpf)
            ->value('client_id');
    }

    /** Texto livre do legado → array de chaves de advogado. */
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
            'concluído',
            'concluido'   => CaseStatus::Closed,
            default       => CaseStatus::Open,
        };
    }

    /** Remove tudo que não for dígito (normaliza CPF). */
    private function soNumeros(?string $valor): string
    {
        return preg_replace('/\D+/', '', (string) $valor) ?? '';
    }
}
