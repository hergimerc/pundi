<?php

namespace App\Livewire\Transfers;

use App\Models\Transfer;
use App\Services\TransferService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.app')]
#[Title('Transfer Detail')]
class Show extends Component
{
    public Transfer $transfer;

    public function mount(Transfer $transfer): void
    {
        $this->transfer = $transfer->load(['fromAccount', 'toAccount']);
    }

    public function delete(TransferService $service): void
    {
        $service->reverse($this->transfer);

        $this->redirectRoute('transactions.index', navigate: true);
    }

    public function render()
    {
        return view('livewire.transfers.show');
    }
}
