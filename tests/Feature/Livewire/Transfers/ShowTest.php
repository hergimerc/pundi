<?php

namespace Tests\Feature\Livewire\Transfers;

use App\Livewire\Transfers\Show;
use App\Models\Account;
use App\Models\Transfer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ShowTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_redirects_guests_to_login(): void
    {
        $transfer = Transfer::factory()->create();

        $this->get(route('transfers.show', $transfer))->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_view_show_page(): void
    {
        $transfer = Transfer::factory()->create();

        $this->actingAs($this->user)
            ->get(route('transfers.show', $transfer))
            ->assertOk();
    }

    public function test_shows_transfer_details(): void
    {
        $from = Account::factory()->create(['name' => 'BCA']);
        $to = Account::factory()->create(['name' => 'GoPay']);

        $transfer = Transfer::factory()->between($from, $to)->create([
            'amount' => 250_000,
            'fee' => 2_500,
            'note' => 'Top up wallet',
            'transferred_at' => now(),
        ]);

        Livewire::actingAs($this->user)
            ->test(Show::class, ['transfer' => $transfer])
            ->assertSee('BCA')
            ->assertSee('GoPay')
            ->assertSee('250.000')
            ->assertSee('2.500')
            ->assertSee('Top up wallet');
    }

    public function test_delete_reverses_balances_and_soft_deletes(): void
    {
        $from = Account::factory()->create(['current_balance' => 795_000]);
        $to = Account::factory()->create(['current_balance' => 700_000]);

        $transfer = Transfer::factory()->between($from, $to)->create([
            'amount' => 200_000,
            'fee' => 5_000,
        ]);

        Livewire::actingAs($this->user)
            ->test(Show::class, ['transfer' => $transfer])
            ->call('delete')
            ->assertRedirect(route('transactions.index'));

        $this->assertSame('1000000.00', $from->fresh()->current_balance);
        $this->assertSame('500000.00', $to->fresh()->current_balance);
        $this->assertSoftDeleted('transfers', ['id' => $transfer->id]);
    }
}
