<?php

namespace App\Livewire\Categories;

use App\Enums\CategoryType;
use App\Models\Category;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.app')]
#[Title('Categories')]
class Index extends Component
{
    public function delete(Category $category): void
    {
        if ($category->children()->exists()) {
            $this->addError('delete', 'Remove all subcategories before deleting this category.');

            return;
        }

        $category->delete();
        $this->dispatch('flash', message: 'Category deleted.');
    }

    public function render()
    {
        $categories = Category::with('children')
            ->roots()
            ->orderBy('type')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get()
            ->groupBy(fn ($c) => $c->type->value);

        return view('livewire.categories.index', [
            'categories' => $categories,
            'types' => CategoryType::cases(),
        ]);
    }
}
