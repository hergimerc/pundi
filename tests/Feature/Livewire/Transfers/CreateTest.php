<?php

namespace Tests\Feature\Livewire\Transfers;

use App\Livewire\Transactions\Create;
use App\Models\Account;
use App\Models\Transfer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CreateTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_creates_transfer_via_transfer_tab_and_redirects(): void
    {
        $from = Account::factory()->create(['current_balance' => 1_000_000]);
        $to = Account::factory()->create(['current_balance' => 0]);

        Livewire::actingAs($this->user)
            ->test(Create::class)
            ->set('type', 'transfer')
            ->set('amount', '300000')
            ->set('from_account_id', (string) $from->id)
            ->set('to_account_id', (string) $to->id)
            ->set('fee', '0')
            ->set('transacted_at', now()->format('Y-m-d\TH:i'))
            ->call('save')
            ->assertRedirect(route('transactions.index'));

        $this->assertDatabaseHas('transfers', [
            'from_account_id' => $from->id,
            'to_account_id' => $to->id,
            'amount' => '300000.00',
        ]);
    }

    public function test_transfer_adjusts_both_balances(): void
    {
        $from = Account::factory()->create(['current_balance' => 1_000_000]);
        $to = Account::factory()->create(['current_balance' => 200_000]);

        Livewire::actingAs($this->user)
            ->test(Create::class)
            ->set('type', 'transfer')
            ->set('amount', '400000')
            ->set('from_account_id', (string) $from->id)
            ->set('to_account_id', (string) $to->id)
            ->set('fee', '5000')
            ->set('transacted_at', now()->format('Y-m-d\TH:i'))
            ->call('save');

        $this->assertSame('595000.00', $from->fresh()->current_balance);
        $this->assertSame('600000.00', $to->fresh()->current_balance);
    }

    public function test_validation_requires_from_account(): void
    {
        $to = Account::factory()->create();

        Livewire::actingAs($this->user)
            ->test(Create::class)
            ->set('type', 'transfer')
            ->set('to_account_id', (string) $to->id)
            ->set('amount', '100000')
            ->set('transacted_at', now()->format('Y-m-d\TH:i'))
            ->call('save')
            ->assertHasErrors(['from_account_id']);
    }

    public function test_validation_requires_to_account(): void
    {
        $from = Account::factory()->create();

        Livewire::actingAs($this->user)
            ->test(Create::class)
            ->set('type', 'transfer')
            ->set('from_account_id', (string) $from->id)
            ->set('amount', '100000')
            ->set('transacted_at', now()->format('Y-m-d\TH:i'))
            ->call('save')
            ->assertHasErrors(['to_account_id']);
    }

    public function test_validation_rejects_same_from_and_to_account(): void
    {
        $account = Account::factory()->create();

        Livewire::actingAs($this->user)
            ->test(Create::class)
            ->set('type', 'transfer')
            ->set('from_account_id', (string) $account->id)
            ->set('to_account_id', (string) $account->id)
            ->set('amount', '100000')
            ->set('transacted_at', now()->format('Y-m-d\TH:i'))
            ->call('save')
            ->assertHasErrors(['from_account_id']);
    }

    public function test_validation_requires_amount(): void
    {
        $from = Account::factory()->create();
        $to = Account::factory()->create();

        Livewire::actingAs($this->user)
            ->test(Create::class)
            ->set('type', 'transfer')
            ->set('from_account_id', (string) $from->id)
            ->set('to_account_id', (string) $to->id)
            ->set('amount', '')
            ->set('transacted_at', now()->format('Y-m-d\TH:i'))
            ->call('save')
            ->assertHasErrors(['amount']);
    }

    public function test_note_is_optional(): void
    {
        $from = Account::factory()->create(['current_balance' => 500_000]);
        $to = Account::factory()->create(['current_balance' => 0]);

        Livewire::actingAs($this->user)
            ->test(Create::class)
            ->set('type', 'transfer')
            ->set('from_account_id', (string) $from->id)
            ->set('to_account_id', (string) $to->id)
            ->set('amount', '100000')
            ->set('fee', '0')
            ->set('transacted_at', now()->format('Y-m-d\TH:i'))
            ->call('save');

        $this->assertNull(Transfer::first()->note);
    }
}
