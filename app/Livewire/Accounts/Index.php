<?php

namespace App\Livewire\Accounts;

use App\Models\Account;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.app')]
#[Title('Accounts')]
class Index extends Component
{
    public function render()
    {
        $accounts = Account::orderBy('name')
            ->get()
            ->groupBy(fn ($a) => $a->type->value);

        return view('livewire.accounts.index', ['accounts' => $accounts]);
    }
}
