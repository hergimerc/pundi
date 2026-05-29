<?php

namespace Tests\Feature\Livewire\Budgets;

use App\Enums\TransactionType;
use App\Livewire\Budgets\Index;
use App\Models\Account;
use App\Models\Budget;
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
        $this->get(route('budgets.index'))->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_view_budgets_page(): void
    {
        $this->actingAs($this->user)
            ->get(route('budgets.index'))
            ->assertOk();
    }

    public function test_shows_empty_state_when_no_budgets(): void
    {
        Livewire::actingAs($this->user)
            ->test(Index::class)
            ->assertSee('No budgets for this month.');
    }

    public function test_shows_budgets_for_current_month(): void
    {
        $budget = Budget::factory()->forMonth(now()->month, now()->year)->create(['name' => 'Groceries']);

        Livewire::actingAs($this->user)
            ->test(Index::class)
            ->assertSee('Groceries');
    }

    public function test_does_not_show_budgets_from_other_months(): void
    {
        $budget = Budget::factory()->forMonth(1, 2020)->create(['name' => 'Old Budget']);

        Livewire::actingAs($this->user)
            ->test(Index::class)
            ->assertDontSee('Old Budget');
    }

    public function test_can_navigate_to_previous_month(): void
    {
        $prevMonth = now()->subMonth();
        $budget = Budget::factory()->forMonth($prevMonth->month, $prevMonth->year)->create(['name' => 'Past Budget']);

        Livewire::actingAs($this->user)
            ->test(Index::class)
            ->call('previousMonth')
            ->assertSee('Past Budget');
    }

    public function test_can_navigate_to_next_month(): void
    {
        $nextMonth = now()->addMonth();
        $budget = Budget::factory()->forMonth($nextMonth->month, $nextMonth->year)->create(['name' => 'Future Budget']);

        Livewire::actingAs($this->user)
            ->test(Index::class)
            ->call('nextMonth')
            ->assertSee('Future Budget');
    }

    public function test_can_delete_budget(): void
    {
        $budget = Budget::factory()->forMonth(now()->month, now()->year)->create();

        Livewire::actingAs($this->user)
            ->test(Index::class)
            ->call('delete', $budget->id);

        $this->assertSoftDeleted('budgets', ['id' => $budget->id]);
    }

    public function test_shows_spent_amount_from_linked_categories(): void
    {
        $category = Category::factory()->expense()->create();
        $account = Account::factory()->create(['current_balance' => 1000000]);
        $budget = Budget::factory()->forMonth(now()->month, now()->year)->create(['amount' => 500000]);
        $budget->categories()->attach($category);

        Transaction::factory()->create([
            'type' => TransactionType::Expense,
            'category_id' => $category->id,
            'account_id' => $account->id,
            'amount' => 200000,
            'transacted_at' => now(),
        ]);

        $budget->load('categories.children');

        $this->assertEqualsWithDelta(200000, $budget->spentAmount(), 0.01);
        $this->assertEqualsWithDelta(300000, $budget->remainingAmount(), 0.01);
        $this->assertEqualsWithDelta(40.0, $budget->progressPercent(), 0.01);
    }

    public function test_spent_amount_includes_subcategory_transactions(): void
    {
        $parent = Category::factory()->expense()->create();
        $child = Category::factory()->expense()->childOf($parent)->create();
        $account = Account::factory()->create(['current_balance' => 1000000]);
        $budget = Budget::factory()->forMonth(now()->month, now()->year)->create(['amount' => 1000000]);
        $budget->categories()->attach($parent);

        Transaction::factory()->create([
            'type' => TransactionType::Expense,
            'category_id' => $child->id,
            'account_id' => $account->id,
            'amount' => 300000,
            'transacted_at' => now(),
        ]);

        $budget->load('categories.children');

        $this->assertEqualsWithDelta(300000, $budget->spentAmount(), 0.01);
    }
}
