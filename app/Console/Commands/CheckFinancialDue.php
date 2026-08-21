<?php

namespace App\Console\Commands;

use App\Enums\UserRoleEnum;
use App\Models\FinancialTransaction;
use App\Models\User;
use App\Notifications\FinancialDueNotification;
use Illuminate\Console\Command;

class CheckFinancialDue extends Command
{
    protected $signature = 'financial:check-due {--days=3}';

    protected $description = 'Notifica despesas pendentes vencidas ou a vencer em até N dias.';

    public function handle(): int
    {
        $days = max(0, (int) $this->option('days'));
        $limit = today()->addDays($days)->endOfDay();

        $transactions = FinancialTransaction::withoutGlobalScope('user')
            ->expenses()
            ->pending()
            ->with('category:id,name')
            ->get()
            ->filter(function (FinancialTransaction $transaction) use ($limit) {
                $due = $transaction->dueDate();

                if ($due === null) {
                    return false;
                }

                return $due->lte($limit);
            });

        $admins = User::query()
            ->where('role', UserRoleEnum::Admin->value)
            ->where('is_active', true)
            ->get();

        if ($admins->isEmpty()) {
            $this->info('Nenhum administrador ativo para notificar.');

            return self::SUCCESS;
        }

        $notified = 0;

        foreach ($transactions as $transaction) {
            if ($transaction->due_notified_at !== null && $transaction->due_notified_at->gt(now()->subHours(24))) {
                continue;
            }

            foreach ($admins as $admin) {
                $admin->notify(new FinancialDueNotification(
                    $transaction,
                    $transaction->isOverdue() ? 'overdue' : 'soon'
                ));
                $notified++;
            }

            FinancialTransaction::withoutGlobalScope('user')
                ->where('id', $transaction->id)
                ->update(['due_notified_at' => now()]);
        }

        $this->info("Notificações enviadas: {$notified}");

        return self::SUCCESS;
    }
}
