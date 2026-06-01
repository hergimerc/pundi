<?php

namespace Tests\Feature\Transactions;

use App\Enums\CategoryType;
use App\Livewire\Transactions\Create;
use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CreateCategoryFromTransactionTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_open_category_modal_sets_flag(): void
    {
        Livewire::actingAs($this->user)
            ->test(Create::class)
            ->call('openCategoryModal')
            ->assertSet('showCategoryModal', true)
            ->assertSet('newCategoryName', '')
            ->assertSet('newCategoryParentId', '');
    }

    public function test_create_expense_category_from_modal(): void
    {
        Livewire::actingAs($this->user)
            ->test(Create::class)
            ->set('type', 'expense')
            ->call('openCategoryModal')
            ->set('newCategoryName', 'Transport')
            ->call('createCategory')
            ->assertSet('showCategoryModal', false);

        $category = Category::where('name', 'Transport')->first();
        $this->assertNotNull($category);
        $this->assertSame(CategoryType::Expense, $category->type);
        $this->assertNull($category->parent_id);
    }

    public function test_create_income_category_from_modal(): void
    {
        Livewire::actingAs($this->user)
            ->test(Create::class)
            ->set('type', 'income')
            ->call('openCategoryModal')
            ->set('newCategoryName', 'Freelance')
            ->call('createCategory');

        $category = Category::where('name', 'Freelance')->first();
        $this->assertNotNull($category);
        $this->assertSame(CategoryType::Income, $category->type);
    }

    public function test_new_category_is_auto_selected(): void
    {
        Livewire::actingAs($this->user)
            ->test(Create::class)
            ->set('type', 'expense')
            ->call('openCategoryModal')
            ->set('newCategoryName', 'Food')
            ->call('createCategory')
            ->assertSet('category_id', (string) Category::where('name', 'Food')->value('id'));
    }

    public function test_create_category_with_parent(): void
    {
        $parent = Category::factory()->expense()->create(['name' => 'Food']);

        Livewire::actingAs($this->user)
            ->test(Create::class)
            ->set('type', 'expense')
            ->call('openCategoryModal')
            ->set('newCategoryName', 'Groceries')
            ->set('newCategoryParentId', (string) $parent->id)
            ->call('createCategory');

        $child = Category::where('name', 'Groceries')->first();
        $this->assertNotNull($child);
        $this->assertSame($parent->id, $child->parent_id);
    }

    public function test_create_category_requires_name(): void
    {
        Livewire::actingAs($this->user)
            ->test(Create::class)
            ->call('openCategoryModal')
            ->set('newCategoryName', '')
            ->call('createCategory')
            ->assertHasErrors(['newCategoryName'])
            ->assertSet('showCategoryModal', true);

        $this->assertSame(0, Category::count());
    }

    public function test_switching_type_clears_modal_parent(): void
    {
        $parent = Category::factory()->expense()->create();

        Livewire::actingAs($this->user)
            ->test(Create::class)
            ->set('type', 'expense')
            ->set('newCategoryParentId', (string) $parent->id)
            ->set('type', 'income')
            ->assertSet('newCategoryParentId', '');
    }

    public function test_modal_resets_fields_on_open(): void
    {
        Livewire::actingAs($this->user)
            ->test(Create::class)
            ->set('newCategoryName', 'leftover')
            ->set('newCategoryParentId', '5')
            ->call('openCategoryModal')
            ->assertSet('newCategoryName', '')
            ->assertSet('newCategoryParentId', '');
    }
}
