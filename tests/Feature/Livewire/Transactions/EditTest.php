<?php

namespace Tests\Feature\Livewire\Transactions;

use App\Livewire\Transactions\Edit;
use App\Models\Account;
use App\Models\Category;
use App\Models\Note;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class EditTest extends TestCase
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
        $transaction = Transaction::factory()->create();

        $this->get(route('transactions.edit', $transaction))->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_view_edit_page(): void
    {
        $transaction = Transaction::factory()->create();

        $this->actingAs($this->user)
            ->get(route('transactions.edit', $transaction))
            ->assertOk();
    }

    public function test_form_is_pre_filled_with_transaction_data(): void
    {
        $account = Account::factory()->create();
        $category = Category::factory()->expense()->create();
        $note = Note::firstOrCreate(['content' => 'Groceries']);
        $transaction = Transaction::factory()->create([
            'type' => 'expense',
            'amount' => 75000,
            'account_id' => $account->id,
            'category_id' => $category->id,
            'note_id' => $note->id,
            'description' => 'Weekly shop',
        ]);

        Livewire::actingAs($this->user)
            ->test(Edit::class, ['transaction' => $transaction])
            ->assertSet('type', 'expense')
            ->assertSet('amount', '75000')
            ->assertSet('account_id', (string) $account->id)
            ->assertSet('category_id', (string) $category->id)
            ->assertSet('note', 'Groceries')
            ->assertSet('description', 'Weekly shop');
    }

    public function test_updates_transaction_and_redirects(): void
    {
        $account = Account::factory()->create();
        $category = Category::factory()->expense()->create();
        $transaction = Transaction::factory()->create([
            'type' => 'expense',
            'amount' => 50000,
            'account_id' => $account->id,
            'category_id' => $category->id,
        ]);

        $newCategory = Category::factory()->expense()->create();

        Livewire::actingAs($this->user)
            ->test(Edit::class, ['transaction' => $transaction])
            ->set('amount', '100000')
            ->set('category_id', (string) $newCategory->id)
            ->call('save')
            ->assertRedirect(route('transactions.index'));

        $this->assertDatabaseHas('transactions', [
            'id' => $transaction->id,
            'amount' => '100000.00',
            'category_id' => $newCategory->id,
        ]);
    }

    public function test_observer_corrects_balance_on_amount_change(): void
    {
        $account = Account::factory()->create(['current_balance' => 500_000]);
        $category = Category::factory()->expense()->create();
        $transaction = Transaction::factory()->create([
            'type' => 'expense',
            'amount' => 100_000,
            'account_id' => $account->id,
            'category_id' => $category->id,
        ]);
        // After create: balance = 500000 - 100000 = 400000
        $account->refresh();
        $this->assertSame('400000.00', $account->current_balance);

        Livewire::actingAs($this->user)
            ->test(Edit::class, ['transaction' => $transaction])
            ->set('amount', '200000')
            ->call('save');

        // Observer reverses old (-100000 → +100000) then applies new (-200000)
        // 400000 + 100000 - 200000 = 300000
        $this->assertSame('300000.00', $account->fresh()->current_balance);
    }

    public function test_observer_corrects_balance_on_type_change(): void
    {
        $account = Account::factory()->create(['current_balance' => 1_000_000]);
        $expenseCategory = Category::factory()->expense()->create();
        $incomeCategory = Category::factory()->income()->create();
        $transaction = Transaction::factory()->create([
            'type' => 'expense',
            'amount' => 200_000,
            'account_id' => $account->id,
            'category_id' => $expenseCategory->id,
        ]);
        // After create: 1000000 - 200000 = 800000
        $account->refresh();

        Livewire::actingAs($this->user)
            ->test(Edit::class, ['transaction' => $transaction])
            ->set('type', 'income')
            ->set('category_id', (string) $incomeCategory->id)
            ->call('save');

        // Observer reverses old expense (-200000 → +200000) then applies income (+200000)
        // 800000 + 200000 + 200000 = 1200000
        $this->assertSame('1200000.00', $account->fresh()->current_balance);
    }

    public function test_observer_corrects_balance_on_account_change(): void
    {
        $accountA = Account::factory()->create(['current_balance' => 1_000_000]);
        $accountB = Account::factory()->create(['current_balance' => 500_000]);
        $category = Category::factory()->expense()->create();
        $transaction = Transaction::factory()->create([
            'type' => 'expense',
            'amount' => 100_000,
            'account_id' => $accountA->id,
            'category_id' => $category->id,
        ]);
        // accountA: 1000000 - 100000 = 900000

        Livewire::actingAs($this->user)
            ->test(Edit::class, ['transaction' => $transaction])
            ->set('account_id', (string) $accountB->id)
            ->call('save');

        // accountA restored: 900000 + 100000 = 1000000
        // accountB charged: 500000 - 100000 = 400000
        $this->assertSame('1000000.00', $accountA->fresh()->current_balance);
        $this->assertSame('400000.00', $accountB->fresh()->current_balance);
    }

    public function test_note_is_created_via_first_or_create(): void
    {
        $transaction = Transaction::factory()->create(['note_id' => null]);

        Livewire::actingAs($this->user)
            ->test(Edit::class, ['transaction' => $transaction])
            ->set('note', 'New note')
            ->call('save');

        $this->assertDatabaseHas('notes', ['content' => 'New note']);
        $this->assertSame(1, Note::count());
        $this->assertNotNull($transaction->fresh()->note_id);
    }

    public function test_note_can_be_cleared(): void
    {
        $note = Note::firstOrCreate(['content' => 'Old note']);
        $transaction = Transaction::factory()->create(['note_id' => $note->id]);

        Livewire::actingAs($this->user)
            ->test(Edit::class, ['transaction' => $transaction])
            ->set('note', '')
            ->call('save');

        $this->assertNull($transaction->fresh()->note_id);
    }

    public function test_switching_type_clears_category(): void
    {
        $category = Category::factory()->expense()->create();
        $transaction = Transaction::factory()->create([
            'type' => 'expense',
            'category_id' => $category->id,
        ]);

        Livewire::actingAs($this->user)
            ->test(Edit::class, ['transaction' => $transaction])
            ->set('type', 'income')
            ->assertSet('category_id', '');
    }

    public function test_validation_requires_amount(): void
    {
        $transaction = Transaction::factory()->create();

        Livewire::actingAs($this->user)
            ->test(Edit::class, ['transaction' => $transaction])
            ->set('amount', '')
            ->call('save')
            ->assertHasErrors(['amount']);
    }

    public function test_validation_requires_valid_account(): void
    {
        $transaction = Transaction::factory()->create();

        Livewire::actingAs($this->user)
            ->test(Edit::class, ['transaction' => $transaction])
            ->set('account_id', '9999')
            ->call('save')
            ->assertHasErrors(['account_id']);
    }

    public function test_validation_requires_valid_category(): void
    {
        $transaction = Transaction::factory()->create();

        Livewire::actingAs($this->user)
            ->test(Edit::class, ['transaction' => $transaction])
            ->set('category_id', '9999')
            ->call('save')
            ->assertHasErrors(['category_id']);
    }
}
