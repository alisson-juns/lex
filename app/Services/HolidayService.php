<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

class HolidayService
{
    /**
     * Retorna eventos de feriado para o FullCalendar.
     */
    public function getEvents(
        string $start,
        string $end,
        array  $states = [],
        array  $cities = []
    ): array {
        $startDate = Carbon::parse($start)->startOfDay();
        $endDate   = Carbon::parse($end)->endOfDay();
        $years     = range($startDate->year, $endDate->year);
        

        $events = [];

        foreach ($years as $year) {
            // ── Nacionais fixos (JSON ou fallback calculado) ────────
            $nacionais = $this->loadJson("nacional/{$year}.json");
            if (empty($nacionais)) {
                $nacionais = $this->getNacionaisFixos($year);
            }

            foreach ($nacionais as $h) {
                $date = $this->parseDate($h['data'] ?? '');
                if ($date?->between($startDate, $endDate)) {
                    $events[] = $this->buildEvent($h, $date, 'nacional');
                }
            }

            // ── Feriados móveis (sempre calculados) ─────────────────
            foreach ($this->getMoveisNacionais($year) as $h) {
                $date = Carbon::parse($h['data'])->startOfDay();
                if ($date->between($startDate, $endDate)) {
                    $events[] = $this->buildEvent($h, $date, 'nacional');
                }
            }

            // ── Estaduais ────────────────────────────────────────────
            $estaduais = $this->loadJson("estadual/{$year}.json");
            foreach ($estaduais as $h) {
                if (! in_array($h['uf'] ?? '', $states)) {
                    continue;
                }
                $date = $this->parseDate($h['data'] ?? '');
                if ($date?->between($startDate, $endDate)) {
                    $events[] = $this->buildEvent($h, $date, 'estadual', $h['uf']);
                }
            }

            // ── Municipais ───────────────────────────────────────────
            if (! empty($cities)) {
                $municipais = $this->loadJson("municipal/{$year}.json");
                foreach ($municipais as $h) {
                    if (! in_array((string) ($h['codigo_ibge'] ?? ''), array_map('strval', $cities))) {
                        continue;
                    }
                    $date = $this->parseDate($h['data'] ?? '');
                    if ($date?->between($startDate, $endDate)) {
                        $events[] = $this->buildEvent($h, $date, 'municipal');
                    }
                }
            }
        }

        return $events;
    }

    // ─────────────────────────────────────────────────────────────────
    // Cálculo dos feriados móveis (Páscoa, Carnaval, Corpus Christi)
    // ─────────────────────────────────────────────────────────────────

    /**
     * Algoritmo de Meeus/Jones/Butcher para o Domingo de Páscoa.
     */
    private function pascoa(int $year): Carbon
    {
        $a = $year % 19;
        $b = intdiv($year, 100);
        $c = $year % 100;
        $d = intdiv($b, 4);
        $e = $b % 4;
        $f = intdiv($b + 8, 25);
        $g = intdiv($b - $f + 1, 3);
        $h = (19 * $a + $b - $d - $g + 15) % 30;
        $i = intdiv($c, 4);
        $k = $c % 4;
        $l = (32 + 2 * $e + 2 * $i - $h - $k) % 7;
        $m = intdiv($a + 11 * $h + 22 * $l, 451);
        $month = intdiv($h + $l - 7 * $m + 114, 31);
        $day   = (($h + $l - 7 * $m + 114) % 31) + 1;

        return Carbon::createFromDate($year, $month, $day)->startOfDay();
    }

    /**
     * Retorna os feriados nacionais móveis do ano.
     * Inclui Carnaval (segunda + terça) como feriado por costume.
     */
    private function getMoveisNacionais(int $year): array
    {
        return Cache::remember("holidays_moveis_{$year}", now()->addDays(30), function () use ($year) {
            $pascoa = $this->pascoa($year);

            return [
                [
                    'data' => $pascoa->copy()->subDays(47)->toDateString(),
                    'nome' => 'Carnaval',
                    'id'   => "carnaval_{$year}",
                ],
                [
                    'data' => $pascoa->copy()->subDays(2)->toDateString(),
                    'nome' => 'Sexta-feira Santa',
                    'id'   => "sexta_santa_{$year}",
                ],
                [
                    'data' => $pascoa->toDateString(),
                    'nome' => 'Páscoa',
                    'id'   => "pascoa_{$year}",
                ],
                [
                    'data' => $pascoa->copy()->addDays(60)->toDateString(),
                    'nome' => 'Corpus Christi',
                    'id'   => "corpus_{$year}",
                ],
            ];
        });
    }

