<?php

namespace App\Livewire\Transactions;

use App\Enums\CategoryType;
use App\Enums\TransactionType;
use App\Models\Account;
use App\Models\Category;
use App\Models\Note;
use App\Models\Transaction;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Validate;
use Livewire\Component;

#[Layout('components.layouts.app')]
#[Title('New Transaction')]
class Create extends Component
{
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

    public function mount(): void
    {
        $this->transacted_at = now()->format('Y-m-d\TH:i');
    }

    public function updatedType(): void
    {
        $this->category_id = '';
    }

    public function save(): void
    {
        $this->validate();

        $noteModel = $this->note !== ''
            ? Note::firstOrCreate(['content' => $this->note])
            : null;

        Transaction::create([
            'type' => TransactionType::from($this->type),
            'account_id' => (int) $this->account_id,
            'category_id' => (int) $this->category_id,
            'note_id' => $noteModel?->id,
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

        return view('livewire.transactions.create', [
            'accounts' => Account::where('is_active', true)->orderBy('name')->get(),
            'categories' => Category::ofType($categoryType)->with('children')->roots()->orderBy('sort_order')->orderBy('name')->get(),
            'noteSuggestions' => $noteSuggestions,
        ]);
    }
}
