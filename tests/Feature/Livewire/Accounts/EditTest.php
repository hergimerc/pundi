<?php

namespace Tests\Feature\Livewire\Accounts;

use App\Livewire\Accounts\Edit;
use App\Models\Account;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class EditTest extends TestCase
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
        $account = Account::factory()->create();

        $this->get(route('accounts.edit', $account))->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_view_edit_page(): void
    {
        $account = Account::factory()->create();

        $this->actingAs($this->user)
            ->get(route('accounts.edit', $account))
            ->assertOk();
    }

    public function test_form_is_pre_filled_with_account_data(): void
    {
        $account = Account::factory()->create([
            'name' => 'BCA Savings',
            'type' => 'bank_account',
            'color' => '#3b82f6',
            'is_active' => true,
        ]);

        Livewire::actingAs($this->user)
            ->test(Edit::class, ['account' => $account])
            ->assertSet('name', 'BCA Savings')
            ->assertSet('type', 'bank_account')
            ->assertSet('color', '#3b82f6')
            ->assertSet('is_active', true);
    }

    public function test_updates_account_and_redirects(): void
    {
        $account = Account::factory()->create([
            'name' => 'Old Name',
            'type' => 'cash',
        ]);

        Livewire::actingAs($this->user)
            ->test(Edit::class, ['account' => $account])
            ->set('name', 'New Name')
            ->set('type', 'bank_account')
            ->set('color', '#22c55e')
            ->call('save')
            ->assertRedirect(route('accounts.index'));

        $this->assertDatabaseHas('accounts', [
            'id' => $account->id,
            'name' => 'New Name',
            'type' => 'bank_account',
            'color' => '#22c55e',
        ]);
    }

    public function test_can_deactivate_account(): void
    {
        $account = Account::factory()->create(['is_active' => true]);

        Livewire::actingAs($this->user)
            ->test(Edit::class, ['account' => $account])
            ->set('is_active', false)
            ->call('save');

        $this->assertDatabaseHas('accounts', [
            'id' => $account->id,
            'is_active' => false,
        ]);
    }

    public function test_can_reactivate_account(): void
    {
        $account = Account::factory()->create(['is_active' => false]);

        Livewire::actingAs($this->user)
            ->test(Edit::class, ['account' => $account])
            ->set('is_active', true)
            ->call('save');

        $this->assertDatabaseHas('accounts', [
            'id' => $account->id,
            'is_active' => true,
        ]);
    }

    public function test_does_not_change_balances(): void
    {
        $account = Account::factory()->create([
            'initial_balance' => 500_000,
            'current_balance' => 750_000,
        ]);

        Livewire::actingAs($this->user)
            ->test(Edit::class, ['account' => $account])
            ->set('name', 'Renamed')
            ->call('save');

        $fresh = $account->fresh();
        $this->assertSame('500000.00', $fresh->initial_balance);
        $this->assertSame('750000.00', $fresh->current_balance);
    }

    public function test_validation_requires_name(): void
    {
        $account = Account::factory()->create();

        Livewire::actingAs($this->user)
            ->test(Edit::class, ['account' => $account])
            ->set('name', '')
            ->call('save')
            ->assertHasErrors(['name']);
    }

    public function test_validation_requires_valid_type(): void
    {
        $account = Account::factory()->create();

        Livewire::actingAs($this->user)
            ->test(Edit::class, ['account' => $account])
            ->set('type', 'invalid_type')
            ->call('save')
            ->assertHasErrors(['type']);
    }

    public function test_validation_rejects_invalid_color(): void
    {
        $account = Account::factory()->create();

        Livewire::actingAs($this->user)
            ->test(Edit::class, ['account' => $account])
            ->set('color', 'not-a-color')
            ->call('save')
            ->assertHasErrors(['color']);
    }
}
