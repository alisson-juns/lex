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
        $this->call(CourtNameSeeder::class);
        $this->call(CourtNumberSeeder::class);
        $this->call(FeeAgreementTemplateSeeder::class);
        $this->call(GratuityDeclarationTemplateSeeder::class);
        $this->call(RolesAndPermissionsSeeder::class);

    }
}
