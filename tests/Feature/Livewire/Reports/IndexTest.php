<?php

namespace Tests\Feature\Livewire\Reports;

use App\Enums\TransactionType;
use App\Livewire\Reports\Index;
use App\Models\Account;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use App\Services\ReportService;
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
        $this->get(route('reports.index'))->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_view_reports_page(): void
    {
        $this->actingAs($this->user)
            ->get(route('reports.index'))
            ->assertOk();
    }

    public function test_shows_empty_state_when_no_transactions(): void
    {
        Livewire::actingAs($this->user)
            ->test(Index::class)
            ->assertSee('No transactions this month.');
    }

    public function test_defaults_to_current_month(): void
    {
        Livewire::actingAs($this->user)
            ->test(Index::class)
            ->assertSet('currentMonth', now()->format('Y-m'));
    }

    public function test_can_navigate_to_previous_month(): void
    {
        Livewire::actingAs($this->user)
            ->test(Index::class)
            ->call('previousMonth')
            ->assertSet('currentMonth', now()->subMonth()->format('Y-m'));
    }

    public function test_can_navigate_to_next_month(): void
    {
        Livewire::actingAs($this->user)
            ->test(Index::class)
            ->call('nextMonth')
            ->assertSet('currentMonth', now()->addMonth()->format('Y-m'));
    }

    public function test_shows_income_and_expense_summary(): void
    {
        $account = Account::factory()->create();
        $category = Category::factory()->expense()->create();

        Transaction::factory()->create([
            'type' => TransactionType::Income,
            'amount' => 5000000,
            'account_id' => $account->id,
            'category_id' => $category->id,
            'transacted_at' => now(),
        ]);
        Transaction::factory()->create([
            'type' => TransactionType::Expense,
            'amount' => 2000000,
            'account_id' => $account->id,
            'category_id' => $category->id,
            'transacted_at' => now(),
        ]);

        Livewire::actingAs($this->user)
            ->test(Index::class)
            ->assertSee('5.000.000')
            ->assertSee('2.000.000');
    }

    public function test_shows_expense_category_breakdown(): void
    {
        $account = Account::factory()->create();
        $category = Category::factory()->expense()->create(['name' => 'Food']);

        Transaction::factory()->create([
            'type' => TransactionType::Expense,
            'amount' => 300000,
            'account_id' => $account->id,
            'category_id' => $category->id,
            'transacted_at' => now(),
        ]);

        Livewire::actingAs($this->user)
            ->test(Index::class)
            ->assertSee('Food')
            ->assertSee('Expenses by Category');
    }

    public function test_excludes_other_months_from_summary(): void
    {
        $account = Account::factory()->create();
        $category = Category::factory()->expense()->create();

        Transaction::factory()->create([
            'type' => TransactionType::Expense,
            'amount' => 999999,
            'account_id' => $account->id,
            'category_id' => $category->id,
            'transacted_at' => now()->subMonths(2),
        ]);

        $service = new ReportService;
        $summary = $service->monthlySummary(now()->month, now()->year);

        $this->assertEquals(0.0, $summary['expense']);
    }

    // --- ReportService unit tests ---

    public function test_report_service_monthly_summary(): void
    {
        $account = Account::factory()->create();
        $category = Category::factory()->expense()->create();

        Transaction::factory()->create([
            'type' => TransactionType::Income,
            'amount' => 4000000,
            'account_id' => $account->id,
            'category_id' => $category->id,
            'transacted_at' => now(),
        ]);
        Transaction::factory()->create([
            'type' => TransactionType::Expense,
            'amount' => 1500000,
            'account_id' => $account->id,
            'category_id' => $category->id,
            'transacted_at' => now(),
        ]);

        $service = new ReportService;
        $summary = $service->monthlySummary(now()->month, now()->year);

        $this->assertEqualsWithDelta(4000000, $summary['income'], 0.01);
        $this->assertEqualsWithDelta(1500000, $summary['expense'], 0.01);
        $this->assertEqualsWithDelta(2500000, $summary['net'], 0.01);
    }

    public function test_report_service_category_breakdown_sorted_by_amount(): void
    {
        $account = Account::factory()->create();
        $catA = Category::factory()->expense()->create(['name' => 'Transport']);
        $catB = Category::factory()->expense()->create(['name' => 'Food']);

        Transaction::factory()->create([
            'type' => TransactionType::Expense,
            'amount' => 100000,
            'account_id' => $account->id,
            'category_id' => $catA->id,
            'transacted_at' => now(),
        ]);
        Transaction::factory()->create([
            'type' => TransactionType::Expense,
            'amount' => 500000,
            'account_id' => $account->id,
            'category_id' => $catB->id,
            'transacted_at' => now(),
        ]);

        $service = new ReportService;
        $breakdown = $service->categoryBreakdown(now()->month, now()->year, TransactionType::Expense);

        $this->assertEquals('Food', $breakdown->first()['name']);
        $this->assertEqualsWithDelta(500000, $breakdown->first()['amount'], 0.01);
    }

    public function test_report_service_annual_trend_returns_12_months(): void
    {
        $service = new ReportService;
        $trend = $service->annualTrend(now()->year);

        $this->assertCount(12, $trend);
        $this->assertEquals(1, $trend->first()['month']);
        $this->assertEquals(12, $trend->last()['month']);
    }
}
