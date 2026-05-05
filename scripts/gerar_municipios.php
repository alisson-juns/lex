#!/usr/bin/env php
<?php
/**
 * Gera storage/app/feriados/municipios.json com todos os municípios do Brasil.
 *
 * Uso:
 *   1. curl -L "https://raw.githubusercontent.com/joaopbini/feriados-brasil/master/dados/localizacao/municipios/municipios.json" \
 *           -o storage/app/feriados/municipios_raw.json
 *
 *   2. php scripts/gerar_municipios.php
 */

$rawPath    = __DIR__ . '/../storage/app/feriados/municipios_raw.json';
$outputPath = __DIR__ . '/../storage/app/feriados/municipios.json';

if (! file_exists($rawPath)) {
    echo "Arquivo não encontrado: {$rawPath}\n";
    exit(1);
}

$all = json_decode(file_get_contents($rawPath), true);

if (! $all) {
    echo "Erro ao decodificar JSON.\n";
    exit(1);
}

$codigoParaUf = [
    11 => 'RO', 12 => 'AC', 13 => 'AM', 14 => 'RR', 15 => 'PA',
    16 => 'AP', 17 => 'TO', 21 => 'MA', 22 => 'PI', 23 => 'CE',
    24 => 'RN', 25 => 'PB', 26 => 'PE', 27 => 'AL', 28 => 'SE',
    29 => 'BA', 31 => 'MG', 32 => 'ES', 33 => 'RJ', 35 => 'SP',
    41 => 'PR', 42 => 'SC', 43 => 'RS', 50 => 'MS', 51 => 'MT',
    52 => 'GO', 53 => 'DF',
];

$municipios = array_map(fn($m) => [
    'codigo_ibge' => (string) $m['codigo_ibge'],
    'nome'        => $m['nome'],
    'uf'          => $codigoParaUf[(int) $m['codigo_uf']] ?? null,
], $all);

// Remove entradas sem UF mapeada
$municipios = array_values(array_filter($municipios, fn($m) => $m['uf'] !== null));

usort($municipios, fn($a, $b) => strcmp($a['nome'], $b['nome']));

file_put_contents(
    $outputPath,
    json_encode($municipios, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)
);

echo "Gerado: {$outputPath}\n";
echo "Total de municípios: " . count($municipios) . "\n";