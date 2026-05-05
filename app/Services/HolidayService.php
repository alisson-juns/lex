<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

class HolidayService
{
    /**
     * Retorna eventos de feriado para o FullCalendar.
     *
     * @param  string  $start       ISO 8601
     * @param  string  $end         ISO 8601
     * @param  array   $states      UFs selecionadas (ex: ['SP', 'RJ', 'BA'])
     * @param  array   $cities      codigo_ibge selecionados
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
            $nacionais  = $this->loadJson("nacional/{$year}.json");
            $estaduais  = $this->loadJson("estadual/{$year}.json");
            $municipais = ! empty($cities) ? $this->loadJson("municipal/{$year}.json") : [];

            foreach ($nacionais as $h) {
                $date = $this->parseDate($h['data'] ?? '');
                if ($date?->between($startDate, $endDate)) {
                    $events[] = $this->buildEvent($h, $date, 'nacional');
                }
            }

            foreach ($estaduais as $h) {
                if (! in_array($h['uf'] ?? '', $states)) {
                    continue;
                }
                $date = $this->parseDate($h['data'] ?? '');
                if ($date?->between($startDate, $endDate)) {
                    $events[] = $this->buildEvent($h, $date, 'estadual', $h['uf']);
                }
            }

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

        return $events;
    }

    private function loadJson(string $relativePath): array
    {
        $cacheKey = 'holidays.' . str_replace(['/', '.'], '_', $relativePath);

        return Cache::remember($cacheKey, now()->addHours(24), function () use ($relativePath) {
            $fullPath = storage_path("app/feriados/{$relativePath}");

            if (! file_exists($fullPath)) {
                return [];
            }

            return json_decode(file_get_contents($fullPath), true) ?? [];
        });
    }

    private function parseDate(string $raw): ?Carbon
    {
        try {
            return Carbon::createFromFormat('d/m/Y', $raw)->startOfDay();
        } catch (\Exception) {
            return null;
        }
    }

    private function buildEvent(array $holiday, Carbon $date, string $tipo, string $uf = ''): array
    {
        $colors = [
            'nacional'  => '#fee2e2', // vermelho suave
            'estadual'  => '#fef3c7', // âmbar suave
            'municipal' => '#dbeafe', // azul suave
        ];

        $label = match($tipo) {
            'estadual'  => " ({$uf})",
            'municipal' => ' (Municipal)',
            default     => '',
        };

        return [
            'id'          => 'holiday-' . md5($tipo . ($holiday['id'] ?? '') . $date->toDateString()),
            'title'       => $holiday['nome'] ?? 'Feriado',
            'start'       => $date->toDateString(),
            'allDay'      => true,
            'display'     => 'block',
            'backgroundColor' => 'transparent',
            'borderColor'     => 'transparent',
            'textColor'       => '#dc2626',
            'classNames'  => ['lex-holiday'],
            'extendedProps' => [
                'type'        => 'holiday',
                'holidayType' => $tipo,
                'label'       => $label,
            ],
        ];
    }

    /**
     * Retorna todos os municípios para o Select do FirmSettings.
     * Formato: ['codigo_ibge' => 'Nome (UF)', ...]
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