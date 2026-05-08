<?php

namespace App\Livewire\Accounts;

use App\Enums\AccountType;
use App\Models\Account;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Validate;
use Livewire\Component;

#[Layout('components.layouts.app')]
#[Title('New Account')]
class Create extends Component
{
    #[Validate('required|string|max:255')]
    public string $name = '';

    #[Validate('required|in:cash,bank_account,credit_card')]
    public string $type = '';

    #[Validate('required|numeric|min:-999999999999.99|max:999999999999.99')]
    public string $initial_balance = '0';

    #[Validate('nullable|regex:/^#[0-9a-fA-F]{6}$/')]
    public ?string $color = '#3b82f6';

    public function save(): void
    {
        $this->validate();

        Account::create([
            'name' => $this->name,
            'type' => AccountType::from($this->type),
            'initial_balance' => (float) $this->initial_balance,
            'current_balance' => (float) $this->initial_balance,
            'color' => $this->color,
        ]);

        $this->redirectRoute('accounts.index', navigate: true);
    }

    public function render()
    {
        return view('livewire.accounts.create', [
            'types' => AccountType::cases(),
        ]);
    }
}
