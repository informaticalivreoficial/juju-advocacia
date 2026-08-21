<?php

namespace Tests\Feature;

use App\Enums\UserRoleEnum;
use App\Models\FinancialTransaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class FinancialDashboardTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->role(UserRoleEnum::Admin)->create();
        $this->actingAs($this->user);
    }

    public function test_dashboard_shows_totals_and_indicators(): void
    {
        FinancialTransaction::factory()->income()->create([
            'user_id' => $this->user->id,
            'year' => 2026,
            'month' => 8,
            'amount' => 5000.00,
            'status' => 'received',
        ]);
        FinancialTransaction::factory()->expense()->create([
            'user_id' => $this->user->id,
            'year' => 2026,
            'month' => 8,
            'amount' => 2000.00,
            'status' => 'paid',
        ]);
        FinancialTransaction::factory()->expense()->create([
            'user_id' => $this->user->id,
            'year' => 2026,
            'month' => 8,
            'amount' => 500.00,
            'status' => 'pending',
            'due_date' => now()->subDays(2)->toDateString(),
        ]);

        $this->get(route('admin.financial.dashboard', ['year' => 2026, 'month' => 8]))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Admin/Financial/Dashboard/Index')
                ->where('totals.income', 5000)
                ->where('totals.expense', 2500)
                ->where('totals.balance', 2500)
                ->where('indicators.pending', 1)
                ->where('indicators.overdue', 1)
                ->where('indicators.received', 1));
    }

    public function test_quick_action_marks_all_expenses_as_paid(): void
    {
        FinancialTransaction::factory()->expense()->create([
            'user_id' => $this->user->id,
            'year' => 2026,
            'month' => 8,
            'status' => 'pending',
        ]);
        FinancialTransaction::factory()->expense()->create([
            'user_id' => $this->user->id,
            'year' => 2026,
            'month' => 8,
            'status' => 'pending',
        ]);

        $this->post(route('admin.financial.transactions.mark-all-expenses-paid'), [
            'year' => 2026,
            'month' => 8,
        ])->assertRedirect();

        $this->assertDatabaseHas('financial_transactions', [
            'user_id' => $this->user->id,
            'year' => 2026,
            'month' => 8,
            'status' => 'paid',
        ]);
        $this->assertDatabaseMissing('financial_transactions', [
            'user_id' => $this->user->id,
            'year' => 2026,
            'month' => 8,
            'status' => 'pending',
        ]);
    }

    public function test_quick_action_marks_all_incomes_as_received(): void
    {
        FinancialTransaction::factory()->income()->create([
            'user_id' => $this->user->id,
            'year' => 2026,
            'month' => 8,
            'status' => 'pending',
        ]);

        $this->post(route('admin.financial.transactions.mark-all-incomes-received'), [
            'year' => 2026,
            'month' => 8,
        ])->assertRedirect();

        $this->assertDatabaseHas('financial_transactions', [
            'user_id' => $this->user->id,
            'year' => 2026,
            'month' => 8,
            'status' => 'received',
        ]);
    }

    public function test_annual_page_shows_monthly_totals(): void
    {
        FinancialTransaction::factory()->income()->create([
            'user_id' => $this->user->id,
            'year' => 2026,
            'month' => 8,
            'amount' => 1000.00,
            'status' => 'received',
        ]);
        FinancialTransaction::factory()->expense()->create([
            'user_id' => $this->user->id,
            'year' => 2026,
            'month' => 8,
            'amount' => 400.00,
            'status' => 'paid',
        ]);

        $this->get(route('admin.financial.annual.index', ['year' => 2026]))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Admin/Financial/Annual/Index')
                ->has('months', 12)
                ->where('totals.income', 1000)
                ->where('totals.expense', 400)
                ->where('totals.balance', 600));
    }

    public function test_report_page_builds_period(): void
    {
        FinancialTransaction::factory()->income()->create([
            'user_id' => $this->user->id,
            'year' => 2026,
            'month' => 8,
            'amount' => 1500.00,
            'status' => 'received',
        ]);

        $this->get(route('admin.financial.reports.index', ['period' => 'monthly', 'year' => 2026, 'month' => 8]))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Admin/Financial/Reports/Index')
                ->where('report.income', 1500)
                ->where('report.period', 'monthly'));
    }

    public function test_report_can_be_exported(): void
    {
        FinancialTransaction::factory()->income()->create([
            'user_id' => $this->user->id,
            'year' => 2026,
            'month' => 8,
            'amount' => 1500.00,
            'status' => 'received',
        ]);

        $this->get(route('admin.financial.reports.export', ['period' => 'monthly', 'year' => 2026, 'month' => 8]))
            ->assertOk()
            ->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
    }
}
