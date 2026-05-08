<?php

namespace Database\Factories;

use App\Enums\TransactionType;
use App\Models\Account;
use App\Models\Category;
use App\Models\Transaction;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Transaction>
 */
class TransactionFactory extends Factory
{
    public function definition(): array
    {
        $type = $this->faker->randomElement(TransactionType::cases());

        return [
            'type' => $type,
            'account_id' => Account::factory(),
            'category_id' => Category::factory()->{$type === TransactionType::Expense ? 'expense' : 'income'}(),
            'note_id' => null,
            'amount' => $this->faker->randomFloat(2, 1000, 1_000_000),
            'description' => $this->faker->optional()->sentence(),
            'transacted_at' => $this->faker->dateTimeBetween('-6 months', 'now'),
        ];
    }

    public function expense(): static
    {
        return $this->state([
            'type' => TransactionType::Expense,
            'category_id' => Category::factory()->expense(),
        ]);
    }

    public function income(): static
    {
        return $this->state([
            'type' => TransactionType::Income,
            'category_id' => Category::factory()->income(),
        ]);
    }

    public function forAccount(Account $account): static
    {
        return $this->state(['account_id' => $account->id]);
    }
}
