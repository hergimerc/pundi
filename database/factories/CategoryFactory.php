<?php

namespace Database\Factories;

use App\Enums\CategoryType;
use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Category>
 */
class CategoryFactory extends Factory
{
    public function definition(): array
    {
        return [
            'parent_id' => null,
            'name' => $this->faker->word(),
            'type' => $this->faker->randomElement(CategoryType::cases()),
            'color' => $this->faker->hexColor(),
            'icon' => null,
            'sort_order' => 0,
        ];
    }

    public function expense(): static
    {
        return $this->state(['type' => CategoryType::Expense]);
    }

    public function income(): static
    {
        return $this->state(['type' => CategoryType::Income]);
    }

    public function childOf(Category $parent): static
    {
        return $this->state([
            'parent_id' => $parent->id,
            'type' => $parent->type,
        ]);
    }
}
