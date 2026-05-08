<?php

namespace Tests\Feature\Livewire\Transactions;

use App\Livewire\Transactions\Create;
use App\Models\Account;
use App\Models\Category;
use App\Models\Note;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CreateTest extends TestCase
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
        $this->get(route('transactions.create'))->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_view_create_page(): void
    {
        $this->actingAs($this->user)
            ->get(route('transactions.create'))
            ->assertOk();
    }

    public function test_creates_expense_transaction_and_redirects(): void
    {
        $account = Account::factory()->create();
        $category = Category::factory()->expense()->create();

        Livewire::actingAs($this->user)
            ->test(Create::class)
            ->set('type', 'expense')
            ->set('amount', '150000')
            ->set('account_id', (string) $account->id)
            ->set('category_id', (string) $category->id)
            ->set('transacted_at', now()->format('Y-m-d\TH:i'))
            ->call('save')
            ->assertRedirect(route('transactions.index'));

        $this->assertDatabaseHas('transactions', [
            'type' => 'expense',
            'account_id' => $account->id,
            'category_id' => $category->id,
            'amount' => '150000.00',
        ]);
    }

    public function test_creates_income_transaction(): void
    {
        $account = Account::factory()->create();
        $category = Category::factory()->income()->create();

        Livewire::actingAs($this->user)
            ->test(Create::class)
            ->set('type', 'income')
            ->set('amount', '5000000')
            ->set('account_id', (string) $account->id)
            ->set('category_id', (string) $category->id)
            ->set('transacted_at', now()->format('Y-m-d\TH:i'))
            ->call('save');

        $this->assertDatabaseHas('transactions', [
            'type' => 'income',
            'amount' => '5000000.00',
        ]);
    }

    public function test_creates_note_via_first_or_create(): void
    {
        $account = Account::factory()->create();
        $category = Category::factory()->expense()->create();

        Livewire::actingAs($this->user)
            ->test(Create::class)
            ->set('type', 'expense')
            ->set('amount', '50000')
            ->set('account_id', (string) $account->id)
            ->set('category_id', (string) $category->id)
            ->set('note', 'Monthly salary')
            ->set('transacted_at', now()->format('Y-m-d\TH:i'))
            ->call('save');

        $this->assertDatabaseHas('notes', ['content' => 'Monthly salary']);
        $this->assertSame(1, Note::count());

        $transaction = Transaction::first();
        $this->assertNotNull($transaction->note_id);
    }

    public function test_reuses_existing_note(): void
    {
        $note = Note::firstOrCreate(['content' => 'Existing note']);
        $account = Account::factory()->create();
        $category = Category::factory()->expense()->create();

        Livewire::actingAs($this->user)
            ->test(Create::class)
            ->set('type', 'expense')
            ->set('amount', '20000')
            ->set('account_id', (string) $account->id)
            ->set('category_id', (string) $category->id)
            ->set('note', 'Existing note')
            ->set('transacted_at', now()->format('Y-m-d\TH:i'))
            ->call('save');

        $this->assertSame(1, Note::count());
        $this->assertSame($note->id, Transaction::first()->note_id);
    }

    public function test_note_is_optional(): void
    {
        $account = Account::factory()->create();
        $category = Category::factory()->expense()->create();

        Livewire::actingAs($this->user)
            ->test(Create::class)
            ->set('type', 'expense')
            ->set('amount', '30000')
            ->set('account_id', (string) $account->id)
            ->set('category_id', (string) $category->id)
            ->set('transacted_at', now()->format('Y-m-d\TH:i'))
            ->call('save');

        $this->assertNull(Transaction::first()->note_id);
        $this->assertSame(0, Note::count());
    }

    public function test_switching_type_clears_category(): void
    {
        $category = Category::factory()->expense()->create();

        Livewire::actingAs($this->user)
            ->test(Create::class)
            ->set('type', 'expense')
            ->set('category_id', (string) $category->id)
            ->set('type', 'income')
            ->assertSet('category_id', '');
    }

    public function test_validation_requires_amount(): void
    {
        $account = Account::factory()->create();
        $category = Category::factory()->expense()->create();

        Livewire::actingAs($this->user)
            ->test(Create::class)
            ->set('type', 'expense')
            ->set('amount', '')
            ->set('account_id', (string) $account->id)
            ->set('category_id', (string) $category->id)
            ->set('transacted_at', now()->format('Y-m-d\TH:i'))
            ->call('save')
            ->assertHasErrors(['amount']);
    }

    public function test_validation_requires_valid_account(): void
    {
        $category = Category::factory()->expense()->create();

        Livewire::actingAs($this->user)
            ->test(Create::class)
            ->set('type', 'expense')
            ->set('amount', '50000')
            ->set('account_id', '9999')
            ->set('category_id', (string) $category->id)
            ->set('transacted_at', now()->format('Y-m-d\TH:i'))
            ->call('save')
            ->assertHasErrors(['account_id']);
    }

    public function test_validation_requires_valid_category(): void
    {
        $account = Account::factory()->create();

        Livewire::actingAs($this->user)
            ->test(Create::class)
            ->set('type', 'expense')
            ->set('amount', '50000')
            ->set('account_id', (string) $account->id)
            ->set('category_id', '9999')
            ->set('transacted_at', now()->format('Y-m-d\TH:i'))
            ->call('save')
            ->assertHasErrors(['category_id']);
    }

    public function test_expense_decreases_account_balance(): void
    {
        $account = Account::factory()->create(['current_balance' => 1_000_000]);
        $category = Category::factory()->expense()->create();

        Livewire::actingAs($this->user)
            ->test(Create::class)
            ->set('type', 'expense')
            ->set('amount', '250000')
            ->set('account_id', (string) $account->id)
            ->set('category_id', (string) $category->id)
            ->set('transacted_at', now()->format('Y-m-d\TH:i'))
            ->call('save');

        $this->assertSame('750000.00', $account->fresh()->current_balance);
    }

    public function test_income_increases_account_balance(): void
    {
        $account = Account::factory()->create(['current_balance' => 500_000]);
        $category = Category::factory()->income()->create();

        Livewire::actingAs($this->user)
            ->test(Create::class)
            ->set('type', 'income')
            ->set('amount', '2000000')
            ->set('account_id', (string) $account->id)
            ->set('category_id', (string) $category->id)
            ->set('transacted_at', now()->format('Y-m-d\TH:i'))
            ->call('save');

        $this->assertSame('2500000.00', $account->fresh()->current_balance);
    }
}
