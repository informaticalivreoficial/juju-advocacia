<?php

namespace App\Http\Controllers\Admin\Financial;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreFinancialExpenseRequest;
use App\Http\Requests\Admin\UpdateFinancialExpenseRequest;
use App\Models\FinancialCategory;
use App\Models\FinancialExpense;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class FinancialExpenseController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', FinancialExpense::class);

        $expenses = FinancialExpense::query()
            ->with('category:id,name,color,type')
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->string('search')->trim()->lower();

                $query->whereRaw('LOWER(description) like ?', ["%{$search}%"])
                    ->orWhereHas('category', fn ($c) => $c->whereRaw('LOWER(name) like ?', ["%{$search}%"]));
            })
            ->orderBy('description')
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Admin/Financial/Expenses/Index', [
            'expenses' => $expenses,
            'filters' => $request->only(['search']),
            'counts' => [
                'total' => FinancialExpense::query()->count(),
                'active' => FinancialExpense::query()->where('active', true)->count(),
            ],
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', FinancialExpense::class);

        return Inertia::render('Admin/Financial/Expenses/Create', [
            'categories' => $this->categories(),
        ]);
    }

    public function store(StoreFinancialExpenseRequest $request): RedirectResponse
    {
        FinancialExpense::create($request->validated() + ['user_id' => $request->user()->id]);

        return redirect()
            ->route('admin.financial.expenses.index')
            ->with('success', 'Despesa fixa criada com sucesso.');
    }

    public function edit(FinancialExpense $expense): Response
    {
        $this->authorize('update', $expense);

        return Inertia::render('Admin/Financial/Expenses/Edit', [
            'expense' => $expense,
            'categories' => $this->categories(),
        ]);
    }

    public function update(UpdateFinancialExpenseRequest $request, FinancialExpense $expense): RedirectResponse
    {
        $expense->update($request->validated());

        return redirect()
            ->route('admin.financial.expenses.index')
            ->with('success', 'Despesa fixa atualizada com sucesso.');
    }

    public function toggleActive(FinancialExpense $expense): RedirectResponse
    {
        $this->authorize('update', $expense);

        $expense->update(['active' => ! $expense->active]);

        return back()->with('success', $expense->active ? 'Despesa ativada.' : 'Despesa desativada.');
    }

    public function destroy(FinancialExpense $expense): RedirectResponse
    {
        $this->authorize('delete', $expense);

        $expense->delete();

        return redirect()
            ->route('admin.financial.expenses.index')
            ->with('success', 'Despesa fixa excluída.');
    }

    private function categories(): array
    {
        return FinancialCategory::query()
            ->where('type', 'expense')
            ->where('active', true)
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn (FinancialCategory $category) => [
                'value' => $category->id,
                'label' => $category->name,
            ])
            ->all();
    }
}
