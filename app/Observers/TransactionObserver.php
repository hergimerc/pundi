<?php

namespace App\Observers;

use App\Enums\TransactionType;
use App\Models\Account;
use App\Models\Transaction;

class TransactionObserver
{
    public function created(Transaction $transaction): void
    {
        $this->applyBalance($transaction->account_id, $transaction->type, $transaction->amount);
    }

    public function updated(Transaction $transaction): void
    {
        // Reverse the old effect
        $oldType = TransactionType::from($transaction->getRawOriginal('type'));
        $oldAmount = (float) $transaction->getRawOriginal('amount');
        $oldAccountId = (int) $transaction->getRawOriginal('account_id');

        $this->reverseBalance($oldAccountId, $oldType, $oldAmount);

        // Apply the new effect
        $this->applyBalance($transaction->account_id, $transaction->type, $transaction->amount);
    }

    public function deleted(Transaction $transaction): void
    {
        $this->reverseBalance($transaction->account_id, $transaction->type, $transaction->amount);
    }

    public function restored(Transaction $transaction): void
    {
        $this->applyBalance($transaction->account_id, $transaction->type, $transaction->amount);
    }

    private function applyBalance(int $accountId, TransactionType $type, float|string $amount): void
    {
        $delta = $type === TransactionType::Income ? (float) $amount : -(float) $amount;

        Account::where('id', $accountId)->increment('current_balance', $delta);
    }

    private function reverseBalance(int $accountId, TransactionType $type, float|string $amount): void
    {
        $delta = $type === TransactionType::Income ? -(float) $amount : (float) $amount;

        Account::where('id', $accountId)->increment('current_balance', $delta);
    }
}
