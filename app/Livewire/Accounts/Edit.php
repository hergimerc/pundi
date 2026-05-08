<?php

namespace App\Livewire\Accounts;

use App\Enums\AccountType;
use App\Models\Account;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Validate;
use Livewire\Component;

#[Layout('components.layouts.app')]
#[Title('Edit Account')]
class Edit extends Component
{
    public Account $account;

    #[Validate('required|string|max:255')]
    public string $name = '';

    #[Validate('required|in:cash,bank_account,credit_card')]
    public string $type = '';

    #[Validate('nullable|regex:/^#[0-9a-fA-F]{6}$/')]
    public ?string $color = null;

    #[Validate('required|boolean')]
    public bool $is_active = true;

    public function mount(Account $account): void
    {
        $this->account = $account;
        $this->name = $account->name;
        $this->type = $account->type->value;
        $this->color = $account->color;
        $this->is_active = $account->is_active;
    }

    public function save(): void
    {
        $this->validate();

        $this->account->update([
            'name' => $this->name,
            'type' => AccountType::from($this->type),
            'color' => $this->color,
            'is_active' => $this->is_active,
        ]);

        $this->redirectRoute('accounts.index', navigate: true);
    }

    public function render()
    {
        return view('livewire.accounts.edit', [
            'types' => AccountType::cases(),
        ]);
    }
}
