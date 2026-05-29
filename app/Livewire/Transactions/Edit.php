<?php

namespace App\Livewire\Transactions;

use App\Enums\CategoryType;
use App\Enums\TransactionType;
use App\Models\Account;
use App\Models\Category;
use App\Models\Note;
use App\Models\Transaction;
use App\Services\TransactionService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Validate;
use Livewire\Component;

#[Layout('components.layouts.app')]
#[Title('Edit Transaction')]
class Edit extends Component
{
    public Transaction $transaction;

    #[Validate('required|in:expense,income')]
    public string $type = 'expense';

    #[Validate('required|numeric|min:1|max:999999999999.99')]
    public string $amount = '';

    #[Validate('required|exists:accounts,id')]
    public string $account_id = '';

    #[Validate('required|exists:categories,id')]
    public string $category_id = '';

    #[Validate('nullable|string|max:255')]
    public string $note = '';

    #[Validate('nullable|string|max:1000')]
    public ?string $description = null;

    #[Validate('required|date')]
    public string $transacted_at = '';

    public function mount(Transaction $transaction): void
    {
        $this->transaction = $transaction;
        $this->type = $transaction->type->value;
        $this->amount = (string) (int) $transaction->amount;
        $this->account_id = (string) $transaction->account_id;
        $this->category_id = (string) $transaction->category_id;
        $this->note = $transaction->note?->content ?? '';
        $this->description = $transaction->description;
        $this->transacted_at = $transaction->transacted_at->format('Y-m-d\TH:i');
    }

    public function updatedType(): void
    {
        $this->category_id = '';
    }

    public function save(TransactionService $transactionService): void
    {
        $this->validate();

        $transactionService->update($this->transaction, [
            'type' => TransactionType::from($this->type),
            'account_id' => (int) $this->account_id,
            'category_id' => (int) $this->category_id,
            'note' => $this->note ?: null,
            'amount' => (float) $this->amount,
            'description' => $this->description ?: null,
            'transacted_at' => $this->transacted_at,
        ]);

        $this->redirectRoute('transactions.index', navigate: true);
    }

    public function render()
    {
        $categoryType = $this->type === 'income' ? CategoryType::Income : CategoryType::Expense;

        $noteSuggestions = strlen($this->note) >= 1
            ? Note::where('content', 'like', '%'.$this->note.'%')->orderBy('content')->limit(8)->pluck('content')
            : collect();

        return view('livewire.transactions.edit', [
            'accounts' => Account::where('is_active', true)->orderBy('name')->get(),
            'categories' => Category::ofType($categoryType)->with('children')->roots()->orderBy('sort_order')->orderBy('name')->get(),
            'noteSuggestions' => $noteSuggestions,
        ]);
    }
}
