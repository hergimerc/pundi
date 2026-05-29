<?php

namespace Tests\Feature\Livewire;

use App\Livewire\Accounts\Create as AccountCreate;
use App\Livewire\Accounts\Edit as AccountEdit;
use App\Livewire\Budgets\Create as BudgetCreate;
use App\Livewire\Budgets\Edit as BudgetEdit;
use App\Livewire\Budgets\Index as BudgetIndex;
use App\Livewire\Categories\Create as CategoryCreate;
use App\Livewire\Categories\Edit as CategoryEdit;
use App\Livewire\Categories\Index as CategoryIndex;
use App\Livewire\Transactions\Create as TransactionCreate;
use App\Livewire\Transactions\Edit as TransactionEdit;
use App\Livewire\Transactions\Show as TransactionShow;
use App\Livewire\Transfers\Show as TransferShow;
use App\Models\Account;
use App\Models\Budget;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\Transfer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class FlashMessageTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_account_create_dispatches_flash(): void
    {
        Livewire::actingAs($this->user)
            ->test(AccountCreate::class)
            ->set('name', 'Wallet')
            ->set('type', 'cash')
            ->set('initial_balance', '0')
            ->call('save')
            ->assertDispatched('flash');
    }

    public function test_account_edit_dispatches_flash(): void
    {
        $account = Account::factory()->create();

        Livewire::actingAs($this->user)
            ->test(AccountEdit::class, ['account' => $account])
            ->set('name', 'Updated')
            ->call('save')
            ->assertDispatched('flash');
    }

    public function test_category_create_dispatches_flash(): void
    {
        Livewire::actingAs($this->user)
            ->test(CategoryCreate::class)
            ->set('name', 'Food')
            ->set('type', 'expense')
            ->call('save')
            ->assertDispatched('flash');
    }

    public function test_category_edit_dispatches_flash(): void
    {
        $category = Category::factory()->expense()->create();

        Livewire::actingAs($this->user)
            ->test(CategoryEdit::class, ['category' => $category])
            ->set('name', 'Updated')
            ->call('save')
            ->assertDispatched('flash');
    }

    public function test_category_delete_dispatches_flash(): void
    {
        $category = Category::factory()->expense()->create();

        Livewire::actingAs($this->user)
            ->test(CategoryIndex::class)
            ->call('delete', $category->id)
            ->assertDispatched('flash');
    }

    public function test_budget_create_dispatches_flash(): void
    {
        Livewire::actingAs($this->user)
            ->test(BudgetCreate::class)
            ->set('name', 'Groceries')
            ->set('amount', '500000')
            ->set('month', (int) now()->month)
            ->set('year', (int) now()->year)
            ->call('save')
            ->assertDispatched('flash');
    }

    public function test_budget_edit_dispatches_flash(): void
    {
        $budget = Budget::factory()->forMonth(now()->month, now()->year)->create();

        Livewire::actingAs($this->user)
            ->test(BudgetEdit::class, ['budget' => $budget])
            ->set('name', 'Updated')
            ->call('save')
            ->assertDispatched('flash');
    }

    public function test_budget_delete_dispatches_flash(): void
    {
        $budget = Budget::factory()->forMonth(now()->month, now()->year)->create();

        Livewire::actingAs($this->user)
            ->test(BudgetIndex::class)
            ->call('delete', $budget->id)
            ->assertDispatched('flash');
    }

    public function test_transaction_create_dispatches_flash(): void
    {
        $account = Account::factory()->create();
        $category = Category::factory()->expense()->create();

        Livewire::actingAs($this->user)
            ->test(TransactionCreate::class)
            ->set('type', 'expense')
            ->set('amount', '50000')
            ->set('account_id', (string) $account->id)
            ->set('category_id', (string) $category->id)
            ->set('transacted_at', now()->format('Y-m-d\TH:i'))
            ->call('save')
            ->assertDispatched('flash');
    }

    public function test_transaction_edit_dispatches_flash(): void
    {
        $account = Account::factory()->create();
        $category = Category::factory()->expense()->create();
        $transaction = Transaction::factory()->expense()->forAccount($account)->create([
            'category_id' => $category->id,
        ]);

        Livewire::actingAs($this->user)
            ->test(TransactionEdit::class, ['transaction' => $transaction])
            ->call('save')
            ->assertDispatched('flash');
    }

    public function test_transaction_delete_dispatches_flash(): void
    {
        $account = Account::factory()->create();
        $category = Category::factory()->expense()->create();
        $transaction = Transaction::factory()->expense()->forAccount($account)->create([
            'category_id' => $category->id,
        ]);

        Livewire::actingAs($this->user)
            ->test(TransactionShow::class, ['transaction' => $transaction])
            ->call('delete')
            ->assertDispatched('flash');
    }

    public function test_transfer_delete_dispatches_flash(): void
    {
        $from = Account::factory()->create(['current_balance' => 500000]);
        $to = Account::factory()->create();
        $transfer = Transfer::factory()->between($from, $to)->create([
            'amount' => 100000,
            'transferred_at' => now(),
        ]);

        Livewire::actingAs($this->user)
            ->test(TransferShow::class, ['transfer' => $transfer])
            ->call('delete')
            ->assertDispatched('flash');
    }
}
