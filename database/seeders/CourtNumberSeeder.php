<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CourtNumberSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        $rows = collect(range(1, 99))->map(fn ($n) => [
            'number'     => "{$n}ª",
            'created_at' => $now,
            'updated_at' => $now,
        ])->all();

        DB::table('court_numbers')->insertOrIgnore($rows);
    }
}
