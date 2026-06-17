<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CourtNameSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        $names = [
            'Anexo Fiscal',
            'Vara Cível',
            'Vara Criminal',
            'Vara da Família',
            'Vara da Família e Sucessões',
            'Vara da Fazenda Pública',
            'Vara do Juizado Especial Cível',
            'Vara do Trabalho',
            'Vara Federal',
            'Vara do Juizado Especial Criminal',
            'Vara do Juizado Especial Federal',
            'OAB SANTOS',
        ];

        $rows = array_map(fn ($name) => [
            'name'       => $name,
            'created_at' => $now,
            'updated_at' => $now,
        ], $names);

        DB::table('court_names')->insertOrIgnore($rows);
    }
}
