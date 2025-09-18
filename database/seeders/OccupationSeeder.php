<?php

namespace Database\Seeders;

use App\Models\Occupation;
use Illuminate\Database\Seeder;

class OccupationSeeder extends Seeder
{
    public function run(): void
    {
        $occupations = [
            [
                'title' => 'Advogado Sênior',
                'description' => 'Advogado com experiência em múltiplas áreas',
                'base_salary' => 12000.00,
                'active' => true
            ],
            [
                'title' => 'Advogado Pleno',
                'description' => 'Advogado com experiência intermediária',
                'base_salary' => 8000.00,
                'active' => true
            ],
            [
                'title' => 'Advogado Júnior',
                'description' => 'Advogado em início de carreira',
                'base_salary' => 6000.00,
                'active' => true
            ],
            [
                'title' => 'Assistente Jurídico',
                'description' => 'Auxilia nas atividades jurídicas',
                'base_salary' => 3000.00,
                'active' => true
            ],
            [
                'title' => 'Secretária Jurídica',
                'description' => 'Atividades administrativas especializadas',
                'base_salary' => 2800.00,
                'active' => true
            ],
            [
                'title' => 'Paralegal',
                'description' => 'Suporte paralegal especializado',
                'base_salary' => 4000.00,
                'active' => true
            ],
            [
                'title' => 'Estagiário de Direito',
                'description' => 'Estudante de direito em estágio',
                'base_salary' => 1200.00,
                'active' => true
            ]
        ];

        foreach ($occupations as $occupation) {
            Occupation::create($occupation);
        }
    }
}