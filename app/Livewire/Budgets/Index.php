<?php

namespace App\Livewire\Budgets;

use App\Models\Budget;
use Carbon\Carbon;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.app')]
#[Title('Budgets')]
class Index extends Component
{
    public int $month;

    public int $year;

    public function mount(): void
    {
        $this->month = (int) now()->month;
        $this->year = (int) now()->year;
    }

    public function previousMonth(): void
    {
        $date = Carbon::createFromDate($this->year, $this->month, 1)->subMonth();
        $this->month = $date->month;
        $this->year = $date->year;
    }

    public function nextMonth(): void
    {
        $date = Carbon::createFromDate($this->year, $this->month, 1)->addMonth();
        $this->month = $date->month;
        $this->year = $date->year;
    }

    public function delete(Budget $budget): void
    {
        $budget->delete();
    }

    public function render()
    {
        $budgets = Budget::with(['categories.children'])
            ->where('month', $this->month)
            ->where('year', $this->year)
            ->orderBy('name')
            ->get();

        return view('livewire.budgets.index', [
            'budgets' => $budgets,
        ]);
    }
}
