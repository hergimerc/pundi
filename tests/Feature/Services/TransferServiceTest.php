<?php

namespace Tests\Feature\Services;

use App\Models\Account;
use App\Models\Transfer;
use App\Services\TransferService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TransferServiceTest extends TestCase
{
    use RefreshDatabase;

    private TransferService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new TransferService;
    }

    public function test_execute_deducts_amount_and_fee_from_source_account(): void
    {
        $from = Account::factory()->create(['current_balance' => 1_000_000]);
        $to = Account::factory()->create(['current_balance' => 500_000]);

        $this->service->execute([
            'from_account_id' => $from->id,
            'to_account_id' => $to->id,
            'amount' => 200_000,
            'fee' => 5_000,
            'note' => null,
            'transferred_at' => now()->toDateTimeString(),
        ]);

        $this->assertSame('795000.00', $from->fresh()->current_balance);
    }

    public function test_execute_adds_only_amount_to_destination_account(): void
    {
        $from = Account::factory()->create(['current_balance' => 1_000_000]);
        $to = Account::factory()->create(['current_balance' => 500_000]);

        $this->service->execute([
            'from_account_id' => $from->id,
            'to_account_id' => $to->id,
            'amount' => 200_000,
            'fee' => 5_000,
            'note' => null,
            'transferred_at' => now()->toDateTimeString(),
        ]);

        $this->assertSame('700000.00', $to->fresh()->current_balance);
    }

    public function test_execute_creates_transfer_record(): void
    {
        $from = Account::factory()->create(['current_balance' => 1_000_000]);
        $to = Account::factory()->create(['current_balance' => 0]);

        $transfer = $this->service->execute([
            'from_account_id' => $from->id,
            'to_account_id' => $to->id,
            'amount' => 300_000,
            'fee' => 2_500,
            'note' => 'Test transfer',
            'transferred_at' => now()->toDateTimeString(),
        ]);

        $this->assertDatabaseHas('transfers', [
            'id' => $transfer->id,
            'from_account_id' => $from->id,
            'to_account_id' => $to->id,
            'amount' => '300000.00',
            'fee' => '2500.00',
            'note' => 'Test transfer',
        ]);
    }

    public function test_execute_works_with_zero_fee(): void
    {
        $from = Account::factory()->create(['current_balance' => 500_000]);
        $to = Account::factory()->create(['current_balance' => 0]);

        $this->service->execute([
            'from_account_id' => $from->id,
            'to_account_id' => $to->id,
            'amount' => 100_000,
            'fee' => 0,
            'note' => null,
            'transferred_at' => now()->toDateTimeString(),
        ]);

        $this->assertSame('400000.00', $from->fresh()->current_balance);
        $this->assertSame('100000.00', $to->fresh()->current_balance);
    }

    public function test_reverse_restores_both_account_balances(): void
    {
        $from = Account::factory()->create(['current_balance' => 795_000]);
        $to = Account::factory()->create(['current_balance' => 700_000]);

        $transfer = Transfer::factory()->between($from, $to)->create([
            'amount' => 200_000,
            'fee' => 5_000,
        ]);

        $this->service->reverse($transfer);

        $this->assertSame('1000000.00', $from->fresh()->current_balance);
        $this->assertSame('500000.00', $to->fresh()->current_balance);
    }

    public function test_reverse_soft_deletes_transfer(): void
    {
        $from = Account::factory()->create(['current_balance' => 0]);
        $to = Account::factory()->create(['current_balance' => 200_000]);

        $transfer = Transfer::factory()->between($from, $to)->create([
            'amount' => 200_000,
            'fee' => 0,
        ]);

        $this->service->reverse($transfer);

        $this->assertSoftDeleted('transfers', ['id' => $transfer->id]);
    }
}
