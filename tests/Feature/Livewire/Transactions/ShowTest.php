<?php

namespace Tests\Feature\Livewire\Transactions;

use App\Livewire\Transactions\Show;
use App\Models\Account;
use App\Models\Category;
use App\Models\Note;
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
        $transaction = Transaction::factory()->create();

        $this->get(route('transactions.show', $transaction))->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_view_show_page(): void
    {
        $transaction = Transaction::factory()->create();

        $this->actingAs($this->user)
            ->get(route('transactions.show', $transaction))
            ->assertOk();
    }

    public function test_shows_transaction_details(): void
    {
        $account = Account::factory()->create(['name' => 'BCA Savings']);
        $category = Category::factory()->expense()->create(['name' => 'Food']);
        $note = Note::firstOrCreate(['content' => 'Lunch']);
        $transaction = Transaction::factory()->create([
            'type' => 'expense',
            'amount' => 50000,
            'account_id' => $account->id,
            'category_id' => $category->id,
            'note_id' => $note->id,
            'description' => 'Nasi goreng',
        ]);

        Livewire::actingAs($this->user)
            ->test(Show::class, ['transaction' => $transaction])
            ->assertSee('Food')
            ->assertSee('BCA Savings')
            ->assertSee('Lunch')
            ->assertSee('Nasi goreng')
            ->assertSee('50.000');
    }

    public function test_delete_soft_deletes_transaction_and_redirects(): void
    {
        $transaction = Transaction::factory()->create();

        Livewire::actingAs($this->user)
            ->test(Show::class, ['transaction' => $transaction])
            ->call('delete')
            ->assertRedirect(route('transactions.index'));

        $this->assertSoftDeleted('transactions', ['id' => $transaction->id]);
    }

    public function test_delete_reverses_expense_balance(): void
    {
        $account = Account::factory()->create(['current_balance' => 500_000]);
        $category = Category::factory()->expense()->create();
        $transaction = Transaction::factory()->create([
            'type' => 'expense',
            'amount' => 100_000,
            'account_id' => $account->id,
            'category_id' => $category->id,
        ]);
        // After create: 500000 - 100000 = 400000
        $account->refresh();

        Livewire::actingAs($this->user)
            ->test(Show::class, ['transaction' => $transaction])
            ->call('delete');

        // Observer reverses the expense: 400000 + 100000 = 500000
        $this->assertSame('500000.00', $account->fresh()->current_balance);
    }

    public function test_delete_reverses_income_balance(): void
    {
        $account = Account::factory()->create(['current_balance' => 1_000_000]);
        $category = Category::factory()->income()->create();
        $transaction = Transaction::factory()->create([
            'type' => 'income',
            'amount' => 500_000,
            'account_id' => $account->id,
            'category_id' => $category->id,
        ]);
        // After create: 1000000 + 500000 = 1500000
        $account->refresh();

        Livewire::actingAs($this->user)
            ->test(Show::class, ['transaction' => $transaction])
            ->call('delete');

        // Observer reverses the income: 1500000 - 500000 = 1000000
        $this->assertSame('1000000.00', $account->fresh()->current_balance);
    }
}
