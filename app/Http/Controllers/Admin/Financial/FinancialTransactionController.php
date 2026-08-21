<?php

namespace App\Http\Controllers\Admin\Financial;

use App\Enums\FinancialPaymentMethod;
use App\Enums\FinancialStatus;
use App\Enums\FinancialType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreFinancialTransactionRequest;
use App\Http\Requests\Admin\UpdateFinancialTransactionRequest;
use App\Models\FinancialCategory;
use App\Models\FinancialTransaction;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FinancialTransactionController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', FinancialTransaction::class);

        $year = (int) $request->integer('year', now()->year);
        $month = max(1, min(12, (int) $request->integer('month', now()->month)));
        $year = $year >= 2000 ? $year : now()->year;

        $transactions = FinancialTransaction::query()
            ->with([
                'category:id,name,color,type',
                'expense:id,description',
                'income:id,description',
            ])
            ->forMonth($year, $month)
            ->when($request->filled('status'), function ($query) use ($request) {
                $query->where('status', $request->string('status'));
            })
            ->when($request->filled('type'), function ($query) use ($request) {
                $query->where('type', $request->string('type'));
            })
            ->orderBy('due_date')
            ->orderBy('category_id')
            ->paginate(25)
            ->through(fn (FinancialTransaction $transaction) => [
                'id' => $transaction->id,
                'type' => $transaction->type->value,
                'status' => $transaction->status->value,
                'status_label' => $transaction->status->label(),
                'amount' => $transaction->amount,
                'description' => $transaction->descriptionText(),
                'category' => $transaction->category?->name,
                'category_id' => $transaction->category_id,
                'category_color' => $transaction->category?->color,
                'year' => $transaction->year,
                'month' => $transaction->month,
                'due_date' => $transaction->dueDate()?->format('Y-m-d'),
                'paid_at' => $transaction->paid_at?->format('Y-m-d'),
                'received_at' => $transaction->received_at?->format('Y-m-d'),
                'payment_method' => $transaction->payment_method,
                'payment_method_label' => $transaction->paymentMethodLabel(),
                'notes' => $transaction->notes,
                'is_ad_hoc' => $transaction->isAdHoc(),
                'is_overdue' => $transaction->isOverdue(),
                'days_until_due' => $transaction->daysUntilDue(),
                'has_attachment' => $transaction->attachment_path !== null,
            ])
            ->withQueryString();

        $monthAll = FinancialTransaction::query()
            ->forMonth($year, $month)
            ->when($request->filled('status'), function ($query) use ($request) {
                $query->where('status', $request->string('status'));
            })
            ->get();

        $active = $monthAll->where('status', '!=', FinancialStatus::Cancelled);

        $totals = [
            'income' => round((float) $active->where('type', FinancialType::Income)->sum('amount'), 2),
            'expense' => round((float) $active->where('type', FinancialType::Expense)->sum('amount'), 2),
            'balance' => round((float) $active->where('type', FinancialType::Income)->sum('amount') - (float) $active->where('type', FinancialType::Expense)->sum('amount'), 2),
        ];

        return Inertia::render('Admin/Financial/Transactions/Index', [
            'transactions' => $transactions,
            'filters' => $request->only(['year', 'month', 'status', 'type']),
            'year' => $year,
            'month' => $month,
            'totals' => $totals,
            'hasGenerated' => FinancialTransaction::query()->forMonth($year, $month)->count() > 0,
            'statuses' => FinancialStatus::options(),
            'types' => FinancialType::options(),
            'paymentMethods' => FinancialPaymentMethod::options(),
            'categories' => FinancialCategory::query()
                ->where('active', true)
                ->orderBy('name')
                ->get(['id', 'name', 'type'])
                ->map(fn (FinancialCategory $category) => [
                    'value' => $category->id,
                    'label' => $category->name,
                    'type' => $category->type->value,
                ]),
        ]);
    }

    public function generate(Request $request): RedirectResponse
    {
        $this->authorize('create', FinancialTransaction::class);

        $year = (int) $request->integer('year', now()->year);
        $month = max(1, min(12, (int) $request->integer('month', now()->month)));

        $count = FinancialTransaction::generateForMonth($year, $month);

        return redirect()
            ->route('admin.financial.transactions.index', ['year' => $year, 'month' => $month])
            ->with('success', "{$count} lançamentos gerados para o mês selecionado.");
    }

    public function store(StoreFinancialTransactionRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $this->assertNoSourceConflict(
            $validated['type'],
            $validated['year'],
            $validated['month'],
            $request->input('expense_id'),
            $request->input('income_id')
        );

        $attachment = null;

        if ($request->hasFile('attachment')) {
            $attachment = $request->file('attachment')->store('financial/attachments', 'public');
        }

        $transaction = FinancialTransaction::create([
            'user_id' => $request->user()->id,
            'type' => $validated['type'],
            'category_id' => $validated['category_id'],
            'expense_id' => $request->input('expense_id'),
            'income_id' => $request->input('income_id'),
            'year' => $validated['year'],
            'month' => $validated['month'],
            'amount' => $validated['amount'],
            'status' => $validated['status'],
            'description' => $validated['description'] ?? null,
            'payment_method' => $validated['payment_method'] ?? null,
            'due_date' => $validated['due_date'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'attachment_path' => $attachment,
            'paid_at' => $this->effectiveDate($validated, 'paid_at'),
            'received_at' => $this->effectiveDate($validated, 'received_at'),
        ]);

        return redirect()
            ->route('admin.financial.transactions.index', ['year' => $transaction->year, 'month' => $transaction->month])
            ->with('success', 'Lançamento criado com sucesso.');
    }

    public function update(UpdateFinancialTransactionRequest $request, FinancialTransaction $transaction): RedirectResponse
    {
        $validated = $request->validated();

        $this->assertNoSourceConflict(
            $transaction->type->value,
            $validated['year'],
            $validated['month'],
            $transaction->expense_id,
            $transaction->income_id,
            $transaction->id
        );

        $attachment = $transaction->attachment_path;

        if ($request->hasFile('attachment')) {
            if ($attachment !== null) {
                Storage::disk('public')->delete($attachment);
            }

            $attachment = $request->file('attachment')->store('financial/attachments', 'public');
        }

        $transaction->update([
            'category_id' => $validated['category_id'],
            'year' => $validated['year'],
            'month' => $validated['month'],
            'amount' => $validated['amount'],
            'status' => $validated['status'],
            'description' => $validated['description'] ?? null,
            'payment_method' => $validated['payment_method'] ?? null,
            'due_date' => $validated['due_date'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'attachment_path' => $attachment,
            'paid_at' => $this->effectiveDate($validated, 'paid_at'),
            'received_at' => $this->effectiveDate($validated, 'received_at'),
        ]);

        return redirect()
            ->route('admin.financial.transactions.index', ['year' => $transaction->year, 'month' => $transaction->month])
            ->with('success', 'Lançamento atualizado com sucesso.');
    }

    public function destroy(FinancialTransaction $transaction): RedirectResponse
    {
        $this->authorize('delete', $transaction);

        if (! $transaction->isAdHoc()) {
            return back()->withErrors(['transaction' => 'Lançamentos recorrentes não podem ser excluídos; use cancelar.']);
        }

        if ($transaction->attachment_path !== null) {
            Storage::disk('public')->delete($transaction->attachment_path);
        }

        $transaction->delete();

        return back()->with('success', 'Lançamento excluído.');
    }

    public function status(Request $request, FinancialTransaction $transaction): RedirectResponse
    {
        $this->authorize('update', $transaction);

        $action = $request->input('action');

        try {
            match ($action) {
                'paid' => $transaction->markAsPaid(),
                'undo_paid' => $transaction->undoPayment(),
                'received' => $transaction->markAsReceived(),
                'undo_received' => $transaction->undoReceipt(),
                'cancel' => $transaction->cancelTransaction(),
                default => throw new \InvalidArgumentException('Ação inválida.'),
            };
        } catch (\Exception $e) {
            return back()->withErrors(['transaction' => $e->getMessage()]);
        }

        return back()->with('success', 'Lançamento atualizado.');
    }

    public function downloadAttachment(FinancialTransaction $transaction): StreamedResponse
    {
        $this->authorize('view', $transaction);

        abort_unless($transaction->attachment_path !== null && Storage::disk('public')->exists($transaction->attachment_path), 404);

        $name = $transaction->descriptionText().'.'.pathinfo($transaction->attachment_path, PATHINFO_EXTENSION);

        return Storage::disk('public')->download($transaction->attachment_path, $name);
    }

    public function markAllExpensesPaid(Request $request): RedirectResponse
    {
        $this->authorize('update', FinancialTransaction::class);

        $year = (int) $request->integer('year', now()->year);
        $month = max(1, min(12, (int) $request->integer('month', now()->month)));

        $transactions = FinancialTransaction::query()
            ->forMonth($year, $month)
            ->expenses()
            ->pending()
            ->get();

        $transactions->each(function (FinancialTransaction $transaction) {
            $transaction->markAsPaid();
        });

        return back()->with('success', "{$transactions->count()} despesas marcadas como pagas.");
    }

    public function markAllIncomesReceived(Request $request): RedirectResponse
    {
        $this->authorize('update', FinancialTransaction::class);

        $year = (int) $request->integer('year', now()->year);
        $month = max(1, min(12, (int) $request->integer('month', now()->month)));

        $transactions = FinancialTransaction::query()
            ->forMonth($year, $month)
            ->incomes()
            ->pending()
            ->get();

        $transactions->each(function (FinancialTransaction $transaction) {
            $transaction->markAsReceived();
        });

        return back()->with('success', "{$transactions->count()} receitas marcadas como recebidas.");
    }

    public function export(Request $request): StreamedResponse
    {
        $this->authorize('viewAny', FinancialTransaction::class);

        $year = (int) $request->integer('year', now()->year);
        $month = max(1, min(12, (int) $request->integer('month', now()->month)));

        $transactions = FinancialTransaction::query()
            ->with(['category:id,name,type', 'expense:id,description', 'income:id,description'])
            ->forMonth($year, $month)
            ->when($request->filled('status'), function ($query) use ($request) {
                $query->where('status', $request->string('status'));
            })
            ->orderBy('due_date')
            ->get();

        $filename = "lancamentos-{$year}-".str_pad($month, 2, '0', STR_PAD_LEFT).'.csv';

        return response()->streamDownload(function () use ($transactions) {
            $out = fopen('php://output', 'w');

            fputcsv($out, ['Tipo', 'Status', 'Descrição', 'Categoria', 'Vencimento', 'Valor', 'Método', 'Origem']);

            foreach ($transactions as $transaction) {
                fputcsv($out, [
                    $transaction->type->label(),
                    $transaction->status->label(),
                    $transaction->descriptionText(),
                    $transaction->category?->name ?? '—',
                    $transaction->dueDate()?->format('d/m/Y') ?? '—',
                    number_format((float) $transaction->amount, 2, ',', '.'),
                    $transaction->paymentMethodLabel() ?? '—',
                    $transaction->isAdHoc() ? 'Avulso' : 'Recorrente',
                ]);
            }

            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    private function effectiveDate(array $validated, string $key): ?string
    {
        if (isset($validated[$key]) && filled($validated[$key])) {
            return $validated[$key];
        }

        $status = $validated['status'] ?? null;

        if ($key === 'paid_at' && $status === FinancialStatus::Paid->value) {
            return now()->toDateTimeString();
        }

        if ($key === 'received_at' && $status === FinancialStatus::Received->value) {
            return now()->toDateTimeString();
        }

        return null;
    }

    private function assertNoSourceConflict(string $type, int $year, int $month, ?int $expenseId, ?int $incomeId, ?int $excludeId = null): void
    {
        $query = FinancialTransaction::query()
            ->where('type', $type)
            ->where('year', $year)
            ->where('month', $month);

        if ($expenseId !== null) {
            $query->where('expense_id', $expenseId);
        } elseif ($incomeId !== null) {
            $query->where('income_id', $incomeId);
        } else {
            return;
        }

        if ($excludeId !== null) {
            $query->where('id', '!=', $excludeId);
        }

        if ($query->exists()) {
            abort(422, 'Já existe um lançamento recorrente desta origem para o mês selecionado.');
        }
    }
}
