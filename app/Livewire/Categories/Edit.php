<?php

namespace App\Livewire\Categories;

use App\Models\Category;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Validate;
use Livewire\Component;

#[Layout('components.layouts.app')]
#[Title('Edit Category')]
class Edit extends Component
{
    public Category $category;

    #[Validate('required|string|max:255')]
    public string $name = '';

    #[Validate('nullable|regex:/^#[0-9a-fA-F]{6}$/')]
    public ?string $color = null;

    #[Validate('nullable|exists:categories,id')]
    public string $parent_id = '';

    public function mount(Category $category): void
    {
        $this->category = $category;
        $this->name = $category->name;
        $this->color = $category->color;
        $this->parent_id = $category->parent_id !== null ? (string) $category->parent_id : '';
    }

    public function save(): void
    {
        $this->validate();

        $this->category->update([
            'name' => $this->name,
            'color' => $this->color,
            'parent_id' => $this->parent_id !== '' ? (int) $this->parent_id : null,
        ]);

        $this->redirectRoute('categories.index', navigate: true);
    }

    public function render()
    {
        return view('livewire.categories.edit', [
            'parents' => Category::ofType($this->category->type)
                ->roots()
                ->where('id', '!=', $this->category->id)
                ->orderBy('name')
                ->get(),
        ]);
    }
}
