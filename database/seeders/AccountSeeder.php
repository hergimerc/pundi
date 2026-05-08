<?php

namespace Database\Seeders;

use App\Enums\AccountType;
use App\Models\Account;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AccountSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $accounts = [
            [
                'name' => 'Cash',
                'type' => AccountType::Cash,
                'initial_balance' => 500_000,
                'current_balance' => 500_000,
                'color' => '#22c55e',
                'icon' => null,
                'is_active' => true,
            ],
            [
                'name' => 'BCA',
                'type' => AccountType::BankAccount,
                'initial_balance' => 5_000_000,
                'current_balance' => 5_000_000,
                'color' => '#3b82f6',
                'icon' => null,
                'is_active' => true,
            ],
            [
                'name' => 'BCA Credit Card',
                'type' => AccountType::CreditCard,
                'initial_balance' => 0,
                'current_balance' => 0,
                'color' => '#f59e0b',
                'icon' => null,
                'is_active' => true,
            ],
        ];

        foreach ($accounts as $account) {
            Account::create($account);
        }
    }
}
