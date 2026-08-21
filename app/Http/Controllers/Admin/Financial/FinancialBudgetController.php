<?php

namespace App\Http\Controllers\Admin\Financial;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreFinancialBudgetRequest;
use App\Models\FinancialBudget;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class FinancialBudgetController extends Controller
{
    public function store(StoreFinancialBudgetRequest $request): RedirectResponse
    {
        $this->authorize('create', FinancialBudget::class);

        $validated = $request->validated();

        $budget = FinancialBudget::query()->updateOrCreate(
            [
                'user_id' => $request->user()->id,
                'category_id' => $validated['category_id'],
                'year' => $validated['year'],
                'month' => $validated['month'],
            ],
            ['amount' => $validated['amount']]
        );

        return redirect()
            ->route('admin.financial.categories.index', ['year' => $validated['year'], 'month' => $validated['month']])
            ->with('success', $budget->wasRecentlyCreated ? 'Orçamento definido.' : 'Orçamento atualizado.');
    }

    public function destroy(Request $request, FinancialBudget $budget): RedirectResponse
    {
        $this->authorize('delete', $budget);

        $budget->delete();

        return redirect()
            ->route('admin.financial.categories.index', ['year' => $request->integer('year', now()->year), 'month' => $request->integer('month', now()->month)])
            ->with('success', 'Orçamento removido.');
    }
}
