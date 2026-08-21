<?php

namespace Tests\Feature;

use App\Enums\UserRoleEnum;
use App\Models\FinancialCategory;
use App\Models\FinancialExpense;
use App\Models\FinancialIncome;
use App\Models\FinancialTransaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class FinancialTransactionsTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->role(UserRoleEnum::Admin)->create();
        $this->actingAs($this->user);
    }

    public function test_month_can_be_generated_from_fixed_sources(): void
    {
        FinancialExpense::factory()->create(['user_id' => $this->user->id]);
        FinancialIncome::factory()->create(['user_id' => $this->user->id]);

        $this->post(route('admin.financial.transactions.generate'), [
            'year' => 2026,
            'month' => 8,
        ])->assertRedirect(route('admin.financial.transactions.index', ['year' => 2026, 'month' => 8]));

        $this->assertDatabaseHas('financial_transactions', [
            'user_id' => $this->user->id,
            'year' => 2026,
            'month' => 8,
            'type' => 'expense',
            'status' => 'pending',
        ]);
        $this->assertDatabaseHas('financial_transactions', [
            'user_id' => $this->user->id,
            'year' => 2026,
            'month' => 8,
            'type' => 'income',
            'status' => 'pending',
        ]);
    }

    public function test_inactive_fixed_sources_are_not_generated(): void
    {
        FinancialExpense::factory()->inactive()->create(['user_id' => $this->user->id]);

        $this->post(route('admin.financial.transactions.generate'), ['year' => 2026, 'month' => 8]);

        $this->assertDatabaseCount('financial_transactions', 0);
    }

    public function test_generating_month_is_idempotent(): void
    {
        FinancialExpense::factory()->create(['user_id' => $this->user->id]);

        $this->post(route('admin.financial.transactions.generate'), ['year' => 2026, 'month' => 8]);
        $this->post(route('admin.financial.transactions.generate'), ['year' => 2026, 'month' => 8]);

        $this->assertDatabaseCount('financial_transactions', 1);
    }

    public function test_ad_hoc_transaction_can_be_created_with_attachment(): void
    {
        Storage::fake('public');

        $category = FinancialCategory::factory()->expense()->create(['user_id' => $this->user->id]);

        $this->post(route('admin.financial.transactions.store'), [
            'type' => 'expense',
            'category_id' => $category->id,
            'description' => 'Compra de material',
            'year' => 2026,
            'month' => 8,
            'amount' => 320.50,
            'status' => 'pending',
            'payment_method' => 'pix',
            'due_date' => '2026-08-15',
            'attachment' => UploadedFile::fake()->image('comprovante.png'),
        ])->assertRedirect();

        $this->assertDatabaseHas('financial_transactions', [
            'user_id' => $this->user->id,
            'description' => 'Compra de material',
            'amount' => 320.50,
            'status' => 'pending',
        ]);

        $transaction = FinancialTransaction::where('user_id', $this->user->id)->firstOrFail();
        Storage::disk('public')->assertExists($transaction->attachment_path);
    }

    public function test_paid_status_sets_paid_at(): void
    {
        $transaction = FinancialTransaction::factory()
            ->expense()
            ->create(['user_id' => $this->user->id]);

        $this->patch(route('admin.financial.transactions.status', $transaction->id), ['action' => 'paid']);

        $this->assertDatabaseHas('financial_transactions', [
            'id' => $transaction->id,
            'status' => 'paid',
        ]);

        $transaction->refresh();
        $this->assertNotNull($transaction->paid_at);
    }

    public function test_received_status_sets_received_at(): void
    {
        $transaction = FinancialTransaction::factory()
            ->income()
            ->create(['user_id' => $this->user->id]);

        $this->patch(route('admin.financial.transactions.status', $transaction->id), ['action' => 'received']);

        $this->assertDatabaseHas('financial_transactions', [
            'id' => $transaction->id,
            'status' => 'received',
        ]);

        $transaction->refresh();
        $this->assertNotNull($transaction->received_at);
    }

    public function test_transaction_can_be_cancelled(): void
    {
        $transaction = FinancialTransaction::factory()->create(['user_id' => $this->user->id]);

        $this->patch(route('admin.financial.transactions.status', $transaction->id), ['action' => 'cancel']);

        $this->assertDatabaseHas('financial_transactions', [
            'id' => $transaction->id,
            'status' => 'cancelled',
        ]);
    }

    public function test_cancelled_transaction_cannot_be_marked_paid(): void
    {
        $transaction = FinancialTransaction::factory()
            ->cancelled()
            ->create(['user_id' => $this->user->id]);

        $this->patch(route('admin.financial.transactions.status', $transaction->id), ['action' => 'paid'])
            ->assertSessionHasErrors('transaction');

        $this->assertDatabaseHas('financial_transactions', [
            'id' => $transaction->id,
            'status' => 'cancelled',
        ]);
    }

    public function test_recurring_transaction_cannot_be_deleted(): void
    {
        $expense = FinancialExpense::factory()->create(['user_id' => $this->user->id]);
        $transaction = FinancialTransaction::factory()
            ->expense()
            ->create(['user_id' => $this->user->id, 'expense_id' => $expense->id]);

        $this->delete(route('admin.financial.transactions.destroy', $transaction->id))
            ->assertSessionHasErrors('transaction');

        $this->assertDatabaseHas('financial_transactions', ['id' => $transaction->id]);
    }

    public function test_ad_hoc_transaction_can_be_deleted(): void
    {
        $transaction = FinancialTransaction::factory()->create(['user_id' => $this->user->id]);

        $this->delete(route('admin.financial.transactions.destroy', $transaction->id))
            ->assertRedirect();

        $this->assertDatabaseMissing('financial_transactions', ['id' => $transaction->id]);
    }

    public function test_transactions_page_is_displayed_with_totals(): void
    {
        FinancialTransaction::factory()->expense()->create([
            'user_id' => $this->user->id,
            'year' => 2026,
            'month' => 8,
            'amount' => 100.00,
            'status' => 'paid',
        ]);
        FinancialTransaction::factory()->income()->create([
            'user_id' => $this->user->id,
            'year' => 2026,
            'month' => 8,
            'amount' => 300.00,
            'status' => 'received',
        ]);

        $this->get(route('admin.financial.transactions.index', ['year' => 2026, 'month' => 8]))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Admin/Financial/Transactions/Index')
                ->has('transactions.data', 2)
                ->where('totals.income', 300)
                ->where('totals.expense', 100)
                ->where('totals.balance', 200));
    }

    public function test_transactions_can_be_exported_as_csv(): void
    {
        FinancialTransaction::factory()->expense()->create([
            'user_id' => $this->user->id,
            'year' => 2026,
            'month' => 8,
        ]);

        $this->get(route('admin.financial.transactions.export', ['year' => 2026, 'month' => 8]))
            ->assertOk()
            ->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
    }
}
