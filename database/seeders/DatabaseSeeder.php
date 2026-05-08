<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'Eric',
            'email' => 'hzsn@hzsn.my.id',
            'password' => bcrypt('password'),
        ]);

        $this->call(AccountSeeder::class);
        $this->call(CategorySeeder::class);
        $this->call(TransactionSeeder::class);
    }
}
