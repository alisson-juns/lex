<?php

namespace Database\Factories;

use App\Enums\CaseStatus;
use App\Models\LegalCase;
use Illuminate\Database\Eloquent\Factories\Factory;

class LegalCaseFactory extends Factory
{
    protected $model = LegalCase::class;

    public function definition(): array
    {
        return [
            'folder_number' => (string) $this->faker->unique()->numberBetween(1, 99999),
            'case_number'   => $this->faker->numerify('#######-##.####.8.26.####'),
            'status'        => CaseStatus::cases()[0],
            'note'          => null,
        ];
    }
}
