<?php

namespace App\Http\Controllers\Admin\Financial;

use App\Enums\FinancialStatus;
use App\Enums\FinancialType;
use App\Http\Controllers\Controller;
use App\Models\FinancialTransaction;
use App\Services\FinancialReportService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FinancialReportController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', FinancialTransaction::class);

        $period = $request->string('period', 'monthly')->toString();
        $year = (int) $request->integer('year', now()->year);
        $year = $year >= 2000 ? $year : now()->year;

        $report = app(FinancialReportService::class)->build(
            year: $year,
            period: in_array($period, ['monthly', 'quarterly', 'semiannual', 'annual'], true) ? $period : 'monthly',
            month: $request->integer('month', now()->month) ?: null,
            quarter: $request->integer('quarter', 1) ?: null,
            half: $request->integer('half', 1) ?: null,
        );

        return Inertia::render('Admin/Financial/Reports/Index', [
            'report' => $report,
            'period' => $report['period'],
            'year' => $year,
            'month' => $request->integer('month', now()->month),
            'quarter' => $request->integer('quarter', 1),
            'half' => $request->integer('half', 1),
        ]);
    }

    public function export(Request $request): StreamedResponse
    {
        $this->authorize('viewAny', FinancialTransaction::class);

        $period = $request->string('period', 'monthly')->toString();
        $year = (int) $request->integer('year', now()->year);
        $year = $year >= 2000 ? $year : now()->year;

        $report = app(FinancialReportService::class)->build(
            year: $year,
            period: in_array($period, ['monthly', 'quarterly', 'semiannual', 'annual'], true) ? $period : 'monthly',
            month: $request->integer('month', now()->month) ?: null,
            quarter: $request->integer('quarter', 1) ?: null,
            half: $request->integer('half', 1) ?: null,
        );

        $filename = 'relatorio-financeiro-'.$year.'-'.$period.'.csv';

        return response()->streamDownload(function () use ($report) {
            $out = fopen('php://output', 'w');

            fputcsv($out, [$report['label'], '', '', '', '']);
            fputcsv($out, ['Tipo', 'Status', 'Descrição', 'Categoria', 'Valor']);
            fputcsv($out, ['', '', '', '', '']);

            foreach ($report['transactions'] as $transaction) {
                fputcsv($out, [
                    $transaction['type'] === FinancialType::Income->value ? 'Receita' : 'Despesa',
                    $transaction['status'] === FinancialStatus::Pending->value ? 'Pendente'
                        : ($transaction['status'] === FinancialStatus::Paid->value ? 'Pago'
                            : ($transaction['status'] === FinancialStatus::Received->value ? 'Recebido' : 'Cancelado')),
                    $transaction['description'],
                    $transaction['category'] ?? '—',
                    number_format((float) $transaction['amount'], 2, ',', '.'),
                ]);
            }

            fputcsv($out, ['', '', '', 'Receitas', number_format((float) $report['income'], 2, ',', '.')]);
            fputcsv($out, ['', '', '', 'Despesas', number_format((float) $report['expense'], 2, ',', '.')]);
            fputcsv($out, ['', '', '', 'Saldo', number_format((float) $report['balance'], 2, ',', '.')]);

            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv']);
    }
}
