<?php

namespace App\Livewire\Accounts;

use App\Enums\TransactionType;
use App\Models\Account;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.app')]
#[Title('Account Detail')]
class Show extends Component
{
    public Account $account;

    public function mount(Account $account): void
    {
        $this->account = $account;
    }

    public function render()
    {
        $transactions = $this->account->transactions()
            ->with(['category', 'note'])
            ->orderByDesc('transacted_at')
            ->orderByDesc('id')
            ->get();

        $grouped = $transactions->groupBy(fn ($t) => $t->transacted_at->toDateString());

        $totalIncome = (float) $transactions->where('type', TransactionType::Income)->sum('amount');
        $totalExpense = (float) $transactions->where('type', TransactionType::Expense)->sum('amount');

        return view('livewire.accounts.show', [
            'grouped' => $grouped,
            'totalIncome' => $totalIncome,
            'totalExpense' => $totalExpense,
        ]);
    }
}
