<?php

namespace App\Livewire\Budgets;

use App\Enums\CategoryType;
use App\Models\Budget;
use App\Models\Category;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Validate;
use Livewire\Component;

#[Layout('components.layouts.app')]
#[Title('New Budget')]
class Create extends Component
{
    #[Validate('required|string|max:255')]
    public string $name = '';

    #[Validate('required|numeric|min:0')]
    public string $amount = '';

    #[Validate('required|integer|min:1|max:12')]
    public int $month;

    #[Validate('required|integer|min:2000|max:2100')]
    public int $year;

    #[Validate('array')]
    public array $categoryIds = [];

    public function mount(): void
    {
        $this->month = (int) now()->month;
        $this->year = (int) now()->year;
    }

    public function save(): void
    {
        $this->validate();

        $budget = Budget::create([
            'name' => $this->name,
            'amount' => (float) $this->amount,
            'month' => $this->month,
            'year' => $this->year,
        ]);

        $budget->categories()->sync($this->categoryIds);

        $this->dispatch('flash', message: 'Budget saved.');
        $this->redirectRoute('budgets.index', navigate: true);
    }

    public function render()
    {
        return view('livewire.budgets.create', [
            'categories' => Category::ofType(CategoryType::Expense)
                ->with('children')
                ->roots()
                ->orderBy('name')
                ->get(),
            'months' => range(1, 12),
            'years' => range((int) now()->year - 2, (int) now()->year + 1),
        ]);
    }
}
