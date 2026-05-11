<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\Transfer;
use Illuminate\Database\Seeder;

class TransferSeeder extends Seeder
{
    public function run(): void
    {
        $accounts = Account::all();

        if ($accounts->count() < 2) {
            return;
        }

        for ($i = 0; $i < 10; $i++) {
            [$from, $to] = $accounts->random(2)->values();

            Transfer::factory()->between($from, $to)->create();
        }
    }
}
