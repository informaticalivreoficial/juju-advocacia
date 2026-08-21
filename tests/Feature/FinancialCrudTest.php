<?php

namespace Tests\Feature;

use App\Enums\UserRoleEnum;
use App\Models\FinancialBudget;
use App\Models\FinancialCategory;
use App\Models\FinancialExpense;
use App\Models\FinancialTransaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class FinancialCrudTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->role(UserRoleEnum::Admin)->create();
        $this->actingAs($this->user);
    }

    public function test_expense_category_can_be_created(): void
    {
        $this->post(route('admin.financial.categories.store'), [
            'name' => 'Material de escritório',
            'type' => 'expense',
            'color' => '#6366f1',
            'active' => true,
        ])->assertRedirect(route('admin.financial.categories.index'));

        $this->assertDatabaseHas('financial_categories', [
            'user_id' => $this->user->id,
            'name' => 'Material de escritório',
            'type' => 'expense',
        ]);
    }

    public function test_category_requires_valid_data(): void
    {
        $this->post(route('admin.financial.categories.store'), [
            'name' => '',
            'type' => 'invalido',
            'color' => '',
        ])->assertSessionHasErrors(['name', 'type', 'color']);

        $this->assertDatabaseCount('financial_categories', 0);
    }

    public function test_fixed_expense_can_be_created_and_updated(): void
    {
        $category = FinancialCategory::factory()->expense()->create(['user_id' => $this->user->id]);

        $this->post(route('admin.financial.expenses.store'), [
            'category_id' => $category->id,
            'description' => 'Aluguel do escritório',
            'due_day' => 10,
            'amount' => 1500.00,
            'active' => true,
        ])->assertRedirect(route('admin.financial.expenses.index'));

        $expense = FinancialExpense::where('user_id', $this->user->id)->firstOrFail();

        $this->put(route('admin.financial.expenses.update', $expense->id), [
            'category_id' => $category->id,
            'description' => 'Aluguel do escritório (novo valor)',
            'due_day' => 12,
            'amount' => 1650.00,
            'active' => true,
        ])->assertRedirect(route('admin.financial.expenses.index'));

        $this->assertDatabaseHas('financial_expenses', [
            'id' => $expense->id,
            'description' => 'Aluguel do escritório (novo valor)',
            'due_day' => 12,
            'amount' => 1650.00,
        ]);
    }

    public function test_fixed_income_can_be_created(): void
    {
        $category = FinancialCategory::factory()->income()->create(['user_id' => $this->user->id]);

        $this->post(route('admin.financial.incomes.store'), [
            'category_id' => $category->id,
            'description' => 'Honorários mensais',
            'receive_day' => 5,
            'amount' => 8000.00,
            'active' => true,
        ])->assertRedirect(route('admin.financial.incomes.index'));

        $this->assertDatabaseHas('financial_incomes', [
            'user_id' => $this->user->id,
            'description' => 'Honorários mensais',
            'amount' => 8000.00,
        ]);
    }

    public function test_budget_can_be_set_and_updated(): void
    {
        $category = FinancialCategory::factory()->expense()->create(['user_id' => $this->user->id]);

        $this->post(route('admin.financial.budgets.store'), [
            'category_id' => $category->id,
            'year' => 2026,
            'month' => 8,
            'amount' => 2000.00,
        ])->assertRedirect(route('admin.financial.categories.index', ['year' => 2026, 'month' => 8]));

        $this->assertDatabaseHas('financial_budgets', [
            'user_id' => $this->user->id,
            'category_id' => $category->id,
            'year' => 2026,
            'month' => 8,
            'amount' => 2000.00,
        ]);

        $this->post(route('admin.financial.budgets.store'), [
            'category_id' => $category->id,
            'year' => 2026,
            'month' => 8,
            'amount' => 2500.00,
        ]);

        $this->assertDatabaseCount('financial_budgets', 1);
        $this->assertDatabaseHas('financial_budgets', ['amount' => 2500.00]);
    }

    public function test_category_with_transactions_cannot_be_deleted(): void
    {
        $category = FinancialCategory::factory()->expense()->create(['user_id' => $this->user->id]);

        FinancialTransaction::factory()
            ->expense()
            ->create(['user_id' => $this->user->id, 'category_id' => $category->id]);

        $this->delete(route('admin.financial.categories.destroy', $category->id))
            ->assertSessionHasErrors('category');

        $this->assertDatabaseHas('financial_categories', ['id' => $category->id]);
    }

    public function test_categories_page_lists_budget_progress(): void
    {
        $category = FinancialCategory::factory()->expense()->create(['user_id' => $this->user->id]);
        FinancialBudget::factory()->create([
            'user_id' => $this->user->id,
            'category_id' => $category->id,
            'year' => 2026,
            'month' => 8,
            'amount' => 1000.00,
        ]);

        $this->get(route('admin.financial.categories.index', ['year' => 2026, 'month' => 8]))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Admin/Financial/Categories/Index')
                ->has('categories.expense', 1));
    }

    public function test_expenses_page_is_displayed(): void
    {
        FinancialExpense::factory()->create(['user_id' => $this->user->id]);

        $this->get(route('admin.financial.expenses.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Admin/Financial/Expenses/Index')
                ->has('expenses.data', 1));
    }
}
