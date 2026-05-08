<?php

namespace Tests\Feature\Livewire\Accounts;

use App\Enums\AccountType;
use App\Livewire\Accounts\Index;
use App\Models\Account;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class IndexTest extends TestCase
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
        $this->get(route('accounts.index'))->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_view_accounts_page(): void
    {
        $this->actingAs($this->user)
            ->get(route('accounts.index'))
            ->assertOk();
    }

    public function test_renders_accounts_grouped_by_type(): void
    {
        Account::factory()->cash()->create(['name' => 'Wallet']);
        Account::factory()->bankAccount()->create(['name' => 'BCA']);
        Account::factory()->creditCard()->create(['name' => 'BCA Credit Card']);

        Livewire::actingAs($this->user)
            ->test(Index::class)
            ->assertSee('Cash')
            ->assertSee('Bank Account')
            ->assertSee('Credit Card')
            ->assertSee('Wallet')
            ->assertSee('BCA')
            ->assertSee('BCA Credit Card');
    }

    public function test_shows_empty_state_when_no_accounts(): void
    {
        Livewire::actingAs($this->user)
            ->test(Index::class)
            ->assertSee('No accounts yet.');
    }

    public function test_total_balance_sums_only_active_accounts(): void
    {
        Account::factory()->create([
            'type' => AccountType::Cash,
            'current_balance' => 1_000_000,
            'is_active' => true,
        ]);
        Account::factory()->create([
            'type' => AccountType::Cash,
            'current_balance' => 500_000,
            'is_active' => false,
        ]);

        Livewire::actingAs($this->user)
            ->test(Index::class)
            ->assertSee('1.000.000')
            ->assertDontSee('1.500.000');
    }

    public function test_group_sum_reflects_active_accounts_only(): void
    {
        Account::factory()->bankAccount()->create([
            'current_balance' => 3_000_000,
            'is_active' => true,
        ]);
        Account::factory()->bankAccount()->create([
            'current_balance' => 2_000_000,
            'is_active' => false,
        ]);

        Livewire::actingAs($this->user)
            ->test(Index::class)
            ->assertSee('3.000.000')
            ->assertDontSee('5.000.000');
    }

    public function test_inactive_accounts_are_shown_with_inactive_label(): void
    {
        Account::factory()->create([
            'name' => 'Old Wallet',
            'type' => AccountType::Cash,
            'is_active' => false,
        ]);

        Livewire::actingAs($this->user)
            ->test(Index::class)
            ->assertSee('Old Wallet')
            ->assertSee('Inactive');
    }

    public function test_negative_balance_is_displayed(): void
    {
        Account::factory()->creditCard()->create([
            'current_balance' => -250_000,
        ]);

        Livewire::actingAs($this->user)
            ->test(Index::class)
            ->assertSee('-250.000');
    }
}
