<?php

namespace Tests\Feature\Livewire\Budgets;

use App\Livewire\Budgets\Edit;
use App\Models\Budget;
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
        $budget = Budget::factory()->create();
        $this->get(route('budgets.edit', $budget))->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_view_edit_page(): void
    {
        $budget = Budget::factory()->create();

        $this->actingAs($this->user)
            ->get(route('budgets.edit', $budget))
            ->assertOk();
    }

    public function test_mounts_with_budget_values(): void
    {
        $budget = Budget::factory()->create([
            'name' => 'Groceries',
            'amount' => '750000.00',
            'month' => 3,
            'year' => 2026,
        ]);

        Livewire::actingAs($this->user)
            ->test(Edit::class, ['budget' => $budget])
            ->assertSet('name', 'Groceries')
            ->assertSet('amount', '750000.00')
            ->assertSet('month', 3)
            ->assertSet('year', 2026);
    }

    public function test_mounts_with_existing_category_ids(): void
    {
        $category = Category::factory()->expense()->create();
        $budget = Budget::factory()->create();
        $budget->categories()->attach($category);

        $component = Livewire::actingAs($this->user)
            ->test(Edit::class, ['budget' => $budget]);

        $this->assertContains((string) $category->id, $component->get('categoryIds'));
    }

    public function test_updates_budget_and_redirects(): void
    {
        $budget = Budget::factory()->create(['name' => 'Old Name']);

        Livewire::actingAs($this->user)
            ->test(Edit::class, ['budget' => $budget])
            ->set('name', 'New Name')
            ->set('amount', '900000')
            ->call('save')
            ->assertRedirect(route('budgets.index'));

        $this->assertDatabaseHas('budgets', [
            'id' => $budget->id,
            'name' => 'New Name',
            'amount' => '900000.00',
        ]);
    }

    public function test_syncs_categories_on_update(): void
    {
        $oldCategory = Category::factory()->expense()->create();
        $newCategory = Category::factory()->expense()->create();
        $budget = Budget::factory()->create();
        $budget->categories()->attach($oldCategory);

        Livewire::actingAs($this->user)
            ->test(Edit::class, ['budget' => $budget])
            ->set('categoryIds', [(string) $newCategory->id])
            ->call('save');

        $budget->refresh();
        $this->assertFalse($budget->categories->contains($oldCategory));
        $this->assertTrue($budget->categories->contains($newCategory));
    }

    public function test_validation_requires_name(): void
    {
        $budget = Budget::factory()->create();

        Livewire::actingAs($this->user)
            ->test(Edit::class, ['budget' => $budget])
            ->set('name', '')
            ->call('save')
            ->assertHasErrors('name');
    }

    public function test_validation_requires_amount(): void
    {
        $budget = Budget::factory()->create();

        Livewire::actingAs($this->user)
            ->test(Edit::class, ['budget' => $budget])
            ->set('amount', '')
            ->call('save')
            ->assertHasErrors('amount');
    }
}
