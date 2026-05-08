<?php

namespace Tests\Feature\Livewire\Accounts;

use App\Livewire\Accounts\Show;
use App\Models\Account;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ShowTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_redirects_guests_to_login(): void
    {
        $account = Account::factory()->create();

        $this->get(route('accounts.show', $account))->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_view_show_page(): void
    {
        $account = Account::factory()->create();

        $this->actingAs($this->user)
            ->get(route('accounts.show', $account))
            ->assertOk();
    }

    public function test_shows_account_name_type_and_balance(): void
    {
        $account = Account::factory()->bankAccount()->create([
            'name' => 'BCA Savings',
            'current_balance' => 2_500_000,
        ]);

        Livewire::actingAs($this->user)
            ->test(Show::class, ['account' => $account])
            ->assertSee('BCA Savings')
            ->assertSee('Bank Account')
            ->assertSee('2.500.000');
    }

    public function test_shows_inactive_badge_for_inactive_account(): void
    {
        $account = Account::factory()->create(['is_active' => false]);

        Livewire::actingAs($this->user)
            ->test(Show::class, ['account' => $account])
            ->assertSee('Inactive');
    }

    public function test_shows_transactions_grouped_by_date(): void
    {
        $account = Account::factory()->create();
        $category = Category::factory()->expense()->create(['name' => 'Food']);

        Transaction::factory()->create([
            'account_id' => $account->id,
            'category_id' => $category->id,
            'type' => 'expense',
            'amount' => 50_000,
            'transacted_at' => now(),
        ]);

        Livewire::actingAs($this->user)
            ->test(Show::class, ['account' => $account])
            ->assertSee('Food')
            ->assertSee('50.000');
    }

    public function test_only_shows_transactions_for_this_account(): void
    {
        $account = Account::factory()->create();
        $otherAccount = Account::factory()->create();
        $category = Category::factory()->expense()->create();

        Transaction::factory()->create([
            'account_id' => $account->id,
            'category_id' => $category->id,
            'amount' => 100_000,
        ]);
        Transaction::factory()->create([
            'account_id' => $otherAccount->id,
            'category_id' => $category->id,
            'amount' => 999_000,
        ]);

        Livewire::actingAs($this->user)
            ->test(Show::class, ['account' => $account])
            ->assertSee('100.000')
            ->assertDontSee('999.000');
    }

    public function test_shows_income_and_expense_totals(): void
    {
        $account = Account::factory()->create(['current_balance' => 0]);
        $expenseCategory = Category::factory()->expense()->create();
        $incomeCategory = Category::factory()->income()->create();

        Transaction::factory()->create([
            'account_id' => $account->id,
            'category_id' => $incomeCategory->id,
            'type' => 'income',
            'amount' => 3_000_000,
        ]);
        Transaction::factory()->create([
            'account_id' => $account->id,
            'category_id' => $expenseCategory->id,
            'type' => 'expense',
            'amount' => 500_000,
        ]);

        Livewire::actingAs($this->user)
            ->test(Show::class, ['account' => $account])
            ->assertSee('3.000.000')
            ->assertSee('500.000');
    }

    public function test_shows_empty_state_when_no_transactions(): void
    {
        $account = Account::factory()->create();

        Livewire::actingAs($this->user)
            ->test(Show::class, ['account' => $account])
            ->assertSee('No transactions for this account.');
    }
}
