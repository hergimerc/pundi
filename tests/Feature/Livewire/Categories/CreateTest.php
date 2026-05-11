<?php

namespace Tests\Feature\Livewire\Categories;

use App\Livewire\Categories\Create;
use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CreateTest extends TestCase
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
        $this->get(route('categories.create'))->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_view_create_page(): void
    {
        $this->actingAs($this->user)
            ->get(route('categories.create'))
            ->assertOk();
    }

    public function test_creates_expense_category_and_redirects(): void
    {
        Livewire::actingAs($this->user)
            ->test(Create::class)
            ->set('type', 'expense')
            ->set('name', 'Food')
            ->set('color', '#ef4444')
            ->call('save')
            ->assertRedirect(route('categories.index'));

        $this->assertDatabaseHas('categories', [
            'name' => 'Food',
            'type' => 'expense',
            'color' => '#ef4444',
            'parent_id' => null,
        ]);
    }

    public function test_creates_income_category(): void
    {
        Livewire::actingAs($this->user)
            ->test(Create::class)
            ->set('type', 'income')
            ->set('name', 'Salary')
            ->set('color', '#22c55e')
            ->call('save');

        $this->assertDatabaseHas('categories', [
            'name' => 'Salary',
            'type' => 'income',
        ]);
    }

    public function test_creates_child_category_with_parent(): void
    {
        $parent = Category::factory()->expense()->create(['name' => 'Food']);

        Livewire::actingAs($this->user)
            ->test(Create::class)
            ->set('type', 'expense')
            ->set('name', 'Coffee')
            ->set('parent_id', $parent->id)
            ->call('save');

        $this->assertDatabaseHas('categories', [
            'name' => 'Coffee',
            'parent_id' => $parent->id,
        ]);
    }

    public function test_switching_type_clears_parent(): void
    {
        $parent = Category::factory()->expense()->create();

        Livewire::actingAs($this->user)
            ->test(Create::class)
            ->set('type', 'expense')
            ->set('parent_id', $parent->id)
            ->set('type', 'income')
            ->assertSet('parent_id', null);
    }

    public function test_validation_requires_name(): void
    {
        Livewire::actingAs($this->user)
            ->test(Create::class)
            ->set('type', 'expense')
            ->set('name', '')
            ->call('save')
            ->assertHasErrors('name');
    }

    public function test_default_type_is_expense(): void
    {
        Livewire::actingAs($this->user)
            ->test(Create::class)
            ->assertSet('type', 'expense');
    }

    public function test_color_is_optional(): void
    {
        Livewire::actingAs($this->user)
            ->test(Create::class)
            ->set('type', 'expense')
            ->set('name', 'Misc')
            ->set('color', '')
            ->call('save')
            ->assertHasNoErrors('color');
    }
}
