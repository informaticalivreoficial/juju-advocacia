<?php

namespace Tests\Feature;

use App\Enums\UserRoleEnum;
use App\Models\FinancialExpense;
use App\Models\FinancialTransaction;
use App\Models\User;
use App\Notifications\FinancialDueNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class CheckFinancialDueTest extends TestCase
{
    use RefreshDatabase;

    public function test_command_notifies_admins_about_due_expenses(): void
    {
        Notification::fake();

        $admin = User::factory()->role(UserRoleEnum::Admin)->create();
        $expense = FinancialExpense::factory()->create(['user_id' => $admin->id]);

        FinancialTransaction::factory()->expense()->create([
            'user_id' => $admin->id,
            'expense_id' => $expense->id,
            'status' => 'pending',
            'due_date' => now()->addDays(2)->toDateString(),
        ]);

        $this->artisan('financial:check-due', ['--days' => 3])->assertSuccessful();

        Notification::assertSentTo($admin, FinancialDueNotification::class);
    }

    public function test_command_does_not_notify_when_no_due_expenses(): void
    {
        Notification::fake();

        $admin = User::factory()->role(UserRoleEnum::Admin)->create();
        $expense = FinancialExpense::factory()->create(['user_id' => $admin->id]);

        FinancialTransaction::factory()->expense()->create([
            'user_id' => $admin->id,
            'expense_id' => $expense->id,
            'status' => 'pending',
            'due_date' => now()->addDays(30)->toDateString(),
        ]);

        $this->artisan('financial:check-due', ['--days' => 3])->assertSuccessful();

        Notification::assertNothingSent();
    }

    public function test_command_skips_already_notified_recently(): void
    {
        Notification::fake();

        $admin = User::factory()->role(UserRoleEnum::Admin)->create();
        $expense = FinancialExpense::factory()->create(['user_id' => $admin->id]);

        FinancialTransaction::factory()->expense()->create([
            'user_id' => $admin->id,
            'expense_id' => $expense->id,
            'status' => 'pending',
            'due_date' => now()->addDays(2)->toDateString(),
            'due_notified_at' => now(),
        ]);

        $this->artisan('financial:check-due', ['--days' => 3])->assertSuccessful();

        Notification::assertNothingSent();
    }

    public function test_command_only_notifies_active_admins(): void
    {
        Notification::fake();

        $admin = User::factory()->role(UserRoleEnum::Admin)->create();
        $inactiveAdmin = User::factory()->role(UserRoleEnum::Admin)->inactive()->create();
        $expense = FinancialExpense::factory()->create(['user_id' => $admin->id]);

        FinancialTransaction::factory()->expense()->create([
            'user_id' => $admin->id,
            'expense_id' => $expense->id,
            'status' => 'pending',
            'due_date' => now()->addDays(2)->toDateString(),
        ]);

        $this->artisan('financial:check-due', ['--days' => 3])->assertSuccessful();

        Notification::assertSentTo($admin, FinancialDueNotification::class);
        Notification::assertNotSentTo($inactiveAdmin, FinancialDueNotification::class);
    }

    public function test_command_marks_transactions_as_notified(): void
    {
        Notification::fake();

        $admin = User::factory()->role(UserRoleEnum::Admin)->create();
        $expense = FinancialExpense::factory()->create(['user_id' => $admin->id]);

        $transaction = FinancialTransaction::factory()->expense()->create([
            'user_id' => $admin->id,
            'expense_id' => $expense->id,
            'status' => 'pending',
            'due_date' => now()->addDays(2)->toDateString(),
        ]);

        $this->assertNull($transaction->due_notified_at);

        $this->artisan('financial:check-due', ['--days' => 3])->assertSuccessful();

        $this->assertNotNull($transaction->fresh()->due_notified_at);
    }
}
