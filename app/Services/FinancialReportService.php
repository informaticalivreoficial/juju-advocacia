<?php

namespace App\Services;

use App\Enums\FinancialStatus;
use App\Enums\FinancialType;
use App\Models\FinancialCategory;
use App\Models\FinancialTransaction;
use Carbon\CarbonImmutable;

class FinancialReportService
{
    /**
     * Constrói o relatório financeiro para o período informado.
     *
     * @return array<string, mixed>
     */
    public function build(int $year, string $period, ?int $month = null, ?int $quarter = null, ?int $half = null): array
    {
        [$start, $end, $label] = $this->periodRange($year, $period, $month, $quarter, $half);

        $transactions = FinancialTransaction::query()
            ->with('category:id,name,color,type')
            ->whereBetween('created_at', [$start, $end])
            ->orderBy('year')
            ->orderBy('month')
            ->orderBy('created_at')
            ->get();

        $income = $transactions->where('type', FinancialType::Income)
            ->where('status', '!=', FinancialStatus::Cancelled)
            ->sum('amount');

        $expense = $transactions->where('type', FinancialType::Expense)
            ->where('status', '!=', FinancialStatus::Cancelled)
            ->sum('amount');

        $categories = FinancialCategory::query()
            ->orderBy('name')
            ->get()
            ->map(function (FinancialCategory $category) use ($transactions) {
                $items = $transactions->where('category_id', $category->id)
                    ->where('status', '!=', FinancialStatus::Cancelled);

                return [
                    'id' => $category->id,
                    'name' => $category->name,
                    'type' => $category->type->value,
                    'color' => $category->color,
                    'income' => $items->where('type', FinancialType::Income)->sum('amount'),
                    'expense' => $items->where('type', FinancialType::Expense)->sum('amount'),
                    'count' => $items->count(),
                ];
            })
            ->filter(fn (array $row) => $row['count'] > 0)
            ->values()
            ->all();

        return [
            'period' => $period,
            'label' => $label,
            'start' => $start->format('Y-m-d'),
            'end' => $end->format('Y-m-d'),
            'income' => round($income, 2),
            'expense' => round($expense, 2),
            'balance' => round($income - $expense, 2),
            'categories' => $categories,
            'transactions' => $transactions->map(function (FinancialTransaction $transaction) {
                return [
                    'id' => $transaction->id,
                    'type' => $transaction->type->value,
                    'status' => $transaction->status->value,
                    'amount' => $transaction->amount,
                    'description' => $transaction->descriptionText(),
                    'category' => $transaction->category?->name,
                    'year' => $transaction->year,
                    'month' => $transaction->month,
                    'paid_at' => $transaction->paid_at?->format('Y-m-d'),
                    'received_at' => $transaction->received_at?->format('Y-m-d'),
                    'due_date' => $transaction->dueDate()?->format('Y-m-d'),
                ];
            })->all(),
            'statusCounts' => [
                'pending' => $transactions->where('status', FinancialStatus::Pending)->count(),
                'paid' => $transactions->where('status', FinancialStatus::Paid)->count(),
                'received' => $transactions->where('status', FinancialStatus::Received)->count(),
                'cancelled' => $transactions->where('status', FinancialStatus::Cancelled)->count(),
            ],
        ];
    }

    /**
     * @return array{0: CarbonImmutable, 1: CarbonImmutable, 2: string}
     */
    private function periodRange(int $year, string $period, ?int $month, ?int $quarter, ?int $half): array
    {
        return match ($period) {
            'monthly' => $this->monthRange($year, $month),
            'quarterly' => $this->quarterRange($year, $quarter),
            'semiannual' => $this->halfRange($year, $half),
            default => $this->yearRange($year),
        };
    }

    /**
     * @return array{0: CarbonImmutable, 1: CarbonImmutable, 2: string}
     */
    private function monthRange(int $year, ?int $month): array
    {
        $month = max(1, min(12, $month ?? now()->month));
        $start = CarbonImmutable::createFromDate($year, $month, 1)->startOfDay();

        return [$start, $start->endOfMonth()->endOfDay(), $start->translatedFormat('F Y')];
    }

    /**
     * @return array{0: CarbonImmutable, 1: CarbonImmutable, 2: string}
     */
    private function quarterRange(int $year, ?int $quarter): array
    {
        $quarter = max(1, min(4, $quarter ?? 1));
        $firstMonth = (($quarter - 1) * 3) + 1;
        $start = CarbonImmutable::createFromDate($year, $firstMonth, 1)->startOfDay();

        return [$start, $start->addMonths(2)->endOfMonth()->endOfDay(), "{$year} · {$quarter}º trimestre"];
    }

    /**
     * @return array{0: CarbonImmutable, 1: CarbonImmutable, 2: string}
     */
    private function halfRange(int $year, ?int $half): array
    {
        $half = max(1, min(2, $half ?? 1));
        $firstMonth = $half === 1 ? 1 : 7;
        $start = CarbonImmutable::createFromDate($year, $firstMonth, 1)->startOfDay();

        return [$start, $start->addMonths(5)->endOfMonth()->endOfDay(), "{$year} · {$half}º semestre"];
    }

    /**
     * @return array{0: CarbonImmutable, 1: CarbonImmutable, 2: string}
     */
    private function yearRange(int $year): array
    {
        $start = CarbonImmutable::createFromDate($year, 1, 1)->startOfDay();

        return [$start, $start->endOfYear()->endOfDay(), "Ano {$year}"];
    }
}
