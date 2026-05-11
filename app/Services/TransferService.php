<?php

namespace App\Services;

use App\Models\Account;
use App\Models\Transfer;
use Illuminate\Support\Facades\DB;

class TransferService
{
    /**
     * @param  array{from_account_id: int, to_account_id: int, amount: float, fee: float, note: ?string, transferred_at: string}  $data
     */
    public function execute(array $data): Transfer
    {
        return DB::transaction(function () use ($data) {
            $from = Account::lockForUpdate()->findOrFail($data['from_account_id']);
            $to = Account::lockForUpdate()->findOrFail($data['to_account_id']);

            $deduct = $data['amount'] + ($data['fee'] ?? 0);

            $from->decrement('current_balance', $deduct);
            $to->increment('current_balance', $data['amount']);

            return Transfer::create([
                'from_account_id' => $from->id,
                'to_account_id' => $to->id,
                'amount' => $data['amount'],
                'fee' => $data['fee'] ?? 0,
                'note' => $data['note'] ?? null,
                'transferred_at' => $data['transferred_at'],
            ]);
        });
    }

    public function reverse(Transfer $transfer): void
    {
        DB::transaction(function () use ($transfer) {
            $from = Account::lockForUpdate()->findOrFail($transfer->from_account_id);
            $to = Account::lockForUpdate()->findOrFail($transfer->to_account_id);

            $from->increment('current_balance', $transfer->amount + $transfer->fee);
            $to->decrement('current_balance', $transfer->amount);

            $transfer->delete();
        });
    }
}
