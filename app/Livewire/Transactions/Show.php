<?php

namespace App\Livewire\Transactions;

use App\Models\Transaction;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.app')]
#[Title('Transaction Detail')]
class Show extends Component
{
    public Transaction $transaction;

    public function mount(Transaction $transaction): void
    {
        $this->transaction = $transaction->load(['account', 'category', 'note']);
    }

    public function delete(): void
    {
        $this->transaction->delete();

        $this->redirectRoute('transactions.index', navigate: true);
    }

    public function render()
    {
        return view('livewire.transactions.show');
    }
}
