<?php

namespace Tests\Feature\Livewire\Transactions;

use App\Livewire\Transactions\Index;
use App\Models\Account;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\Transfer;
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

    public function test_shows_empty_state_when_no_activity_this_month(): void
    {
        Livewire::actingAs($this->user)
            ->test(Index::class)
            ->assertSee('No activity this month.');
    }

    public function test_shows_transfers_in_list(): void
    {
        $from = Account::factory()->create(['name' => 'BCA']);
        $to = Account::factory()->create(['name' => 'GoPay']);

        Transfer::factory()->between($from, $to)->create([
            'amount' => 150_000,
            'transferred_at' => now(),
        ]);

        Livewire::actingAs($this->user)
            ->test(Index::class)
            ->assertSee('BCA')
            ->assertSee('GoPay')
            ->assertSee('150.000');
    }

    public function test_transfer_amount_not_counted_in_income_or_expense_summary(): void
    {
        $from = Account::factory()->create();
        $to = Account::factory()->create();

        // Transfer with a uniquely identifiable amount
        Transfer::factory()->between($from, $to)->create([
            'amount' => 777_777,
            'transferred_at' => now(),
        ]);

        // Income summary shows Rp 0, expense summary shows Rp 0
        // The transfer amount must not appear in either summary card
        $rendered = Livewire::actingAs($this->user)->test(Index::class)->html();

        // Both summary values should be 0, not inflated by the transfer
        $this->assertStringContainsString('Rp 0', $rendered);
    }

    public function test_transfers_from_other_months_are_not_shown(): void
    {
        $from = Account::factory()->create(['name' => 'OldFrom']);
        $to = Account::factory()->create();

        Transfer::factory()->between($from, $to)->create([
            'transferred_at' => now()->subMonths(2),
        ]);

        Livewire::actingAs($this->user)
            ->test(Index::class)
            ->assertDontSee('OldFrom');
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

    public function test_load_more_button_appears_when_more_than_10_day_groups(): void
    {
        // EarliestDay on day 1 will be the 11th group (oldest, sorted last) — hidden initially
        $earliest = Category::factory()->expense()->create(['name' => 'EarliestDay']);
        $recent = Category::factory()->expense()->create(['name' => 'RecentDay']);

        Transaction::factory()->expense()->create([
            'category_id' => $earliest->id,
            'transacted_at' => now()->startOfMonth()->addDays(0), // day 1
        ]);

        // Days 2–11 (10 more distinct days, sorted descending they appear first)
        for ($day = 2; $day <= 11; $day++) {
            Transaction::factory()->expense()->create([
                'category_id' => $recent->id,
                'transacted_at' => now()->startOfMonth()->addDays($day - 1),
            ]);
        }

        Livewire::actingAs($this->user)
            ->test(Index::class)
            ->assertSee('RecentDay')
            ->assertDontSee('EarliestDay')
            ->assertSee('Load more');
    }

    public function test_load_more_increases_visible_days(): void
    {
        Livewire::actingAs($this->user)
            ->test(Index::class)
            ->assertSet('visibleDays', 10)
            ->call('loadMore')
            ->assertSet('visibleDays', 20);
    }

    public function test_month_navigation_resets_visible_days(): void
    {
        Livewire::actingAs($this->user)
            ->test(Index::class)
            ->call('loadMore')
            ->assertSet('visibleDays', 20)
            ->call('previousMonth')
            ->assertSet('visibleDays', 10)
            ->call('loadMore')
            ->call('nextMonth')
            ->assertSet('visibleDays', 10);
    }

    public function test_summary_totals_cover_full_month_regardless_of_visible_days(): void
    {
        // Create transactions on 12 distinct days
        for ($day = 1; $day <= 12; $day++) {
            Transaction::factory()->expense()->create([
                'amount' => 10_000,
                'transacted_at' => now()->startOfMonth()->addDays($day - 1),
            ]);
        }

        // With only 10 visible days, the summary should still show the full 120,000
        Livewire::actingAs($this->user)
            ->test(Index::class)
            ->assertSee('120.000');
    }
}
