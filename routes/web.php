<?php

use App\Http\Controllers\Auth\LoginController;
use App\Livewire\Accounts\Create as AccountsCreate;
use App\Livewire\Accounts\Edit as AccountsEdit;
use App\Livewire\Accounts\Index as AccountsIndex;
use App\Livewire\Accounts\Show as AccountsShow;
use App\Livewire\Budgets\Create as BudgetsCreate;
use App\Livewire\Budgets\Edit as BudgetsEdit;
use App\Livewire\Budgets\Index as BudgetsIndex;
use App\Livewire\Categories\Create as CategoriesCreate;
use App\Livewire\Categories\Edit as CategoriesEdit;
use App\Livewire\Categories\Index as CategoriesIndex;
use App\Livewire\Reports\Index as ReportsIndex;
use App\Livewire\Transactions\Create as TransactionsCreate;
use App\Livewire\Transactions\Edit as TransactionsEdit;
use App\Livewire\Transactions\Index as TransactionsIndex;
use App\Livewire\Transactions\Show as TransactionsShow;
use App\Livewire\Transfers\Show as TransfersShow;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
});

Route::post('/logout', [LoginController::class, 'logout'])->name('logout')->middleware('auth');

Route::middleware('auth')->group(function () {
    Route::get('/', fn () => redirect()->route('transactions.index'));
    Route::livewire('/accounts', AccountsIndex::class)->name('accounts.index');
    Route::livewire('/accounts/create', AccountsCreate::class)->name('accounts.create');
    Route::get('/accounts/{account}', AccountsShow::class)->name('accounts.show');
    Route::get('/accounts/{account}/edit', AccountsEdit::class)->name('accounts.edit');
    Route::livewire('/transactions', TransactionsIndex::class)->name('transactions.index');
    Route::livewire('/transactions/create', TransactionsCreate::class)->name('transactions.create');
    Route::get('/transactions/{transaction}', TransactionsShow::class)->name('transactions.show');
    Route::get('/transactions/{transaction}/edit', TransactionsEdit::class)->name('transactions.edit');
    Route::get('/transfers/{transfer}', TransfersShow::class)->name('transfers.show');
    Route::livewire('/categories', CategoriesIndex::class)->name('categories.index');
    Route::livewire('/categories/create', CategoriesCreate::class)->name('categories.create');
    Route::get('/categories/{category}/edit', CategoriesEdit::class)->name('categories.edit');
    Route::livewire('/budgets', BudgetsIndex::class)->name('budgets.index');
    Route::livewire('/budgets/create', BudgetsCreate::class)->name('budgets.create');
    Route::get('/budgets/{budget}/edit', BudgetsEdit::class)->name('budgets.edit');
    Route::livewire('/reports', ReportsIndex::class)->name('reports.index');
});
