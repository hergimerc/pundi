<?php

namespace Database\Factories;

use App\Models\Account;
use App\Models\Transfer;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Transfer>
 */
class TransferFactory extends Factory
{
    public function definition(): array
    {
        return [
            'from_account_id' => Account::factory(),
            'to_account_id' => Account::factory(),
            'amount' => $this->faker->randomFloat(2, 10_000, 5_000_000),
            'fee' => $this->faker->randomFloat(2, 0, 50_000),
            'note' => $this->faker->optional()->sentence(),
            'transferred_at' => $this->faker->dateTimeBetween('-6 months', 'now'),
        ];
    }

    public function between(Account $from, Account $to): static
    {
        return $this->state([
            'from_account_id' => $from->id,
            'to_account_id' => $to->id,
        ]);
    }
}
