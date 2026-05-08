<?php

namespace App\Livewire\Transactions;

use App\Enums\TransactionType;
use App\Models\Transaction;
use Carbon\Carbon;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Layout('components.layouts.app')]
#[Title('Transactions')]
class Index extends Component
{
    #[Url(as: 'month')]
    public string $currentMonth;

    public function mount(): void
    {
        $this->currentMonth = now()->format('Y-m');
    }

    public function previousMonth(): void
    {
        $this->currentMonth = Carbon::parse($this->currentMonth.'-01')->subMonth()->format('Y-m');
    }

    public function nextMonth(): void
    {
        $this->currentMonth = Carbon::parse($this->currentMonth.'-01')->addMonth()->format('Y-m');
    }

    public function render()
    {
        $month = Carbon::parse($this->currentMonth.'-01');

        $transactions = Transaction::with(['account', 'category', 'note'])
            ->whereYear('transacted_at', $month->year)
            ->whereMonth('transacted_at', $month->month)
            ->orderByDesc('transacted_at')
            ->orderByDesc('id')
            ->get();

        $grouped = $transactions->groupBy(fn ($t) => $t->transacted_at->toDateString());

        $totalIncome = (float) $transactions->where('type', TransactionType::Income)->sum('amount');
        $totalExpense = (float) $transactions->where('type', TransactionType::Expense)->sum('amount');

        return view('livewire.transactions.index', [
            'grouped' => $grouped,
            'totalIncome' => $totalIncome,
            'totalExpense' => $totalExpense,
            'month' => $month,
        ]);
    }
}
