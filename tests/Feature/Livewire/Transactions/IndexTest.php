<?php

namespace Tests\Feature\Livewire\Transactions;

use App\Livewire\Transactions\Index;
use App\Models\Account;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class IndexTest extends TestCase
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
        $this->get(route('transactions.index'))->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_view_transactions_page(): void
    {
        $this->actingAs($this->user)
            ->get(route('transactions.index'))
            ->assertOk();
    }

    public function test_shows_empty_state_when_no_transactions_this_month(): void
    {
        Livewire::actingAs($this->user)
            ->test(Index::class)
            ->assertSee('No transactions this month.');
    }

    public function test_shows_transactions_for_current_month(): void
    {
        $account = Account::factory()->create();
        $category = Category::factory()->expense()->create(['name' => 'Food']);

        Transaction::factory()->expense()->forAccount($account)->create([
            'category_id' => $category->id,
            'amount' => 50_000,
            'transacted_at' => now(),
        ]);

        Livewire::actingAs($this->user)
            ->test(Index::class)
            ->assertSee('Food')
            ->assertSee('50.000');
    }

    public function test_does_not_show_transactions_from_other_months(): void
    {
        $account = Account::factory()->create();
        $category = Category::factory()->expense()->create(['name' => 'OldCategory']);

        Transaction::factory()->expense()->forAccount($account)->create([
            'category_id' => $category->id,
            'transacted_at' => now()->subMonths(2),
        ]);

        Livewire::actingAs($this->user)
            ->test(Index::class)
            ->assertDontSee('OldCategory');
    }

    public function test_income_shown_in_summary(): void
    {
        Transaction::factory()->income()->create([
            'amount' => 5_000_000,
            'transacted_at' => now(),
        ]);

        Livewire::actingAs($this->user)
            ->test(Index::class)
            ->assertSee('5.000.000');
    }

    public function test_expense_shown_in_summary(): void
    {
        Transaction::factory()->expense()->create([
            'amount' => 200_000,
            'transacted_at' => now(),
        ]);

        Livewire::actingAs($this->user)
            ->test(Index::class)
            ->assertSee('200.000');
    }

    public function test_previous_month_navigation_shows_older_transactions(): void
    {
        $account = Account::factory()->create();
        $category = Category::factory()->expense()->create(['name' => 'LastMonthItem']);

        Transaction::factory()->expense()->forAccount($account)->create([
            'category_id' => $category->id,
            'transacted_at' => now()->subMonth(),
        ]);

        Livewire::actingAs($this->user)
            ->test(Index::class)
            ->assertDontSee('LastMonthItem')
            ->call('previousMonth')
            ->assertSee('LastMonthItem');
    }

    public function test_next_month_navigation_hides_current_month_transactions(): void
    {
        $category = Category::factory()->expense()->create(['name' => 'ThisMonth']);

        Transaction::factory()->expense()->create([
            'category_id' => $category->id,
            'transacted_at' => now(),
        ]);

        Livewire::actingAs($this->user)
            ->test(Index::class)
            ->assertSee('ThisMonth')
            ->call('nextMonth')
            ->assertDontSee('ThisMonth');
    }

    public function test_income_amount_is_positive_prefixed(): void
    {
        Transaction::factory()->income()->create([
            'amount' => 100_000,
            'transacted_at' => now(),
        ]);

        Livewire::actingAs($this->user)
            ->test(Index::class)
            ->assertSee('+Rp');
    }

    public function test_expense_amount_is_negative_prefixed(): void
    {
        Transaction::factory()->expense()->create([
            'amount' => 100_000,
            'transacted_at' => now(),
        ]);

        Livewire::actingAs($this->user)
            ->test(Index::class)
            ->assertSee('-Rp');
    }
}
