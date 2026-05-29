<?php

namespace Tests\Feature\Services;

use App\Enums\TransactionType;
use App\Models\Account;
use App\Models\Category;
use App\Models\Note;
use App\Models\Transaction;
use App\Services\TransactionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use Tests\TestCase;

class TransactionServiceTest extends TestCase
{
    use RefreshDatabase;

    private TransactionService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new TransactionService;
    }

    public function test_create_stores_transaction(): void
    {
        $account = Account::factory()->create();
        $category = Category::factory()->expense()->create();

        $transaction = $this->service->create([
            'type' => TransactionType::Expense,
            'account_id' => $account->id,
            'category_id' => $category->id,
            'amount' => 150000.0,
            'transacted_at' => now()->format('Y-m-d H:i:s'),
        ]);

        $this->assertInstanceOf(Transaction::class, $transaction);
        $this->assertDatabaseHas('transactions', [
            'id' => $transaction->id,
            'type' => 'expense',
            'amount' => '150000.00',
        ]);
    }

    public function test_create_resolves_note_via_first_or_create(): void
    {
        $account = Account::factory()->create();
        $category = Category::factory()->expense()->create();

        $this->service->create([
            'type' => TransactionType::Expense,
            'account_id' => $account->id,
            'category_id' => $category->id,
            'amount' => 50000.0,
            'transacted_at' => now()->format('Y-m-d H:i:s'),
            'note' => 'Groceries',
        ]);

        $this->assertDatabaseHas('notes', ['content' => 'Groceries']);
        $this->assertSame(1, Note::count());
        $this->assertNotNull(Transaction::first()->note_id);
    }

    public function test_create_reuses_existing_note(): void
    {
        $existing = Note::firstOrCreate(['content' => 'Lunch']);
        $account = Account::factory()->create();
        $category = Category::factory()->expense()->create();

        $this->service->create([
            'type' => TransactionType::Expense,
            'account_id' => $account->id,
            'category_id' => $category->id,
            'amount' => 30000.0,
            'transacted_at' => now()->format('Y-m-d H:i:s'),
            'note' => 'Lunch',
        ]);

        $this->assertSame(1, Note::count());
        $this->assertSame($existing->id, Transaction::first()->note_id);
    }

    public function test_create_without_note_leaves_note_id_null(): void
    {
        $account = Account::factory()->create();
        $category = Category::factory()->expense()->create();

        $transaction = $this->service->create([
            'type' => TransactionType::Expense,
            'account_id' => $account->id,
            'category_id' => $category->id,
            'amount' => 20000.0,
            'transacted_at' => now()->format('Y-m-d H:i:s'),
        ]);

        $this->assertNull($transaction->note_id);
        $this->assertSame(0, Note::count());
    }

    public function test_create_throws_when_category_type_mismatches(): void
    {
        $account = Account::factory()->create();
        $incomeCategory = Category::factory()->income()->create();

        $this->expectException(InvalidArgumentException::class);

        $this->service->create([
            'type' => TransactionType::Expense,
            'account_id' => $account->id,
            'category_id' => $incomeCategory->id,
            'amount' => 10000.0,
            'transacted_at' => now()->format('Y-m-d H:i:s'),
        ]);
    }

    public function test_update_modifies_transaction(): void
    {
        $account = Account::factory()->create();
        $category = Category::factory()->expense()->create();
        $newCategory = Category::factory()->expense()->create();
        $transaction = Transaction::factory()->expense()->forAccount($account)->create([
            'category_id' => $category->id,
            'amount' => 50000,
        ]);

        $this->service->update($transaction, [
            'type' => TransactionType::Expense,
            'account_id' => $account->id,
            'category_id' => $newCategory->id,
            'amount' => 75000.0,
            'transacted_at' => now()->format('Y-m-d H:i:s'),
        ]);

        $this->assertDatabaseHas('transactions', [
            'id' => $transaction->id,
            'amount' => '75000.00',
            'category_id' => $newCategory->id,
        ]);
    }

    public function test_update_handles_note_change(): void
    {
        $transaction = Transaction::factory()->expense()->create(['note_id' => null]);

        $this->service->update($transaction, [
            'type' => TransactionType::Expense,
            'account_id' => $transaction->account_id,
            'category_id' => $transaction->category_id,
            'amount' => (float) $transaction->amount,
            'transacted_at' => now()->format('Y-m-d H:i:s'),
            'note' => 'New note',
        ]);

        $this->assertDatabaseHas('notes', ['content' => 'New note']);
        $this->assertNotNull($transaction->fresh()->note_id);
    }

    public function test_update_throws_when_category_type_mismatches(): void
    {
        $transaction = Transaction::factory()->expense()->create();
        $incomeCategory = Category::factory()->income()->create();

        $this->expectException(InvalidArgumentException::class);

        $this->service->update($transaction, [
            'type' => TransactionType::Expense,
            'account_id' => $transaction->account_id,
            'category_id' => $incomeCategory->id,
            'amount' => (float) $transaction->amount,
            'transacted_at' => now()->format('Y-m-d H:i:s'),
        ]);
    }

    public function test_delete_soft_deletes_transaction(): void
    {
        $transaction = Transaction::factory()->create();

        $this->service->delete($transaction);

        $this->assertSoftDeleted('transactions', ['id' => $transaction->id]);
    }
}
