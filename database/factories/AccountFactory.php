<?php

namespace Database\Factories;

use App\Enums\AccountType;
use App\Models\Account;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Account>
 */
class AccountFactory extends Factory
{
    public function definition(): array
    {
        $initial = $this->faker->randomFloat(2, 0, 10_000_000);

        return [
            'name' => $this->faker->words(2, true),
            'type' => $this->faker->randomElement(AccountType::cases()),
            'initial_balance' => $initial,
            'current_balance' => $initial,
            'color' => $this->faker->hexColor(),
            'icon' => null,
            'is_active' => true,
        ];
    }

    public function cash(): static
    {
        return $this->state(['type' => AccountType::Cash]);
    }

    public function bankAccount(): static
    {
        return $this->state(['type' => AccountType::BankAccount]);
    }

    public function creditCard(): static
    {
        return $this->state(['type' => AccountType::CreditCard]);
    }

    public function inactive(): static
    {
        return $this->state(['is_active' => false]);
    }
}
