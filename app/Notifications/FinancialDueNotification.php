<?php

namespace App\Notifications;

use App\Models\FinancialTransaction;
use App\Notifications\Channels\TelegramChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class FinancialDueNotification extends Notification
{
    use Queueable;

    public function __construct(
        public readonly FinancialTransaction $transaction,
        public readonly string $type = 'soon', // overdue | soon
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', TelegramChannel::class];
    }

    public function toArray(object $notifiable): array
    {
        $overdue = $this->transaction->isOverdue();

        return [
            'title' => $overdue ? 'Despesa vencida' : 'Despesa a vencer',
            'message' => "{$this->transaction->descriptionText()} — {$this->formatAmount()} (vence em {$this->dueLabel()})",
            'transaction_id' => $this->transaction->id,
            'due_date' => $this->transaction->dueDate()?->format('Y-m-d'),
            'amount' => $this->transaction->amount,
            'url' => route('admin.financial.transactions.index', [
                'year' => $this->transaction->year,
                'month' => $this->transaction->month,
            ]),
        ];
    }

    public function toTelegram(object $notifiable): string
    {
        $overdue = $this->transaction->isOverdue();
        $emoji = $overdue ? '🔴' : '⚠️';

        return "<b>{$emoji} {$this->transaction->descriptionText()}</b>\n"
            ."Valor: <b>{$this->formatAmount()}</b>\n"
            ."Vencimento: {$this->dueLabel()}\n"
            .'Categoria: '.($this->transaction->category?->name ?? '—')."\n"
            .'<a href="'.route('admin.financial.transactions.index').'">Ver lançamentos</a>';
    }

    private function formatAmount(): string
    {
        return 'R$ '.number_format((float) $this->transaction->amount, 2, ',', '.');
    }

    private function dueLabel(): string
    {
        return $this->transaction->dueDate()?->format('d/m/Y') ?? '—';
    }
}
