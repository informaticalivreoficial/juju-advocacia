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

class FinancialAnnualController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', FinancialTransaction::class);

        $year = (int) $request->integer('year', now()->year);
        $year = $year >= 2000 ? $year : now()->year;

        $rows = FinancialTransaction::query()
            ->forYear($year)
            ->where('status', '!=', FinancialStatus::Cancelled->value)
            ->get()
            ->groupBy('month');

        $months = [];

        foreach (range(1, 12) as $month) {
            $items = $rows->get($month, collect());

            $income = (float) $items->where('type', FinancialType::Income)->sum('amount');
            $expense = (float) $items->where('type', FinancialType::Expense)->sum('amount');

            $months[] = [
                'month' => $month,
                'label' => CarbonImmutable::createFromDate($year, $month, 1)->translatedFormat('F'),
                'income' => round($income, 2),
                'expense' => round($expense, 2),
                'balance' => round($income - $expense, 2),
                'transactions' => $items->count(),
            ];
        }

        $totals = [
            'income' => round(array_sum(array_column($months, 'income')), 2),
            'expense' => round(array_sum(array_column($months, 'expense')), 2),
            'balance' => round(array_sum(array_column($months, 'balance')), 2),
        ];

        return Inertia::render('Admin/Financial/Annual/Index', [
            'year' => $year,
            'months' => $months,
            'totals' => $totals,
        ]);
    }
}
