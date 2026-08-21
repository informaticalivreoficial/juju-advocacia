<?php

namespace App\Http\Controllers\Admin\Financial;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreFinancialIncomeRequest;
use App\Http\Requests\Admin\UpdateFinancialIncomeRequest;
use App\Models\FinancialCategory;
use App\Models\FinancialIncome;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class FinancialIncomeController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', FinancialIncome::class);

        $incomes = FinancialIncome::query()
            ->with('category:id,name,color,type')
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->string('search')->trim()->lower();

                $query->whereRaw('LOWER(description) like ?', ["%{$search}%"])
                    ->orWhereHas('category', fn ($c) => $c->whereRaw('LOWER(name) like ?', ["%{$search}%"]));
            })
            ->orderBy('description')
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Admin/Financial/Incomes/Index', [
            'incomes' => $incomes,
            'filters' => $request->only(['search']),
            'counts' => [
                'total' => FinancialIncome::query()->count(),
                'active' => FinancialIncome::query()->where('active', true)->count(),
            ],
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', FinancialIncome::class);

        return Inertia::render('Admin/Financial/Incomes/Create', [
            'categories' => $this->categories(),
        ]);
    }

    public function store(StoreFinancialIncomeRequest $request): RedirectResponse
    {
        FinancialIncome::create($request->validated() + ['user_id' => $request->user()->id]);

        return redirect()
            ->route('admin.financial.incomes.index')
            ->with('success', 'Receita fixa criada com sucesso.');
    }

    public function edit(FinancialIncome $income): Response
    {
        $this->authorize('update', $income);

        return Inertia::render('Admin/Financial/Incomes/Edit', [
            'income' => $income,
            'categories' => $this->categories(),
        ]);
    }

    public function update(UpdateFinancialIncomeRequest $request, FinancialIncome $income): RedirectResponse
    {
        $income->update($request->validated());

        return redirect()
            ->route('admin.financial.incomes.index')
            ->with('success', 'Receita fixa atualizada com sucesso.');
    }

    public function toggleActive(FinancialIncome $income): RedirectResponse
    {
        $this->authorize('update', $income);

        $income->update(['active' => ! $income->active]);

        return back()->with('success', $income->active ? 'Receita ativada.' : 'Receita desativada.');
    }

    public function destroy(FinancialIncome $income): RedirectResponse
    {
        $this->authorize('delete', $income);

        $income->delete();

        return redirect()
            ->route('admin.financial.incomes.index')
            ->with('success', 'Receita fixa excluída.');
    }

    private function categories(): array
    {
        return FinancialCategory::query()
            ->where('type', 'income')
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
