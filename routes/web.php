<?php

use App\Http\Controllers\Admin\CalendarController;
use App\Http\Controllers\Admin\ClientController;
use App\Http\Controllers\Admin\DeadlineController;
use App\Http\Controllers\Admin\DocumentController;
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
    return Inertia::render('Dashboard');
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
        Route::get('/', function () {
            return redirect()->route('admin.users.index');
        })->name('dashboard');

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
    });

require __DIR__.'/auth.php';
