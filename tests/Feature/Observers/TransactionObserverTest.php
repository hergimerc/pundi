<?php

namespace Tests\Feature\Observers;

use App\Enums\TransactionType;
use App\Models\Account;
use App\Models\Transaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TransactionObserverTest extends TestCase
{
    use RefreshDatabase;

    public function test_expense_decreases_account_balance(): void
    {
        $account = Account::factory()->create(['current_balance' => 1_000_000]);

        Transaction::factory()->expense()->forAccount($account)->create(['amount' => 150_000]);

        $this->assertSame('850000.00', $account->fresh()->current_balance);
    }

    public function test_income_increases_account_balance(): void
    {
        $account = Account::factory()->create(['current_balance' => 1_000_000]);

        Transaction::factory()->income()->forAccount($account)->create(['amount' => 500_000]);

        $this->assertSame('1500000.00', $account->fresh()->current_balance);
    }

    public function test_updating_amount_adjusts_balance(): void
    {
        $account = Account::factory()->create(['current_balance' => 1_000_000]);
        $transaction = Transaction::factory()->expense()->forAccount($account)->create(['amount' => 100_000]);

        // Balance is now 900_000
        $transaction->update(['amount' => 200_000]);

        // Observer reverses 100_000 expense (+100_000) then applies 200_000 expense (-200_000)
        $this->assertSame('800000.00', $account->fresh()->current_balance);
    }

    public function test_updating_type_from_expense_to_income_adjusts_balance(): void
    {
        $account = Account::factory()->create(['current_balance' => 1_000_000]);
        $transaction = Transaction::factory()->expense()->forAccount($account)->create(['amount' => 200_000]);

        // Balance is now 800_000
        $transaction->update(['type' => TransactionType::Income]);

        // Reverses 200_000 expense (+200_000) then applies 200_000 income (+200_000)
        $this->assertSame('1200000.00', $account->fresh()->current_balance);
    }

    public function test_updating_account_moves_balance_between_accounts(): void
    {
        $accountA = Account::factory()->create(['current_balance' => 1_000_000]);
        $accountB = Account::factory()->create(['current_balance' => 500_000]);

        $transaction = Transaction::factory()->expense()->forAccount($accountA)->create(['amount' => 100_000]);

        // accountA is now 900_000
        $transaction->update(['account_id' => $accountB->id]);

        // Reverses expense on A (+100_000), applies expense on B (-100_000)
        $this->assertSame('1000000.00', $accountA->fresh()->current_balance);
        $this->assertSame('400000.00', $accountB->fresh()->current_balance);
    }

    public function test_soft_deleting_reverses_balance(): void
    {
        $account = Account::factory()->create(['current_balance' => 1_000_000]);
        $transaction = Transaction::factory()->expense()->forAccount($account)->create(['amount' => 300_000]);

        // Balance is now 700_000
        $transaction->delete();

        $this->assertSame('1000000.00', $account->fresh()->current_balance);
    }

    public function test_restoring_reapplies_balance(): void
    {
        $account = Account::factory()->create(['current_balance' => 1_000_000]);
        $transaction = Transaction::factory()->expense()->forAccount($account)->create(['amount' => 300_000]);

        $transaction->delete();
        $transaction->restore();

        $this->assertSame('700000.00', $account->fresh()->current_balance);
    }

    public function test_balance_can_go_negative_for_credit_card(): void
    {
        $account = Account::factory()->creditCard()->create(['current_balance' => 0]);

        Transaction::factory()->expense()->forAccount($account)->create(['amount' => 500_000]);

        $this->assertSame('-500000.00', $account->fresh()->current_balance);
    }
}
