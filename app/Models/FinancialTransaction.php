<?php

namespace App\Models;

use App\Enums\FinancialPaymentMethod;
use App\Enums\FinancialStatus;
use App\Enums\FinancialType;
use App\Traits\BelongsToUser;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FinancialTransaction extends Model
{
    use BelongsToUser, HasFactory;

    protected $fillable = [
        'user_id',
        'type',
        'category_id',
        'expense_id',
        'income_id',
        'year',
        'month',
        'amount',
        'status',
        'paid_at',
        'received_at',
        'notes',
        'description',
        'payment_method',
        'due_date',
        'attachment_path',
        'due_notified_at',
    ];

    protected $casts = [
        'type' => FinancialType::class,
        'status' => FinancialStatus::class,
        'amount' => 'decimal:2',
        'paid_at' => 'datetime',
        'received_at' => 'datetime',
        'due_date' => 'date',
        'due_notified_at' => 'datetime',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(FinancialCategory::class);
    }

    public function expense(): BelongsTo
    {
        return $this->belongsTo(FinancialExpense::class);
    }

    public function income(): BelongsTo
    {
        return $this->belongsTo(FinancialIncome::class);
    }

    public function scopeExpenses(Builder $query): Builder
    {
        return $query->where('type', FinancialType::Expense->value);
    }

    public function scopeIncomes(Builder $query): Builder
    {
        return $query->where('type', FinancialType::Income->value);
    }

    public function scopeForYear(Builder $query, int $year): Builder
    {
        return $query->where('year', $year);
    }

    public function scopeForMonth(Builder $query, int $year, int $month): Builder
    {
        return $query->where('year', $year)->where('month', $month);
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', FinancialStatus::Pending->value);
    }

    public function isExpense(): bool
    {
        return $this->type === FinancialType::Expense;
    }

    public function isIncome(): bool
    {
        return $this->type === FinancialType::Income;
    }

    public function isOverdue(): bool
    {
        $due = $this->dueDate();

        return $this->status === FinancialStatus::Pending && $due !== null && $due->isBefore(today());
    }

    public function isAdHoc(): bool
    {
        return $this->expense_id === null && $this->income_id === null;
    }

    public function dueDate(): ?Carbon
    {
        return $this->resolveDate($this->expense?->due_day);
    }

    public function receiveDate(): ?Carbon
    {
        return $this->resolveDate($this->income?->receive_day);
    }

    private function resolveDate(?int $day): ?Carbon
    {
        if ($this->due_date !== null) {
            return $this->due_date;
        }

        if ($day !== null && $day >= 1 && $day <= 31) {
            return Carbon::createFromDate($this->year, $this->month, $day);
        }

        return null;
    }

    public function daysUntilDue(): int
    {
        $due = $this->dueDate();

        return $due !== null ? (int) today()->diffInDays($due, false) : 0;
    }

    public function descriptionText(): string
    {
        return $this->description
            ?? $this->expense?->description
            ?? $this->income?->description
            ?? '—';
    }

    public function paymentMethodLabel(): ?string
    {
        if ($this->payment_method === null) {
            return null;
        }

        return FinancialPaymentMethod::tryFrom($this->payment_method)?->label();
    }

    public function markAsPaid(): void
    {
        $this->assertNotCancelled('marcar como pago');

        $this->status = FinancialStatus::Paid;
        $this->paid_at = now();
        $this->save();
    }

    public function undoPayment(): void
    {
        $this->assertStatus(FinancialStatus::Paid, 'Somente lançamentos pagos podem ter o pagamento desfeito.');

        $this->status = FinancialStatus::Pending;
        $this->paid_at = null;
        $this->save();
    }

    public function markAsReceived(): void
    {
        $this->assertNotCancelled('marcar como recebido');

        $this->status = FinancialStatus::Received;
        $this->received_at = now();
        $this->save();
    }

    public function undoReceipt(): void
    {
        $this->assertStatus(FinancialStatus::Received, 'Somente lançamentos recebidos podem ter o recebimento desfeito.');

        $this->status = FinancialStatus::Pending;
        $this->received_at = null;
        $this->save();
    }

    public function cancelTransaction(): void
    {
        $this->assertStatus(FinancialStatus::Pending, 'Somente lançamentos pendentes podem ser cancelados.');

        $this->status = FinancialStatus::Cancelled;
        $this->save();
    }

    private function assertNotCancelled(string $action): void
    {
        if ($this->status === FinancialStatus::Cancelled) {
            throw new \Exception("Não é possível {$action} um lançamento cancelado.");
        }
    }

    private function assertStatus(FinancialStatus $expected, string $message): void
    {
        if ($this->status !== $expected) {
            throw new \Exception($message);
        }
    }

    public static function generateForMonth(int $year, int $month): int
    {
        $userId = auth()->id();

        if ($userId === null) {
            return 0;
        }

        $count = 0;

        FinancialExpense::withoutGlobalScope('user')
            ->where('user_id', $userId)
            ->where('active', true)
            ->each(function (FinancialExpense $expense) use ($userId, $year, $month, &$count) {
                $created = self::withoutGlobalScope('user')->firstOrCreate(
                    [
                        'user_id' => $userId,
                        'type' => FinancialType::Expense->value,
                        'year' => $year,
                        'month' => $month,
                        'expense_id' => $expense->id,
                    ],
                    [
                        'category_id' => $expense->category_id,
                        'amount' => $expense->amount,
                        'status' => FinancialStatus::Pending->value,
                        'due_date' => Carbon::createFromDate($year, $month, $expense->due_day),
                    ]
                );

                if ($created->wasRecentlyCreated) {
                    $count++;
                }
            });

        FinancialIncome::withoutGlobalScope('user')
            ->where('user_id', $userId)
            ->where('active', true)
            ->each(function (FinancialIncome $income) use ($userId, $year, $month, &$count) {
                $created = self::withoutGlobalScope('user')->firstOrCreate(
                    [
                        'user_id' => $userId,
                        'type' => FinancialType::Income->value,
                        'year' => $year,
                        'month' => $month,
                        'income_id' => $income->id,
                    ],
                    [
                        'category_id' => $income->category_id,
                        'amount' => $income->amount,
                        'status' => FinancialStatus::Pending->value,
                        'due_date' => Carbon::createFromDate($year, $month, $income->receive_day),
                    ]
                );

                if ($created->wasRecentlyCreated) {
                    $count++;
                }
            });

        return $count;
    }

    public static function generateForYear(int $year): int
    {
        $count = 0;

        for ($month = 1; $month <= 12; $month++) {
            $count += static::generateForMonth($year, $month);
        }

        return $count;
    }
}
