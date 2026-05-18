<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeederSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->call(PowerOfAttorneyTemplateSeeder::class);
        $this->call(EnterprisePowerOfAttorneyTemplateSeeder::class);
        $this->call(EnterpriseFeeAgreementTemplateSeeder::class);

    }
}
