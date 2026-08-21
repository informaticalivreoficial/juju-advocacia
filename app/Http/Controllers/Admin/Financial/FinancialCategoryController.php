<?php

namespace App\Http\Controllers\Admin\Financial;

use App\Enums\FinancialStatus;
use App\Enums\FinancialType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreFinancialCategoryRequest;
use App\Http\Requests\Admin\UpdateFinancialCategoryRequest;
use App\Models\FinancialBudget;
use App\Models\FinancialCategory;
use App\Models\FinancialTransaction;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class FinancialCategoryController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', FinancialCategory::class);

        $year = (int) $request->integer('year', now()->year);
        $month = max(1, min(12, (int) $request->integer('month', now()->month)));
        $year = $year >= 2000 ? $year : now()->year;

        $spent = FinancialTransaction::query()
            ->forMonth($year, $month)
            ->where('status', '!=', FinancialStatus::Cancelled->value)
            ->get()
            ->groupBy('category_id')
            ->map(fn ($items) => round((float) $items->sum('amount'), 2));

        $categories = FinancialCategory::query()
            ->orderBy('type')
            ->orderBy('name')
            ->get()
            ->map(function (FinancialCategory $category) use ($year, $month, $spent) {
                $budget = FinancialBudget::query()
                    ->where('category_id', $category->id)
                    ->where('year', $year)
                    ->where('month', $month)
                    ->first();

                $limit = $budget?->amount ?? 0;
                $used = (float) ($spent[$category->id] ?? 0);

                return [
                    'id' => $category->id,
                    'name' => $category->name,
                    'type' => $category->type->value,
                    'type_label' => $category->type->label(),
                    'color' => $category->color,
                    'icon' => $category->icon,
                    'active' => $category->active,
                    'budget' => [
                        'id' => $budget?->id,
                        'limit' => round($limit, 2),
                        'used' => $used,
                        'percent' => $limit > 0 ? min(100, round(($used / $limit) * 100)) : 0,
                    ],
                ];
            })
            ->groupBy('type');

        return Inertia::render('Admin/Financial/Categories/Index', [
            'categories' => $categories,
            'year' => $year,
            'month' => $month,
            'types' => FinancialType::options(),
        ]);
    }

    public function store(StoreFinancialCategoryRequest $request): RedirectResponse
    {
        $this->authorize('create', FinancialCategory::class);

        FinancialCategory::create($request->validated() + ['user_id' => $request->user()->id]);

        return redirect()
            ->route('admin.financial.categories.index')
            ->with('success', 'Categoria criada com sucesso.');
    }

    public function update(UpdateFinancialCategoryRequest $request, FinancialCategory $category): RedirectResponse
    {
        $this->authorize('update', $category);

        $category->update($request->validated());

        return back()->with('success', 'Categoria atualizada.');
    }

    public function toggleActive(FinancialCategory $category): RedirectResponse
    {
        $this->authorize('update', $category);

        $category->update(['active' => ! $category->active]);

        return back()->with('success', $category->active ? 'Categoria ativada.' : 'Categoria desativada.');
    }

    public function destroy(FinancialCategory $category): RedirectResponse
    {
        $this->authorize('delete', $category);

        if ($category->transactions()->exists()) {
            return back()->withErrors(['category' => 'Não é possível excluir uma categoria com lançamentos vinculados.']);
        }

        $category->budgets()->delete();
        $category->delete();

        return back()->with('success', 'Categoria excluída.');
    }
}
