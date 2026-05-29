<?php

namespace Tests\Feature\Livewire\Budgets;

use App\Livewire\Budgets\Create;
use App\Models\Budget;
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
        $this->get(route('budgets.create'))->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_view_create_page(): void
    {
        $this->actingAs($this->user)
            ->get(route('budgets.create'))
            ->assertOk();
    }

    public function test_creates_budget_and_redirects(): void
    {
        Livewire::actingAs($this->user)
            ->test(Create::class)
            ->set('name', 'Groceries')
            ->set('amount', '500000')
            ->set('month', 5)
            ->set('year', 2026)
            ->call('save')
            ->assertRedirect(route('budgets.index'));

        $this->assertDatabaseHas('budgets', [
            'name' => 'Groceries',
            'amount' => '500000.00',
            'month' => 5,
            'year' => 2026,
        ]);
    }

    public function test_creates_budget_with_categories(): void
    {
        $category = Category::factory()->expense()->create();

        Livewire::actingAs($this->user)
            ->test(Create::class)
            ->set('name', 'Food')
            ->set('amount', '1000000')
            ->set('month', now()->month)
            ->set('year', now()->year)
            ->set('categoryIds', [(string) $category->id])
            ->call('save');

        $budget = Budget::where('name', 'Food')->first();
        $this->assertNotNull($budget);
        $this->assertTrue($budget->categories->contains($category));
    }

    public function test_defaults_to_current_month_and_year(): void
    {
        Livewire::actingAs($this->user)
            ->test(Create::class)
            ->assertSet('month', now()->month)
            ->assertSet('year', now()->year);
    }

    public function test_validation_requires_name(): void
    {
        Livewire::actingAs($this->user)
            ->test(Create::class)
            ->set('name', '')
            ->set('amount', '100000')
            ->call('save')
            ->assertHasErrors('name');
    }

    public function test_validation_requires_amount(): void
    {
        Livewire::actingAs($this->user)
            ->test(Create::class)
            ->set('name', 'Test')
            ->set('amount', '')
            ->call('save')
            ->assertHasErrors('amount');
    }

    public function test_validation_rejects_invalid_month(): void
    {
        Livewire::actingAs($this->user)
            ->test(Create::class)
            ->set('name', 'Test')
            ->set('amount', '100000')
            ->set('month', 13)
            ->call('save')
            ->assertHasErrors('month');
    }
}
