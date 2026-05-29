<?php

namespace Database\Factories;

use App\Models\Budget;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Budget>
 */
class BudgetFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => $this->faker->words(2, true),
            'amount' => $this->faker->numberBetween(100000, 5000000),
            'month' => $this->faker->numberBetween(1, 12),
            'year' => $this->faker->numberBetween(2024, 2026),
        ];
    }

    public function forMonth(int $month, int $year): static
    {
        return $this->state(['month' => $month, 'year' => $year]);
    }
}
