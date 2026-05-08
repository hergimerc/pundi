<?php

namespace Database\Seeders;

use App\Enums\CategoryType;
use App\Enums\TransactionType;
use App\Models\Account;
use App\Models\Category;
use App\Models\Note;
use App\Models\Transaction;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TransactionSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $accounts = Account::all();
        $expenseCategories = Category::ofType(CategoryType::Expense)->get();
        $incomeCategories = Category::ofType(CategoryType::Income)->get();

        $salaryNote = Note::firstOrCreate(['content' => 'Monthly salary']);
        $freelanceNote = Note::firstOrCreate(['content' => 'Freelance project']);

        // Seed 3 months of transactions
        for ($monthOffset = 2; $monthOffset >= 0; $monthOffset--) {
            $month = now()->subMonths($monthOffset);

            // Income: salary at start of each month
            Transaction::create([
                'type' => TransactionType::Income,
                'account_id' => $accounts->firstWhere('name', 'BCA')->id ?? $accounts->first()->id,
                'category_id' => $incomeCategories->firstWhere('name', 'Salary')->id ?? $incomeCategories->first()->id,
                'note_id' => $salaryNote->id,
                'amount' => 8_000_000,
                'description' => null,
                'transacted_at' => $month->copy()->startOfMonth()->addDay(),
            ]);

            // Freelance income mid-month (not every month)
            if ($monthOffset === 1) {
                Transaction::create([
                    'type' => TransactionType::Income,
                    'account_id' => $accounts->firstWhere('name', 'BCA')->id ?? $accounts->first()->id,
                    'category_id' => $incomeCategories->firstWhere('name', 'Freelance')->id ?? $incomeCategories->first()->id,
                    'note_id' => $freelanceNote->id,
                    'amount' => 2_500_000,
                    'description' => null,
                    'transacted_at' => $month->copy()->startOfMonth()->addDays(14),
                ]);
            }

            // Daily expenses throughout the month
            $days = $monthOffset === 0 ? now()->day : $month->daysInMonth;
            for ($day = 1; $day <= $days; $day += rand(1, 3)) {
                $account = $accounts->random();
                $category = $expenseCategories->random();

                Transaction::create([
                    'type' => TransactionType::Expense,
                    'account_id' => $account->id,
                    'category_id' => $category->id,
                    'note_id' => null,
                    'amount' => rand(10_000, 300_000),
                    'description' => null,
                    'transacted_at' => $month->copy()->startOfMonth()->addDays($day - 1),
                ]);
            }
        }
    }
}
