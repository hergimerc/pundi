<?php

namespace Database\Seeders;

use App\Enums\CategoryType;
use App\Models\Category;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $expense = CategoryType::Expense;
        $income = CategoryType::Income;

        $categories = [
            // Expense categories
            ['name' => 'Food & Drink', 'type' => $expense, 'color' => '#f97316', 'children' => [
                ['name' => 'Groceries', 'type' => $expense, 'color' => '#fb923c'],
                ['name' => 'Restaurants', 'type' => $expense, 'color' => '#fdba74'],
                ['name' => 'Coffee', 'type' => $expense, 'color' => '#fed7aa'],
            ]],
            ['name' => 'Transport', 'type' => $expense, 'color' => '#3b82f6', 'children' => [
                ['name' => 'Fuel', 'type' => $expense, 'color' => '#60a5fa'],
                ['name' => 'Parking', 'type' => $expense, 'color' => '#93c5fd'],
                ['name' => 'Public Transit', 'type' => $expense, 'color' => '#bfdbfe'],
            ]],
            ['name' => 'Shopping', 'type' => $expense, 'color' => '#ec4899', 'children' => [
                ['name' => 'Clothing', 'type' => $expense, 'color' => '#f472b6'],
                ['name' => 'Electronics', 'type' => $expense, 'color' => '#f9a8d4'],
            ]],
            ['name' => 'Bills & Utilities', 'type' => $expense, 'color' => '#8b5cf6', 'children' => [
                ['name' => 'Electricity', 'type' => $expense, 'color' => '#a78bfa'],
                ['name' => 'Internet', 'type' => $expense, 'color' => '#c4b5fd'],
                ['name' => 'Phone', 'type' => $expense, 'color' => '#ddd6fe'],
            ]],
            ['name' => 'Health', 'type' => $expense, 'color' => '#10b981', 'children' => [
                ['name' => 'Medicine', 'type' => $expense, 'color' => '#34d399'],
                ['name' => 'Doctor', 'type' => $expense, 'color' => '#6ee7b7'],
            ]],
            ['name' => 'Entertainment', 'type' => $expense, 'color' => '#f59e0b', 'children' => [
                ['name' => 'Streaming', 'type' => $expense, 'color' => '#fbbf24'],
                ['name' => 'Games', 'type' => $expense, 'color' => '#fcd34d'],
            ]],
            ['name' => 'Education', 'type' => $expense, 'color' => '#06b6d4'],
            ['name' => 'Personal Care', 'type' => $expense, 'color' => '#d946ef'],
            ['name' => 'Other', 'type' => $expense, 'color' => '#71717a'],

            // Income categories
            ['name' => 'Salary', 'type' => $income, 'color' => '#22c55e'],
            ['name' => 'Freelance', 'type' => $income, 'color' => '#16a34a'],
            ['name' => 'Investment', 'type' => $income, 'color' => '#15803d', 'children' => [
                ['name' => 'Dividends', 'type' => $income, 'color' => '#166534'],
                ['name' => 'Interest', 'type' => $income, 'color' => '#14532d'],
            ]],
            ['name' => 'Gift', 'type' => $income, 'color' => '#84cc16'],
            ['name' => 'Other Income', 'type' => $income, 'color' => '#a3a3a3'],
        ];

        foreach ($categories as $order => $data) {
            $children = $data['children'] ?? [];
            unset($data['children']);

            $parent = Category::create(array_merge($data, ['sort_order' => $order]));

            foreach ($children as $childOrder => $childData) {
                Category::create(array_merge($childData, [
                    'parent_id' => $parent->id,
                    'sort_order' => $childOrder,
                ]));
            }
        }
    }
}
