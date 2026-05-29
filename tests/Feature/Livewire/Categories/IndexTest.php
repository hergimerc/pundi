<?php

namespace Tests\Feature\Livewire\Categories;

use App\Livewire\Categories\Index;
use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class IndexTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_redirects_guests_to_login(): void
    {
        $this->get(route('categories.index'))->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_view_categories_page(): void
    {
        $this->actingAs($this->user)
            ->get(route('categories.index'))
            ->assertOk();
    }

    public function test_shows_empty_state_when_no_categories(): void
    {
        Livewire::actingAs($this->user)
            ->test(Index::class)
            ->assertSee('No categories yet.')
            ->assertSee('Add your first category');
    }

    public function test_shows_expense_categories(): void
    {
        Category::factory()->expense()->create(['name' => 'Food']);

        Livewire::actingAs($this->user)
            ->test(Index::class)
            ->assertSee('Food')
            ->assertSee('Expense');
    }

    public function test_shows_income_categories(): void
    {
        Category::factory()->income()->create(['name' => 'Salary']);

        Livewire::actingAs($this->user)
            ->test(Index::class)
            ->assertSee('Salary')
            ->assertSee('Income');
    }

    public function test_shows_children_under_parent(): void
    {
        $parent = Category::factory()->expense()->create(['name' => 'Food']);
        Category::factory()->childOf($parent)->create(['name' => 'Coffee']);

        Livewire::actingAs($this->user)
            ->test(Index::class)
            ->assertSee('Food')
            ->assertSee('Coffee');
    }

    public function test_delete_soft_deletes_category(): void
    {
        $category = Category::factory()->expense()->create();

        Livewire::actingAs($this->user)
            ->test(Index::class)
            ->call('delete', $category->id);

        $this->assertSoftDeleted('categories', ['id' => $category->id]);
    }

    public function test_delete_blocked_when_category_has_children(): void
    {
        $parent = Category::factory()->expense()->create(['name' => 'Food']);
        Category::factory()->childOf($parent)->create(['name' => 'Coffee']);

        Livewire::actingAs($this->user)
            ->test(Index::class)
            ->call('delete', $parent->id)
            ->assertHasErrors('delete');

        $this->assertNotSoftDeleted('categories', ['id' => $parent->id]);
    }

    public function test_deleted_categories_are_not_shown(): void
    {
        $category = Category::factory()->expense()->create(['name' => 'Hidden']);
        $category->delete();

        Livewire::actingAs($this->user)
            ->test(Index::class)
            ->assertDontSee('Hidden');
    }
}
