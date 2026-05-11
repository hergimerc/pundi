<?php

namespace App\Livewire\Categories;

use App\Enums\CategoryType;
use App\Models\Category;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Validate;
use Livewire\Component;

#[Layout('components.layouts.app')]
#[Title('New Category')]
class Create extends Component
{
    #[Validate('required|string|max:255')]
    public string $name = '';

    #[Validate('required|in:expense,income')]
    public string $type = 'expense';

    #[Validate('nullable|regex:/^#[0-9a-fA-F]{6}$/')]
    public ?string $color = null;

    #[Validate('nullable|exists:categories,id')]
    public string $parent_id = '';

    public function updatedType(): void
    {
        $this->parent_id = '';
    }

    public function save(): void
    {
        $this->validate();

        Category::create([
            'name' => $this->name,
            'type' => CategoryType::from($this->type),
            'color' => $this->color,
            'parent_id' => $this->parent_id !== '' ? (int) $this->parent_id : null,
            'sort_order' => 0,
        ]);

        $this->redirectRoute('categories.index', navigate: true);
    }

    public function render()
    {
        $categoryType = CategoryType::from($this->type);

        return view('livewire.categories.create', [
            'types' => CategoryType::cases(),
            'parents' => Category::ofType($categoryType)->roots()->orderBy('name')->get(),
        ]);
    }
}
