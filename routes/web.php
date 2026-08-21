<?php

use App\Http\Controllers\Admin\CalendarController;
use App\Http\Controllers\Admin\ClientController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DeadlineController;
use App\Http\Controllers\Admin\DocumentController;
use App\Http\Controllers\Admin\Financial\FinancialAnnualController;
use App\Http\Controllers\Admin\Financial\FinancialBudgetController;
use App\Http\Controllers\Admin\Financial\FinancialCategoryController;
use App\Http\Controllers\Admin\Financial\FinancialDashboardController;
use App\Http\Controllers\Admin\Financial\FinancialExpenseController;
use App\Http\Controllers\Admin\Financial\FinancialIncomeController;
use App\Http\Controllers\Admin\Financial\FinancialReportController;
use App\Http\Controllers\Admin\Financial\FinancialTransactionController;
use App\Http\Controllers\Admin\PermissionController;
use App\Http\Controllers\Admin\ProcessController;
use App\Http\Controllers\Admin\TaskController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return Inertia::render('Welcome');
});

Route::post('/contato', [ContactController::class, 'store'])->name('contact.store');

Route::get('/dashboard', function () {
    return redirect()->route('admin.dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

/*
|--------------------------------------------------------------------------
| Painel Administrativo (/admin)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

        Route::get('permissions', [PermissionController::class, 'index'])->name('permissions.index');

        Route::get('clients', [ClientController::class, 'index'])->name('clients.index');
        Route::get('clients/create', [ClientController::class, 'create'])->name('clients.create');
        Route::post('clients', [ClientController::class, 'store'])->name('clients.store');
        Route::get('clients/{client}', [ClientController::class, 'show'])->name('clients.show');
        Route::get('clients/{client}/edit', [ClientController::class, 'edit'])->name('clients.edit');
        Route::put('clients/{client}', [ClientController::class, 'update'])->name('clients.update');
        Route::delete('clients/{client}', [ClientController::class, 'destroy'])->name('clients.destroy');

        Route::get('processes', [ProcessController::class, 'index'])->name('processes.index');
        Route::get('processes/create', [ProcessController::class, 'create'])->name('processes.create');
        Route::post('processes', [ProcessController::class, 'store'])->name('processes.store');
        Route::get('processes/{process}', [ProcessController::class, 'show'])->name('processes.show');
        Route::get('processes/{process}/edit', [ProcessController::class, 'edit'])->name('processes.edit');
        Route::put('processes/{process}', [ProcessController::class, 'update'])->name('processes.update');
        Route::delete('processes/{process}', [ProcessController::class, 'destroy'])->name('processes.destroy');

        Route::get('deadlines', [DeadlineController::class, 'index'])->name('deadlines.index');
        Route::get('deadlines/create', [DeadlineController::class, 'create'])->name('deadlines.create');
        Route::post('deadlines', [DeadlineController::class, 'store'])->name('deadlines.store');
        Route::get('deadlines/{deadline}/edit', [DeadlineController::class, 'edit'])->name('deadlines.edit');
        Route::put('deadlines/{deadline}', [DeadlineController::class, 'update'])->name('deadlines.update');
        Route::patch('deadlines/{deadline}/toggle-complete', [DeadlineController::class, 'toggleComplete'])->name('deadlines.toggle-complete');
        Route::delete('deadlines/{deadline}', [DeadlineController::class, 'destroy'])->name('deadlines.destroy');

        Route::get('tasks', [TaskController::class, 'index'])->name('tasks.index');
        Route::get('tasks/create', [TaskController::class, 'create'])->name('tasks.create');
        Route::post('tasks', [TaskController::class, 'store'])->name('tasks.store');
        Route::get('tasks/{task}/edit', [TaskController::class, 'edit'])->name('tasks.edit');
        Route::put('tasks/{task}', [TaskController::class, 'update'])->name('tasks.update');
        Route::patch('tasks/{task}/toggle-complete', [TaskController::class, 'toggleComplete'])->name('tasks.toggle-complete');
        Route::delete('tasks/{task}', [TaskController::class, 'destroy'])->name('tasks.destroy');

        Route::get('calendar', [CalendarController::class, 'index'])->name('calendar.index');
        Route::get('calendar/create', [CalendarController::class, 'create'])->name('calendar.create');
        Route::post('calendar', [CalendarController::class, 'store'])->name('calendar.store');
        Route::get('calendar/{calendar_event}/edit', [CalendarController::class, 'edit'])->name('calendar.edit');
        Route::put('calendar/{calendar_event}', [CalendarController::class, 'update'])->name('calendar.update');
        Route::delete('calendar/{calendar_event}', [CalendarController::class, 'destroy'])->name('calendar.destroy');

        Route::get('documents', [DocumentController::class, 'index'])->name('documents.index');
        Route::get('documents/create', [DocumentController::class, 'create'])->name('documents.create');
        Route::post('documents', [DocumentController::class, 'store'])->name('documents.store');
        Route::get('documents/{document}/download', [DocumentController::class, 'download'])->name('documents.download');
        Route::delete('documents/{document}', [DocumentController::class, 'destroy'])->name('documents.destroy');

        Route::get('users', [UserController::class, 'index'])->name('users.index');
        Route::get('users/create', [UserController::class, 'create'])->name('users.create');
        Route::post('users', [UserController::class, 'store'])->name('users.store');
        Route::get('users/{user}', [UserController::class, 'show'])->name('users.show');
        Route::get('users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
        Route::put('users/{user}', [UserController::class, 'update'])->name('users.update');
        Route::patch('users/{user}/active', [UserController::class, 'toggleActive'])->name('users.toggle-active');
        Route::delete('users/{user}', [UserController::class, 'destroy'])->name('users.destroy');

        Route::prefix('financeiro')
            ->name('financial.')
            ->group(function () {
                Route::get('/', [FinancialDashboardController::class, 'index'])->name('dashboard');
                Route::get('anual', [FinancialAnnualController::class, 'index'])->name('annual.index');
                Route::get('relatorios', [FinancialReportController::class, 'index'])->name('reports.index');
                Route::get('relatorios/exportar', [FinancialReportController::class, 'export'])->name('reports.export');

                Route::get('lancamentos', [FinancialTransactionController::class, 'index'])->name('transactions.index');
                Route::post('lancamentos/gerar', [FinancialTransactionController::class, 'generate'])->name('transactions.generate');
                Route::post('lancamentos/marcar-despesas-pagas', [FinancialTransactionController::class, 'markAllExpensesPaid'])->name('transactions.mark-all-expenses-paid');
                Route::post('lancamentos/marcar-receitas-recebidas', [FinancialTransactionController::class, 'markAllIncomesReceived'])->name('transactions.mark-all-incomes-received');
                Route::post('lancamentos', [FinancialTransactionController::class, 'store'])->name('transactions.store');
                Route::get('lancamentos/exportar', [FinancialTransactionController::class, 'export'])->name('transactions.export');
                Route::get('lancamentos/{transaction}/anexo', [FinancialTransactionController::class, 'downloadAttachment'])->name('transactions.download-attachment');
                Route::put('lancamentos/{transaction}', [FinancialTransactionController::class, 'update'])->name('transactions.update');
                Route::patch('lancamentos/{transaction}/status', [FinancialTransactionController::class, 'status'])->name('transactions.status');
                Route::delete('lancamentos/{transaction}', [FinancialTransactionController::class, 'destroy'])->name('transactions.destroy');

                Route::get('despesas', [FinancialExpenseController::class, 'index'])->name('expenses.index');
                Route::get('despesas/create', [FinancialExpenseController::class, 'create'])->name('expenses.create');
                Route::post('despesas', [FinancialExpenseController::class, 'store'])->name('expenses.store');
                Route::get('despesas/{expense}/edit', [FinancialExpenseController::class, 'edit'])->name('expenses.edit');
                Route::put('despesas/{expense}', [FinancialExpenseController::class, 'update'])->name('expenses.update');
                Route::patch('despesas/{expense}/active', [FinancialExpenseController::class, 'toggleActive'])->name('expenses.toggle-active');
                Route::delete('despesas/{expense}', [FinancialExpenseController::class, 'destroy'])->name('expenses.destroy');

                Route::get('receitas', [FinancialIncomeController::class, 'index'])->name('incomes.index');
                Route::get('receitas/create', [FinancialIncomeController::class, 'create'])->name('incomes.create');
                Route::post('receitas', [FinancialIncomeController::class, 'store'])->name('incomes.store');
                Route::get('receitas/{income}/edit', [FinancialIncomeController::class, 'edit'])->name('incomes.edit');
                Route::put('receitas/{income}', [FinancialIncomeController::class, 'update'])->name('incomes.update');
                Route::patch('receitas/{income}/active', [FinancialIncomeController::class, 'toggleActive'])->name('incomes.toggle-active');
                Route::delete('receitas/{income}', [FinancialIncomeController::class, 'destroy'])->name('incomes.destroy');

                Route::get('categorias', [FinancialCategoryController::class, 'index'])->name('categories.index');
                Route::post('categorias', [FinancialCategoryController::class, 'store'])->name('categories.store');
                Route::put('categorias/{category}', [FinancialCategoryController::class, 'update'])->name('categories.update');
                Route::patch('categorias/{category}/active', [FinancialCategoryController::class, 'toggleActive'])->name('categories.toggle-active');
                Route::delete('categorias/{category}', [FinancialCategoryController::class, 'destroy'])->name('categories.destroy');

                Route::post('orcamentos', [FinancialBudgetController::class, 'store'])->name('budgets.store');
                Route::delete('orcamentos/{budget}', [FinancialBudgetController::class, 'destroy'])->name('budgets.destroy');
            });
    });

require __DIR__.'/auth.php';
