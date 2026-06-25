<?php

namespace Database\Factories;

use App\Enums\DeadlineStatus;
use App\Enums\DeadlineType;
use App\Models\Deadline;
use App\Models\LegalCase;
use Illuminate\Database\Eloquent\Factories\Factory;

class DeadlineFactory extends Factory
{
    protected $model = Deadline::class;

    public function definition(): array
    {
        return [
            'legal_case_id' => LegalCase::factory(),
            'deadline_type' => DeadlineType::Contestacao,
            'fatal_date'    => now()->addDays(10),
            'internal_date' => null,
            'status'        => DeadlineStatus::Pending,
            'note'          => null,
        ];
    }
}
