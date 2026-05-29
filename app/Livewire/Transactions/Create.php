<?php

namespace App\Livewire\Transactions;

use App\Enums\CategoryType;
use App\Enums\TransactionType;
use App\Models\Account;
use App\Models\Category;
use App\Models\Note;
use App\Services\TransactionService;
use App\Services\TransferService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('components.layouts.app')]
#[Title('New Transaction')]
class Create extends Component
{
    use WithFileUploads;

    public string $type = 'expense';

    public string $amount = '';

    public string $transacted_at = '';

    // Transaction fields
    public string $account_id = '';

    public string $category_id = '';

    public string $note = '';

    public ?string $description = null;

    public array $attachments = [];

    // Transfer fields
    public string $from_account_id = '';

    public string $to_account_id = '';

    public string $fee = '0';

    public function mount(): void
    {
        $this->transacted_at = now()->format('Y-m-d\TH:i');
    }

    public function updatedType(): void
    {
        $this->category_id = '';
    }

    public function save(TransferService $transferService, TransactionService $transactionService): void
    {
        if ($this->type === 'transfer') {
            $this->validate([
                'amount' => 'required|numeric|min:1|max:999999999999.99',
                'from_account_id' => 'required|exists:accounts,id|different:to_account_id',
                'to_account_id' => 'required|exists:accounts,id|different:from_account_id',
                'fee' => 'nullable|numeric|min:0|max:999999999999.99',
                'note' => 'nullable|string|max:500',
                'transacted_at' => 'required|date',
            ]);

            $transferService->execute([
                'from_account_id' => (int) $this->from_account_id,
                'to_account_id' => (int) $this->to_account_id,
                'amount' => (float) $this->amount,
                'fee' => (float) ($this->fee ?: 0),
                'note' => $this->note ?: null,
                'transferred_at' => $this->transacted_at,
            ]);
        } else {
            $this->validate([
                'type' => 'required|in:expense,income',
                'amount' => 'required|numeric|min:1|max:999999999999.99',
                'account_id' => 'required|exists:accounts,id',
                'category_id' => 'required|exists:categories,id',
                'note' => 'nullable|string|max:255',
                'description' => 'nullable|string|max:1000',
                'transacted_at' => 'required|date',
                'attachments.*' => 'nullable|file|max:10240',
            ]);

            $transaction = $transactionService->create([
                'type' => TransactionType::from($this->type),
                'account_id' => (int) $this->account_id,
                'category_id' => (int) $this->category_id,
                'note' => $this->note ?: null,
                'amount' => (float) $this->amount,
                'description' => $this->description ?: null,
                'transacted_at' => $this->transacted_at,
            ]);

            if ($this->attachments) {
                $transactionService->storeAttachments($transaction, $this->attachments);
            }
        }

        $this->redirectRoute('transactions.index', navigate: true);
    }

    public function render()
    {
        $categoryType = $this->type === 'income' ? CategoryType::Income : CategoryType::Expense;

        $noteSuggestions = $this->type !== 'transfer' && strlen($this->note) >= 1
            ? Note::where('content', 'like', '%'.$this->note.'%')->orderBy('content')->limit(8)->pluck('content')
            : collect();

        return view('livewire.transactions.create', [
            'accounts' => Account::where('is_active', true)->orderBy('name')->get(),
            'categories' => Category::ofType($categoryType)->with('children')->roots()->orderBy('sort_order')->orderBy('name')->get(),
            'noteSuggestions' => $noteSuggestions,
        ]);
    }
}
