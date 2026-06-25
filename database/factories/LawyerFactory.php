<?php

namespace Database\Factories;

use App\Models\Lawyer;
use Illuminate\Database\Eloquent\Factories\Factory;

class LawyerFactory extends Factory
{
    protected $model = Lawyer::class;

    public function definition(): array
    {
        return [
            'user_id'   => null,
            'name'      => $this->faker->name(),
            'oab'       => (string) $this->faker->numberBetween(10000, 999999),
            'oab_state' => 'SP',
            'active'    => true,
        ];
    }
}
