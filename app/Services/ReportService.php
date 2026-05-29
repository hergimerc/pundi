<?php

namespace App\Services;

use App\Enums\TransactionType;
use App\Models\Transaction;
use Illuminate\Support\Collection;

class ReportService
{
    /**
     * @return array{income: float, expense: float, net: float}
     */
    public function monthlySummary(int $month, int $year): array
    {
        $transactions = Transaction::whereYear('transacted_at', $year)
            ->whereMonth('transacted_at', $month)
            ->get(['type', 'amount']);

        $income = (float) $transactions->where('type', TransactionType::Income)->sum('amount');
        $expense = (float) $transactions->where('type', TransactionType::Expense)->sum('amount');

        return [
            'income' => $income,
            'expense' => $expense,
            'net' => $income - $expense,
        ];
    }

    /**
     * @return Collection<int, array{category_id: int, name: string, color: string|null, amount: float}>
     */
    public function categoryBreakdown(int $month, int $year, TransactionType $type): Collection
    {
        return Transaction::with('category')
            ->where('type', $type)
            ->whereYear('transacted_at', $year)
            ->whereMonth('transacted_at', $month)
            ->whereNotNull('category_id')
            ->get()
            ->groupBy('category_id')
            ->map(fn ($items) => [
                'category_id' => $items->first()->category_id,
                'name' => $items->first()->category?->name ?? 'Uncategorized',
                'color' => $items->first()->category?->color,
                'amount' => (float) $items->sum('amount'),
            ])
            ->sortByDesc('amount')
            ->values();
    }

    /**
     * Returns 12 months of income/expense totals for the given year.
     *
     * @return Collection<int, array{month: int, income: float, expense: float}>
     */
    public function annualTrend(int $year): Collection
    {
        return collect(range(1, 12))->map(function (int $month) use ($year) {
            $transactions = Transaction::whereYear('transacted_at', $year)
                ->whereMonth('transacted_at', $month)
                ->get(['type', 'amount']);

            return [
                'month' => $month,
                'income' => (float) $transactions->where('type', TransactionType::Income)->sum('amount'),
                'expense' => (float) $transactions->where('type', TransactionType::Expense)->sum('amount'),
            ];
        });
    }
}