    /**
     * Fallback de feriados nacionais fixos quando o JSON do ano não existe.
     */
    private function getNacionaisFixos(int $year): array
    {
        return [
            ['id' => "ano_novo_{$year}",      'data' => "{$year}-01-01", 'nome' => 'Ano Novo'],
            ['id' => "tiradentes_{$year}",    'data' => "{$year}-04-21", 'nome' => 'Tiradentes'],
            ['id' => "trabalho_{$year}",      'data' => "{$year}-05-01", 'nome' => 'Dia do Trabalho'],
            ['id' => "independencia_{$year}", 'data' => "{$year}-09-07", 'nome' => 'Independência do Brasil'],
            ['id' => "aparecida_{$year}",     'data' => "{$year}-10-12", 'nome' => 'Nossa Senhora Aparecida'],
            ['id' => "finados_{$year}",       'data' => "{$year}-11-02", 'nome' => 'Finados'],
            ['id' => "republica_{$year}",     'data' => "{$year}-11-15", 'nome' => 'Proclamação da República'],
            ['id' => "consciencia_{$year}",   'data' => "{$year}-11-20", 'nome' => 'Consciência Negra'],
            ['id' => "natal_{$year}",         'data' => "{$year}-12-25", 'nome' => 'Natal'],
        ];
    }

    // ─────────────────────────────────────────────────────────────────
    // Helpers
    // ─────────────────────────────────────────────────────────────────

    private function loadJson(string $relativePath): array
    {
        $cacheKey = 'holidays.' . str_replace(['/', '.'], '_', $relativePath);

        return Cache::remember($cacheKey, now()->addHours(24), function () use ($relativePath) {
            $fullPath = storage_path("app/feriados/{$relativePath}");

            if (file_exists($fullPath)) {
                return json_decode(file_get_contents($fullPath), true) ?? [];
            }

            // Arquivo do ano não existe — deriva de um ano anterior disponível
            return $this->deriveFromExistingYear($relativePath);
        });
    }

    /**
     * Para anos sem JSON, encontra o arquivo mais recente disponível
     * e substitui o ano nas datas. Funciona porque feriados fixos
     * (estaduais, municipais, nacionais) sempre caem no mesmo dia/mês.
     */
    private function deriveFromExistingYear(string $relativePath): array
    {
        // Extrai tipo e ano do caminho (ex: "estadual/2027.json")
        if (! preg_match('#^(.+)/(\d{4})\.json$#', $relativePath, $m)) {
            return [];
        }

        [, $tipo, $targetYear] = $m;

        $baseDir = storage_path("app/feriados/{$tipo}");

        if (! is_dir($baseDir)) {
            return [];
        }

        // Pega o arquivo mais recente disponível
        $files = glob("{$baseDir}/*.json");
        if (empty($files)) {
            return [];
        }

        sort($files);
        $sourceFile = end($files); // arquivo mais recente
        $sourceYear = (int) pathinfo($sourceFile, PATHINFO_FILENAME);

        $data = json_decode(file_get_contents($sourceFile), true) ?? [];

        // Substitui o ano nas datas (DD/MM/AAAA → DD/MM/$targetYear)
        return array_map(function ($holiday) use ($sourceYear, $targetYear) {
            if (isset($holiday['data'])) {
                $holiday['data'] = str_replace(
                    "/{$sourceYear}",
                    "/{$targetYear}",
                    $holiday['data']
                );
            }
            return $holiday;
        }, $data);
    }

    private function parseDate(string $raw): ?Carbon
    {
        if ($raw === '') {
            return null;
        }
        // Aceita DD/MM/YYYY (JSON do repositório) e YYYY-MM-DD (fallback)
        try {
            return str_contains($raw, '/')
                ? Carbon::createFromFormat('d/m/Y', $raw)->startOfDay()
                : Carbon::createFromFormat('Y-m-d', $raw)->startOfDay();
        } catch (\Exception) {
            return null;
        }
    }

    private function buildEvent(array $holiday, Carbon $date, string $tipo, string $uf = ''): array
    {
        $label = match($tipo) {
            'estadual'  => " ({$uf})",
            'municipal' => ' (Municipal)',
            default     => '',
        };

        return [
            'id'              => 'holiday-' . md5($tipo . ($holiday['id'] ?? '') . $date->toDateString()),
            'title'           => ($holiday['nome'] ?? 'Feriado') . $label,
            'start'           => $date->toDateString(),
            'allDay'          => true,
            'display'         => 'block',
            'backgroundColor' => 'transparent',
            'borderColor'     => 'transparent',
            'textColor'       => '#dc2626',
            'classNames'      => ['lex-holiday'],
            'extendedProps'   => [
                'type'        => 'holiday',
                'holidayType' => $tipo,
            ],
        ];
    }

    /**
     * Retorna todos os municípios para o Select do FirmSettings.
     */
    public static function getMunicipios(array $filterUfs = []): array
    {
        $cacheKey = 'municipios_all_' . implode('_', $filterUfs);

        return Cache::remember($cacheKey, now()->addDays(7), function () use ($filterUfs) {
            $path = storage_path('app/feriados/municipios.json');

            if (! file_exists($path)) {
                return [];
            }

            $data = json_decode(file_get_contents($path), true) ?? [];

            return collect($data)
                ->when(! empty($filterUfs), fn ($c) => $c->filter(
                    fn ($m) => in_array($m['uf'] ?? '', $filterUfs)
                ))
                ->sortBy('nome')
                ->mapWithKeys(fn ($m) => [
                    (string) $m['codigo_ibge'] => $m['nome'] . ' (' . $m['uf'] . ')',
                ])
                ->toArray();
        });
    }
}