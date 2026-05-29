<?php

namespace App\Services;

use App\Enums\CategoryType;
use App\Enums\TransactionType;
use App\Models\Category;
use App\Models\Note;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class TransactionService
{
    /**
     * @param array{
     *   type: TransactionType,
     *   account_id: int,
     *   category_id: int,
     *   amount: float,
     *   transacted_at: string,
     *   note?: string|null,
     *   description?: string|null
     * } $data
     */
    public function create(array $data): Transaction
    {
        $this->ensureCategoryMatchesType($data['type'], $data['category_id']);

        return DB::transaction(function () use ($data) {
            return Transaction::create([
                'type' => $data['type'],
                'account_id' => $data['account_id'],
                'category_id' => $data['category_id'],
                'note_id' => $this->resolveNote($data['note'] ?? null)?->id,
                'amount' => $data['amount'],
                'description' => $data['description'] ?? null,
                'transacted_at' => $data['transacted_at'],
            ]);
        });
    }

    /**
     * @param array{
     *   type: TransactionType,
     *   account_id: int,
     *   category_id: int,
     *   amount: float,
     *   transacted_at: string,
     *   note?: string|null,
     *   description?: string|null
     * } $data
     */
    public function update(Transaction $transaction, array $data): Transaction
    {
        $this->ensureCategoryMatchesType($data['type'], $data['category_id']);

        return DB::transaction(function () use ($transaction, $data) {
            $transaction->update([
                'type' => $data['type'],
                'account_id' => $data['account_id'],
                'category_id' => $data['category_id'],
                'note_id' => $this->resolveNote($data['note'] ?? null)?->id,
                'amount' => $data['amount'],
                'description' => $data['description'] ?? null,
                'transacted_at' => $data['transacted_at'],
            ]);

            return $transaction;
        });
    }

    public function delete(Transaction $transaction): void
    {
        $transaction->delete();
    }

    private function resolveNote(?string $content): ?Note
    {
        if ($content === null || $content === '') {
            return null;
        }

        return Note::firstOrCreate(['content' => $content]);
    }

    private function ensureCategoryMatchesType(TransactionType $type, int $categoryId): void
    {
        $category = Category::find($categoryId);

        if ($category === null) {
            throw new InvalidArgumentException("Category [{$categoryId}] not found.");
        }

        $expected = $type === TransactionType::Income ? CategoryType::Income : CategoryType::Expense;

        if ($category->type !== $expected) {
            throw new InvalidArgumentException(
                "Category [{$category->name}] is {$category->type->value} but transaction type is {$type->value}."
            );
        }
    }
}
