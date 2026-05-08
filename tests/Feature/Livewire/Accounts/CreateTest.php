<?php

namespace Tests\Feature\Livewire\Accounts;

use App\Enums\AccountType;
use App\Livewire\Accounts\Create;
use App\Models\Account;
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

    public function test_redirects_guests_to_login(): void
    {
        $this->get(route('accounts.create'))->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_view_create_page(): void
    {
        $this->actingAs($this->user)
            ->get(route('accounts.create'))
            ->assertOk();
    }

    public function test_can_create_account_with_valid_data(): void
    {
        Livewire::actingAs($this->user)
            ->test(Create::class)
            ->set('name', 'BCA Savings')
            ->set('type', AccountType::BankAccount->value)
            ->set('initial_balance', '5000000')
            ->set('color', '#3b82f6')
            ->call('save')
            ->assertHasNoErrors()
            ->assertRedirect(route('accounts.index'));

        $account = Account::first();
        $this->assertNotNull($account);
        $this->assertSame('BCA Savings', $account->name);
        $this->assertSame(AccountType::BankAccount, $account->type);
        $this->assertSame('5000000.00', $account->initial_balance);
        $this->assertSame('5000000.00', $account->current_balance);
        $this->assertSame('#3b82f6', $account->color);
    }

    public function test_current_balance_equals_initial_balance_on_creation(): void
    {
        Livewire::actingAs($this->user)
            ->test(Create::class)
            ->set('name', 'Cash')
            ->set('type', AccountType::Cash->value)
            ->set('initial_balance', '250000')
            ->call('save');

        $account = Account::first();
        $this->assertSame($account->initial_balance, $account->current_balance);
    }

    public function test_name_is_required(): void
    {
        Livewire::actingAs($this->user)
            ->test(Create::class)
            ->set('name', '')
            ->set('type', AccountType::Cash->value)
            ->call('save')
            ->assertHasErrors(['name' => 'required']);
    }

    public function test_type_is_required(): void
    {
        Livewire::actingAs($this->user)
            ->test(Create::class)
            ->set('name', 'Wallet')
            ->set('type', '')
            ->call('save')
            ->assertHasErrors(['type' => 'required']);
    }

    public function test_type_must_be_valid(): void
    {
        Livewire::actingAs($this->user)
            ->test(Create::class)
            ->set('name', 'Wallet')
            ->set('type', 'invalid_type')
            ->call('save')
            ->assertHasErrors(['type']);
    }

    public function test_initial_balance_is_required(): void
    {
        Livewire::actingAs($this->user)
            ->test(Create::class)
            ->set('name', 'Wallet')
            ->set('type', AccountType::Cash->value)
            ->set('initial_balance', '')
            ->call('save')
            ->assertHasErrors(['initial_balance' => 'required']);
    }

    public function test_initial_balance_must_be_numeric(): void
    {
        Livewire::actingAs($this->user)
            ->test(Create::class)
            ->set('name', 'Wallet')
            ->set('type', AccountType::Cash->value)
            ->set('initial_balance', 'abc')
            ->call('save')
            ->assertHasErrors(['initial_balance' => 'numeric']);
    }

    public function test_color_must_be_valid_hex(): void
    {
        Livewire::actingAs($this->user)
            ->test(Create::class)
            ->set('name', 'Wallet')
            ->set('type', AccountType::Cash->value)
            ->set('initial_balance', '0')
            ->set('color', 'not-a-color')
            ->call('save')
            ->assertHasErrors(['color']);
    }

    public function test_negative_initial_balance_is_allowed(): void
    {
        Livewire::actingAs($this->user)
            ->test(Create::class)
            ->set('name', 'Credit Card')
            ->set('type', AccountType::CreditCard->value)
            ->set('initial_balance', '-500000')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertSame('-500000.00', Account::first()->initial_balance);
    }

    public function test_account_is_active_by_default(): void
    {
        Livewire::actingAs($this->user)
            ->test(Create::class)
            ->set('name', 'Wallet')
            ->set('type', AccountType::Cash->value)
            ->set('initial_balance', '0')
            ->call('save');

        $this->assertTrue(Account::first()->is_active);
    }
}
