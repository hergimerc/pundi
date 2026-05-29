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
#[Title('Edit Budget')]
class Edit extends Component
{
    public Budget $budget;

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

    public function mount(Budget $budget): void
    {
        $this->budget = $budget;
        $this->name = $budget->name;
        $this->amount = (string) $budget->amount;
        $this->month = $budget->month;
        $this->year = $budget->year;
        $this->categoryIds = $budget->categories->pluck('id')->map(fn ($id) => (string) $id)->toArray();
    }

    public function save(): void
    {
        $this->validate();

        $this->budget->update([
            'name' => $this->name,
            'amount' => (float) $this->amount,
            'month' => $this->month,
            'year' => $this->year,
        ]);

        $this->budget->categories()->sync($this->categoryIds);

        $this->redirectRoute('budgets.index', navigate: true);
    }

    public function render()
    {
        return view('livewire.budgets.edit', [
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
