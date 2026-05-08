<?php

use App\Http\Controllers\Auth\LoginController;
use App\Livewire\Accounts\Create as AccountsCreate;
use App\Livewire\Accounts\Edit as AccountsEdit;
use App\Livewire\Accounts\Index as AccountsIndex;
use App\Livewire\Transactions\Create as TransactionsCreate;
use App\Livewire\Transactions\Edit as TransactionsEdit;
use App\Livewire\Transactions\Index as TransactionsIndex;
use App\Livewire\Transactions\Show as TransactionsShow;
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
    Route::get('/accounts/{account}/edit', AccountsEdit::class)->name('accounts.edit');
    Route::livewire('/transactions', TransactionsIndex::class)->name('transactions.index');
    Route::livewire('/transactions/create', TransactionsCreate::class)->name('transactions.create');
    Route::get('/transactions/{transaction}', TransactionsShow::class)->name('transactions.show');
    Route::get('/transactions/{transaction}/edit', TransactionsEdit::class)->name('transactions.edit');
});
