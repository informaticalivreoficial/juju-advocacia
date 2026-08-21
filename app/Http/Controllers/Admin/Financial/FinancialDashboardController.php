<?php

namespace App\Http\Controllers\Admin\Financial;

use App\Enums\FinancialStatus;
use App\Enums\FinancialType;
use App\Http\Controllers\Controller;
use App\Models\FinancialTransaction;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class FinancialDashboardController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', FinancialTransaction::class);

        $year = (int) $request->integer('year', now()->year);
        $month = max(1, min(12, (int) $request->integer('month', now()->month)));

        $year = $year >= 2000 ? $year : now()->year;

        $monthTransactions = FinancialTransaction::query()
            ->with('category:id,name,color,type')
            ->forMonth($year, $month)
            ->get();

        $active = $monthTransactions->where('status', '!=', FinancialStatus::Cancelled);

        $income = $active->where('type', FinancialType::Income)->sum('amount');
        $expense = $active->where('type', FinancialType::Expense)->sum('amount');

        $pendingExpenses = $monthTransactions->where('type', FinancialType::Expense)
            ->where('status', FinancialStatus::Pending);
        $pendingIncomes = $monthTransactions->where('type', FinancialType::Income)
            ->where('status', FinancialStatus::Pending);

        $indicators = [
            'pending' => $pendingExpenses->count(),
            'overdue' => $pendingExpenses->filter(fn (FinancialTransaction $t) => $t->isOverdue())->count(),
            'paid' => $monthTransactions->where('status', FinancialStatus::Paid)->count(),
            'expected' => round($pendingIncomes->sum('amount'), 2),
            'received' => $monthTransactions->where('status', FinancialStatus::Received)->count(),
        ];

        $yearly = $this->yearlyChart($year);
        $byCategory = $this->categoryDoughnut($monthTransactions);

        $cashFlow = $this->cashFlowProjection(CarbonImmutable::now()->startOfMonth());
        $comparison = $this->annualComparison($year);

        return Inertia::render('Admin/Financial/Dashboard/Index', [
            'year' => $year,
            'month' => $month,
            'totals' => [
                'income' => round($income, 2),
                'expense' => round($expense, 2),
                'balance' => round($income - $expense, 2),
            ],
            'indicators' => $indicators,
            'chart' => [
                'yearly' => $yearly,
                'byCategory' => $byCategory,
            ],
            'cashFlow' => $cashFlow,
            'comparison' => $comparison,
            'months' => $this->monthOptions(),
        ]);
    }

    private function yearlyChart(int $year): array
    {
        $rows = FinancialTransaction::query()
            ->forYear($year)
            ->where('status', '!=', FinancialStatus::Cancelled->value)
            ->selectRaw('month, type, SUM(amount) as total')
            ->groupBy('month', 'type')
            ->get()
            ->keyBy(fn ($row) => $row->month.'-'.$row->type->value);

        $labels = [];
        $income = [];
        $expense = [];

        foreach (range(1, 12) as $month) {
            $labels[] = CarbonImmutable::createFromDate($year, $month, 1)->translatedFormat('M');
            $income[] = (float) ($rows["{$month}-".FinancialType::Income->value]->total ?? 0);
            $expense[] = (float) ($rows["{$month}-".FinancialType::Expense->value]->total ?? 0);
        }

        return [
            'labels' => $labels,
            'income' => $income,
            'expense' => $expense,
        ];
    }

    private function categoryDoughnut($monthTransactions): array
    {
        $expenses = $monthTransactions->where('type', FinancialType::Expense)
            ->where('status', '!=', FinancialStatus::Cancelled)
            ->groupBy('category_id');

        $labels = [];
        $data = [];
        $colors = [];

        foreach ($expenses as $categoryId => $items) {
            $category = $items->first()->category;

            $labels[] = $category?->name ?? 'Sem categoria';
            $data[] = round((float) $items->sum('amount'), 2);
            $colors[] = $category?->color ?? '#94a3b8';
        }

        return [
            'labels' => $labels,
            'data' => $data,
            'colors' => $colors,
        ];
    }

    private function cashFlowProjection(CarbonImmutable $start): array
    {
        $rows = [];

        foreach (range(0, 5) as $offset) {
            $month = $start->addMonthsNoOverflow($offset);

            $pending = FinancialTransaction::query()
                ->pending()
                ->where(function ($query) use ($month) {
                    $query->where('year', $month->year)->where('month', $month->month);
                })
                ->get()
                ->filter(function (FinancialTransaction $t) use ($month) {
                    $due = $t->dueDate();

                    return $due === null || $due->between($month->startOfMonth(), $month->endOfMonth());
                });

            $income = (float) $pending->where('type', FinancialType::Income)->sum('amount');
            $expense = (float) $pending->where('type', FinancialType::Expense)->sum('amount');

            $rows[] = [
                'label' => $month->translatedFormat('M/Y'),
                'income' => $income,
                'expense' => $expense,
                'balance' => round($income - $expense, 2),
            ];
        }

        return $rows;
    }

    private function annualComparison(int $year): array
    {
        $build = function (int $y) {
            $rows = FinancialTransaction::query()
                ->forYear($y)
                ->where('status', '!=', FinancialStatus::Cancelled->value)
                ->get();

            return [
                'income' => round((float) $rows->where('type', FinancialType::Income)->sum('amount'), 2),
                'expense' => round((float) $rows->where('type', FinancialType::Expense)->sum('amount'), 2),
            ];
        };

        return [
            'current' => ['year' => $year] + $build($year),
            'previous' => ['year' => $year - 1] + $build($year - 1),
        ];
    }

    private function monthOptions(): array
    {
        return collect(range(1, 12))->map(fn (int $month) => [
            'value' => $month,
            'label' => CarbonImmutable::createFromDate(now()->year, $month, 1)->translatedFormat('F'),
        ])->all();
    }
}
