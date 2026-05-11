<?php

namespace Tests\Feature\Livewire\Categories;

use App\Livewire\Categories\Edit;
use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class EditTest extends TestCase
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
        $category = Category::factory()->expense()->create();

        $this->get(route('categories.edit', $category))->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_view_edit_page(): void
    {
        $category = Category::factory()->expense()->create();

        $this->actingAs($this->user)
            ->get(route('categories.edit', $category))
            ->assertOk();
    }

    public function test_form_pre_fills_with_current_values(): void
    {
        $category = Category::factory()->expense()->create([
            'name' => 'Food',
            'color' => '#ef4444',
        ]);

        Livewire::actingAs($this->user)
            ->test(Edit::class, ['category' => $category])
            ->assertSet('name', 'Food')
            ->assertSet('color', '#ef4444');
    }

    public function test_saves_updated_name_and_redirects(): void
    {
        $category = Category::factory()->expense()->create(['name' => 'Old Name']);

        Livewire::actingAs($this->user)
            ->test(Edit::class, ['category' => $category])
            ->set('name', 'New Name')
            ->call('save')
            ->assertRedirect(route('categories.index'));

        $this->assertDatabaseHas('categories', ['id' => $category->id, 'name' => 'New Name']);
    }

    public function test_saves_updated_color(): void
    {
        $category = Category::factory()->expense()->create(['color' => '#ef4444']);

        Livewire::actingAs($this->user)
            ->test(Edit::class, ['category' => $category])
            ->set('color', '#3b82f6')
            ->call('save');

        $this->assertDatabaseHas('categories', ['id' => $category->id, 'color' => '#3b82f6']);
    }

    public function test_can_assign_parent_category(): void
    {
        $parent = Category::factory()->expense()->create(['name' => 'Food']);
        $child = Category::factory()->expense()->create(['name' => 'Coffee']);

        Livewire::actingAs($this->user)
            ->test(Edit::class, ['category' => $child])
            ->set('parent_id', $parent->id)
            ->call('save');

        $this->assertDatabaseHas('categories', ['id' => $child->id, 'parent_id' => $parent->id]);
    }

    public function test_can_remove_parent_category(): void
    {
        $parent = Category::factory()->expense()->create();
        $child = Category::factory()->childOf($parent)->create();

        Livewire::actingAs($this->user)
            ->test(Edit::class, ['category' => $child])
            ->set('parent_id', null)
            ->call('save');

        $this->assertDatabaseHas('categories', ['id' => $child->id, 'parent_id' => null]);
    }

    public function test_validation_requires_name(): void
    {
        $category = Category::factory()->expense()->create();

        Livewire::actingAs($this->user)
            ->test(Edit::class, ['category' => $category])
            ->set('name', '')
            ->call('save')
            ->assertHasErrors('name');
    }

    public function test_parent_list_excludes_self(): void
    {
        $category = Category::factory()->expense()->create(['name' => 'Food']);

        $component = Livewire::actingAs($this->user)
            ->test(Edit::class, ['category' => $category]);

        $parents = $component->viewData('parents');

        $this->assertFalse($parents->contains('id', $category->id));
    }
}
