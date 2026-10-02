<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BudgetController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\IncomeController;
use App\Http\Controllers\LoanController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RecurringExpenseController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SavingsController;
use App\Http\Controllers\TransactionController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes - Personal Expense Tracker
|--------------------------------------------------------------------------
*/

// Root Redirect
Route::get('/', function () {
    return auth()->check() ? redirect()->route('dashboard') : redirect()->route('login');
});

// Guest Authentication Routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.post');
});

// Authenticated Routes
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Expense Management
    Route::resource('expenses', ExpenseController::class)->except(['create', 'show']);

    // Income Management
    Route::resource('incomes', IncomeController::class)->except(['create', 'show']);

    // Category Management
    Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');
    Route::post('/categories', [CategoryController::class, 'store'])->name('categories.store');
    Route::post('/categories/quick-store', [CategoryController::class, 'quickStore'])->name('categories.quick-store');
    Route::post('/categories/load-defaults', [CategoryController::class, 'loadDefaults'])->name('categories.load-defaults');
    Route::delete('/categories/{category}', [CategoryController::class, 'destroy'])->name('categories.destroy');

    // Budget Management
    Route::get('/budgets', [BudgetController::class, 'index'])->name('budgets.index');
    Route::post('/budgets', [BudgetController::class, 'store'])->name('budgets.store');

    // Reports & Analytics
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');

    // Recurring Expenses
    Route::get('/recurring', [RecurringExpenseController::class, 'index'])->name('recurring.index');
    Route::post('/recurring', [RecurringExpenseController::class, 'store'])->name('recurring.store');
    Route::post('/recurring/{recurringExpense}/toggle', [RecurringExpenseController::class, 'toggle'])->name('recurring.toggle');
    Route::post('/recurring/{recurringExpense}/record', [RecurringExpenseController::class, 'recordNow'])->name('recurring.record');
    Route::delete('/recurring/{recurringExpense}', [RecurringExpenseController::class, 'destroy'])->name('recurring.destroy');

    // Loans & EMI
    Route::get('/loans', [LoanController::class, 'index'])->name('loans.index');
    Route::post('/loans', [LoanController::class, 'store'])->name('loans.store');
    Route::put('/loans/{loan}', [LoanController::class, 'update'])->name('loans.update');
    Route::post('/loans/{loan}/payment', [LoanController::class, 'recordPayment'])->name('loans.payment');
    Route::delete('/loan-payments/{payment}', [LoanController::class, 'deletePayment'])->name('loans.payment.destroy');
    Route::delete('/loans/{loan}', [LoanController::class, 'destroy'])->name('loans.destroy');

    // Savings & Goal Tracker
    Route::get('/savings', [SavingsController::class, 'index'])->name('savings.index');
    Route::post('/savings', [SavingsController::class, 'store'])->name('savings.store');
    Route::post('/savings/{savingsGoal}/deposit', [SavingsController::class, 'deposit'])->name('savings.deposit');
    Route::post('/savings/{savingsGoal}/withdraw', [SavingsController::class, 'withdraw'])->name('savings.withdraw');
    Route::delete('/savings/{savingsGoal}', [SavingsController::class, 'destroy'])->name('savings.destroy');

    // Unified Transactions & CSV Export
    Route::get('/transactions', [TransactionController::class, 'index'])->name('transactions.index');
    Route::get('/transactions/export', [TransactionController::class, 'exportCsv'])->name('transactions.export');

    // Profile & Account Settings
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');

    // Admin Panel Management (Restricted to Administrators)
    Route::middleware('admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/', [AdminController::class, 'index'])->name('dashboard');
        Route::get('/users/{user}', [AdminController::class, 'show'])->name('users.show');
        Route::post('/users', [AdminController::class, 'storeUser'])->name('users.store');
        Route::post('/users/{user}/reset-password', [AdminController::class, 'resetPassword'])->name('users.reset-password');
        Route::post('/users/{user}/toggle-role', [AdminController::class, 'toggleRole'])->name('users.toggle-role');
        Route::delete('/users/{user}', [AdminController::class, 'destroy'])->name('users.destroy');
    });
});
